<?php

namespace App\Services\Finance;

use App\Models\Sales\CustomerInvoice;
use App\Models\Finance\SupplierInvoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * AR/AP Aging: every unpaid, non-cancelled invoice bucketed by days overdue
 * as of a given date (Current / 1-30 / 31-60 / 61-90 / 90+), based on
 * due_date (falling back to invoice_date when unset).
 */
class AgingReportService
{
    public function customerAging(int $companyId, string $asOfDate): Collection
    {
        $invoices = CustomerInvoice::query()
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->with('businessPartner')
            ->get();

        return $this->bucket($invoices, $asOfDate);
    }

    public function supplierAging(int $companyId, string $asOfDate): Collection
    {
        $invoices = SupplierInvoice::query()
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->with('businessPartner')
            ->get();

        return $this->bucket($invoices, $asOfDate);
    }

    protected function bucket(Collection $invoices, string $asOfDate): Collection
    {
        $asOf = Carbon::parse($asOfDate)->startOfDay();

        return $invoices
            ->filter(fn ($invoice) => $invoice->outstanding >= 0.01)
            ->map(function ($invoice) use ($asOf) {
                $dueDate = Carbon::parse($invoice->due_date ?? $invoice->invoice_date)->startOfDay();
                $daysOverdue = $this->daysOverdue($dueDate, $asOf);

                return [
                    'business_partner' => $invoice->businessPartner?->name,
                    'number' => $invoice->number,
                    'invoice_date' => $invoice->invoice_date,
                    'due_date' => $invoice->due_date,
                    'outstanding' => round($invoice->outstanding, 2),
                    'days_overdue' => $daysOverdue,
                    'bucket' => $this->bucketLabel($daysOverdue),
                ];
            })
            ->values();
    }

    /**
     * Positive = overdue by that many days, zero/negative = not yet due.
     * Computed via raw Unix-timestamp subtraction rather than Carbon's
     * diffInDays()/lt() combo, whose mutation and sign conventions have
     * bitten this codebase before — this is unambiguous by construction.
     */
    protected function daysOverdue(Carbon $dueDate, Carbon $asOf): int
    {
        $dueTimestamp = $dueDate->copy()->startOfDay()->getTimestamp();
        $asOfTimestamp = $asOf->copy()->startOfDay()->getTimestamp();

        return (int) round(($asOfTimestamp - $dueTimestamp) / 86400);
    }

    protected function bucketLabel(int $days): string
    {
        return match (true) {
            $days <= 0 => 'Current',
            $days <= 30 => '1-30',
            $days <= 60 => '31-60',
            $days <= 90 => '61-90',
            default => '90+',
        };
    }
}
