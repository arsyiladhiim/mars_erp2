<?php

namespace App\Services\Procurement;

use App\Models\Audit\AuditLog;
use App\Models\Core\AccountingPeriod;
use App\Models\Core\NumberSeries;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\SupplierInvoice;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Posts a draft Supplier Invoice against its linked Goods Receipt (3-way
 * match): clears GR/IR, recognizes input VAT, and books the Accounts
 * Payable liability.
 */
class SupplierInvoicePostingService
{
    public function post(SupplierInvoice $invoice): JournalEntry
    {
        if ($invoice->status !== 'draft') {
            throw new RuntimeException("Supplier Invoice {$invoice->number} has already been posted.");
        }

        if (! $invoice->goods_receipt_id) {
            throw new RuntimeException('Link a Goods Receipt to this invoice before posting (3-way match required).');
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
            $grIrAccount = GlAccountMapping::resolve('gr_ir_clearing', $invoice->company_id);
            $apAccount = GlAccountMapping::resolve('accounts_payable', $invoice->company_id);
            $period = AccountingPeriod::forDate($invoice->invoice_date, $invoice->company_id);

            $journalEntry = JournalEntry::create([
                'company_id' => $invoice->company_id,
                'accounting_period_id' => $period?->id,
                'number' => NumberSeries::next($invoice->company_id, 'journal_entry'),
                'entry_date' => $invoice->invoice_date,
                'source_type' => 'supplier_invoice',
                'reference_type' => SupplierInvoice::class,
                'reference_id' => $invoice->id,
                'memo' => "Supplier Invoice {$invoice->number}",
                'total_debit' => $invoice->grand_total,
                'total_credit' => $invoice->grand_total,
                'status' => 'posted',
            ]);

            $lines = [
                ['chart_of_account_id' => $grIrAccount->id, 'description' => "SINV {$invoice->number} — GR/IR Clearing", 'debit' => (float) $invoice->subtotal, 'credit' => 0],
            ];

            if ((float) $invoice->tax_total > 0) {
                $taxAccount = GlAccountMapping::resolve('tax_input', $invoice->company_id);
                $lines[] = ['chart_of_account_id' => $taxAccount->id, 'description' => "SINV {$invoice->number} — VAT Input", 'debit' => (float) $invoice->tax_total, 'credit' => 0];
            }

            $lines[] = ['chart_of_account_id' => $apAccount->id, 'description' => "SINV {$invoice->number} — Accounts Payable", 'debit' => 0, 'credit' => (float) $invoice->grand_total];

            $journalEntry->lines()->createMany($lines);

            $invoice->forceFill(['status' => 'posted'])->save();

            AuditLog::record('posted', SupplierInvoice::class, $invoice->getKey(), null, ['journal_entry_id' => $journalEntry->id]);

            return $journalEntry;
        });
    }
}
