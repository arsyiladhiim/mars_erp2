<?php

namespace App\Filament\Widgets;

use App\Models\Core\Company;
use App\Models\Finance\SupplierInvoice;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\SalesOrder;
use App\Services\Reporting\InventoryDashboardService;
use App\Services\Reporting\ProfitLossService;
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

        $company = Company::first();
        $profitLoss = $company
            ? app(ProfitLossService::class)->generate($company->id, now()->startOfYear()->toDateString(), now()->toDateString())
            : ['gross_profit' => 0, 'net_profit' => 0];
        $inventoryValue = $company ? app(InventoryDashboardService::class)->totalStockValue($company->id) : 0.0;

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
            Stat::make('Net Profit (YTD)', 'Rp '.number_format($profitLoss['net_profit'], 0, ',', '.'))
                ->description('Gross Profit: Rp '.number_format($profitLoss['gross_profit'], 0, ',', '.'))
                ->icon('heroicon-o-chart-bar')
                ->color($profitLoss['net_profit'] >= 0 ? 'success' : 'danger'),
            Stat::make('Inventory Value', 'Rp '.number_format($inventoryValue, 0, ',', '.'))
                ->description('Current stock, at moving-average cost')
                ->icon('heroicon-o-cube')
                ->color('info'),
        ];
    }
}
