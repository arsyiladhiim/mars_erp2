<?php

namespace App\Services\Sales;

use App\Models\Audit\AuditLog;
use App\Models\Core\AccountingPeriod;
use App\Models\Core\NumberSeries;
use App\Models\Finance\GlAccountMapping;
use App\Models\Finance\IncomingPayment;
use App\Models\Finance\JournalEntry;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Posts a draft Incoming Payment against its applied Customer Invoice: a
 * cash/bank receipt journal (Dr the payment's bank/cash account, Cr
 * Accounts Receivable), then rolls the invoice's paid_amount and status.
 */
class IncomingPaymentPostingService
{
    public function post(IncomingPayment $payment): JournalEntry
    {
        if ($payment->status !== 'draft') {
            throw new RuntimeException("Incoming Payment {$payment->number} has already been posted.");
        }

        if (! $payment->customer_invoice_id) {
            throw new RuntimeException('Link a Customer Invoice before posting this payment.');
        }

        if (! $payment->bank_account_id) {
            throw new RuntimeException('Select a bank/cash account before posting this payment.');
        }

        $invoice = $payment->customerInvoice;
        $amount = (float) $payment->amount;

        if ($amount <= 0) {
            throw new RuntimeException('Payment amount must be greater than zero.');
        }

        if (bccomp((string) $amount, (string) $invoice->outstanding, 2) > 0) {
            throw new RuntimeException("Payment amount ({$amount}) exceeds the outstanding balance ({$invoice->outstanding}) of Customer Invoice {$invoice->number}.");
        }

        AccountingPeriod::assertOpenForPosting($payment->payment_date, $payment->company_id);

        return DB::transaction(function () use ($payment, $invoice, $amount) {
            $bankAccount = $payment->bankAccount()->firstOrFail();
            $cashAccount = $bankAccount->chartOfAccount()->firstOrFail();
            $arAccount = GlAccountMapping::resolve('accounts_receivable', $payment->company_id);
            $period = AccountingPeriod::forDate($payment->payment_date, $payment->company_id);

            $journalEntry = JournalEntry::create([
                'company_id' => $payment->company_id,
                'accounting_period_id' => $period?->id,
                'number' => NumberSeries::next($payment->company_id, 'journal_entry'),
                'entry_date' => $payment->payment_date,
                'source_type' => 'incoming_payment',
                'reference_type' => IncomingPayment::class,
                'reference_id' => $payment->id,
                'memo' => "Incoming Payment {$payment->number}",
                'total_debit' => $amount,
                'total_credit' => $amount,
                'status' => 'posted',
            ]);

            $journalEntry->lines()->createMany([
                ['chart_of_account_id' => $cashAccount->id, 'description' => "RCP {$payment->number} — {$bankAccount->account_name}", 'debit' => $amount, 'credit' => 0],
                ['chart_of_account_id' => $arAccount->id, 'description' => "RCP {$payment->number} — Accounts Receivable", 'debit' => 0, 'credit' => $amount],
            ]);

            $newPaidAmount = round((float) $invoice->paid_amount + $amount, 2);
            $invoice->forceFill([
                'paid_amount' => $newPaidAmount,
                'status' => bccomp((string) $newPaidAmount, (string) $invoice->grand_total, 2) >= 0 ? 'paid' : 'partially_paid',
            ])->save();

            $payment->forceFill(['status' => 'posted'])->save();

            AuditLog::record('posted', IncomingPayment::class, $payment->getKey(), null, ['journal_entry_id' => $journalEntry->id]);

            return $journalEntry;
        });
    }
}
