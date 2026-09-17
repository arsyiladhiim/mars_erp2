<?php

namespace App\Services\Sales;

use App\Models\Audit\AuditLog;
use App\Models\Core\AccountingPeriod;
use App\Models\Core\NumberSeries;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\JournalEntry;
use App\Models\Sales\CustomerInvoice;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Posts a draft Customer Invoice: an AR journal (Dr Accounts Receivable,
 * Cr Sales Revenue + Cr VAT Output if any). Independent of Delivery — the
 * COGS/Inventory reduction already happened when the Delivery was posted;
 * revenue recognition doesn't require a delivery link (service invoices,
 * invoice-before-delivery, etc. are all valid).
 */
class CustomerInvoicePostingService
{
    public function post(CustomerInvoice $invoice): JournalEntry
    {
        if ($invoice->status !== 'draft') {
            throw new RuntimeException("Customer Invoice {$invoice->number} has already been posted.");
        }

        $reconciled = bccomp(
            bcadd((string) $invoice->subtotal, (string) $invoice->tax_total, 2),
            (string) $invoice->grand_total,
            2
        ) === 0;

        if (! $reconciled) {
            throw new RuntimeException('Subtotal + Tax must equal Grand Total before posting.');
        }

        AccountingPeriod::assertOpenForPosting($invoice->invoice_date, $invoice->company_id);

        return DB::transaction(function () use ($invoice) {
            $arAccount = GlAccountMapping::resolve('accounts_receivable', $invoice->company_id);
            $revenueAccount = GlAccountMapping::resolve('sales_revenue', $invoice->company_id);
            $period = AccountingPeriod::forDate($invoice->invoice_date, $invoice->company_id);

            $journalEntry = JournalEntry::create([
                'company_id' => $invoice->company_id,
                'accounting_period_id' => $period?->id,
                'number' => NumberSeries::next($invoice->company_id, 'journal_entry'),
                'entry_date' => $invoice->invoice_date,
                'source_type' => 'customer_invoice',
                'reference_type' => CustomerInvoice::class,
                'reference_id' => $invoice->id,
                'memo' => "Customer Invoice {$invoice->number}",
                'total_debit' => $invoice->grand_total,
                'total_credit' => $invoice->grand_total,
                'status' => 'posted',
            ]);

            $lines = [
                ['chart_of_account_id' => $arAccount->id, 'description' => "INV {$invoice->number} — Accounts Receivable", 'debit' => (float) $invoice->grand_total, 'credit' => 0],
                ['chart_of_account_id' => $revenueAccount->id, 'description' => "INV {$invoice->number} — Sales Revenue", 'debit' => 0, 'credit' => (float) $invoice->subtotal],
            ];

            if ((float) $invoice->tax_total > 0) {
                $taxAccount = GlAccountMapping::resolve('tax_output', $invoice->company_id);
                $lines[] = ['chart_of_account_id' => $taxAccount->id, 'description' => "INV {$invoice->number} — VAT Output", 'debit' => 0, 'credit' => (float) $invoice->tax_total];
            }

            $journalEntry->lines()->createMany($lines);

            $invoice->forceFill(['status' => 'posted'])->save();

            AuditLog::record('posted', CustomerInvoice::class, $invoice->getKey(), null, ['journal_entry_id' => $journalEntry->id]);

            return $journalEntry;
        });
    }
}
