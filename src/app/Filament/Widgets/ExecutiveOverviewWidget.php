<?php

namespace App\Filament\Widgets;

use App\Models\Finance\SupplierInvoice;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Executive Dashboard — Revenue, AR/AP, Purchasing, Sales snapshot (PRD §27).
 * Figures are computed directly from posted transaction headers; once the
 * Finance module's GL posting is hardened, these should read from the ledger instead.
 */
class ExecutiveOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $salesTotal = CustomerInvoice::whereNotIn('status', ['cancelled'])->sum('grand_total');
        $outstandingAr = CustomerInvoice::whereNotIn('status', ['paid', 'cancelled'])
            ->selectRaw('COALESCE(SUM(grand_total - paid_amount), 0) as outstanding')
            ->value('outstanding');

        $purchaseTotal = PurchaseOrder::whereNotIn('status', ['cancelled', 'rejected'])->sum('grand_total');
        $outstandingAp = SupplierInvoice::whereNotIn('status', ['paid', 'cancelled'])
            ->selectRaw('COALESCE(SUM(grand_total - paid_amount), 0) as outstanding')
            ->value('outstanding');

        $openSalesOrders = SalesOrder::whereNotIn('status', ['closed', 'cancelled', 'rejected'])->count();
        $openPurchaseOrders = PurchaseOrder::whereNotIn('status', ['closed', 'cancelled', 'rejected'])->count();

        return [
            Stat::make('Total Sales', 'Rp '.number_format((float) $salesTotal, 0, ',', '.'))
                ->description('Posted customer invoices')
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Outstanding AR', 'Rp '.number_format((float) $outstandingAr, 0, ',', '.'))
                ->description('Unpaid customer invoices')
                ->icon('heroicon-o-arrow-down-circle')
                ->color('warning'),
            Stat::make('Total Purchasing', 'Rp '.number_format((float) $purchaseTotal, 0, ',', '.'))
                ->description('Purchase orders issued')
                ->icon('heroicon-o-shopping-cart')
                ->color('info'),
            Stat::make('Outstanding AP', 'Rp '.number_format((float) $outstandingAp, 0, ',', '.'))
                ->description('Unpaid supplier invoices')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('danger'),
            Stat::make('Open Sales Orders', (string) $openSalesOrders)
                ->icon('heroicon-o-shopping-bag'),
            Stat::make('Open Purchase Orders', (string) $openPurchaseOrders)
                ->icon('heroicon-o-clipboard-document-list'),
        ];
    }
}
