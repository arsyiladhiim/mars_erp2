<?php

namespace App\Services\Finance;

use App\Models\Audit\AuditLog;
use App\Models\Core\AccountingPeriod;
use App\Models\Core\NumberSeries;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\OutgoingPayment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Posts a draft Outgoing Payment against its applied Supplier Invoice: a
 * cash/bank disbursement journal (Dr Accounts Payable, Cr the payment's
 * bank/cash account), then rolls the invoice's paid_amount and status.
 * Mirrors App\Services\Sales\IncomingPaymentPostingService exactly, on the
 * AP side instead of AR.
 */
class OutgoingPaymentPostingService
{
    public function post(OutgoingPayment $payment): JournalEntry
    {
        if ($payment->status !== 'draft') {
            throw new RuntimeException("Outgoing Payment {$payment->number} has already been posted.");
        }

        if (! $payment->supplier_invoice_id) {
            throw new RuntimeException('Link a Supplier Invoice before posting this payment.');
        }

        if (! $payment->bank_account_id) {
            throw new RuntimeException('Select a bank/cash account before posting this payment.');
        }

        $invoice = $payment->supplierInvoice;
        $amount = (float) $payment->amount;

        if ($amount <= 0) {
            throw new RuntimeException('Payment amount must be greater than zero.');
        }

        if (bccomp((string) $amount, (string) $invoice->outstanding, 2) > 0) {
            throw new RuntimeException("Payment amount ({$amount}) exceeds the outstanding balance ({$invoice->outstanding}) of Supplier Invoice {$invoice->number}.");
        }

        AccountingPeriod::assertOpenForPosting($payment->payment_date, $payment->company_id);

        return DB::transaction(function () use ($payment, $invoice, $amount) {
            $bankAccount = $payment->bankAccount()->firstOrFail();
            $cashAccount = $bankAccount->chartOfAccount()->firstOrFail();
            $apAccount = GlAccountMapping::resolve('accounts_payable', $payment->company_id);
            $period = AccountingPeriod::forDate($payment->payment_date, $payment->company_id);

            $journalEntry = JournalEntry::create([
                'company_id' => $payment->company_id,
                'accounting_period_id' => $period?->id,
                'number' => NumberSeries::next($payment->company_id, 'journal_entry'),
                'entry_date' => $payment->payment_date,
                'source_type' => 'outgoing_payment',
                'reference_type' => OutgoingPayment::class,
                'reference_id' => $payment->id,
                'memo' => "Outgoing Payment {$payment->number}",
                'total_debit' => $amount,
                'total_credit' => $amount,
                'status' => 'posted',
            ]);

            $journalEntry->lines()->createMany([
                ['chart_of_account_id' => $apAccount->id, 'description' => "PAY {$payment->number} — Accounts Payable", 'debit' => $amount, 'credit' => 0],
                ['chart_of_account_id' => $cashAccount->id, 'description' => "PAY {$payment->number} — {$bankAccount->account_name}", 'debit' => 0, 'credit' => $amount],
            ]);

            $newPaidAmount = round((float) $invoice->paid_amount + $amount, 2);
            $invoice->forceFill([
                'paid_amount' => $newPaidAmount,
                'status' => bccomp((string) $newPaidAmount, (string) $invoice->grand_total, 2) >= 0 ? 'paid' : 'partially_paid',
            ])->save();

            $payment->forceFill(['status' => 'posted'])->save();

            AuditLog::record('posted', OutgoingPayment::class, $payment->getKey(), null, ['journal_entry_id' => $journalEntry->id]);

            return $journalEntry;
        });
    }
}
