<?php

namespace App\Filament\Widgets;

use App\Models\Finance\OutgoingPayment;
use App\Models\Inventory\StockAdjustment;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Sales\SalesOrder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Cross-module "pending my action" snapshot, standing in for a full
 * Approval Workflow inbox (PRD §23) until the workflow engine is wired to
 * these documents in a later phase.
 */
class PendingApprovalsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Purchase Requests', (string) PurchaseRequest::where('status', 'pending_approval')->count())
                ->description('Awaiting approval')->icon('heroicon-o-clipboard-document-list')->color('warning'),
            Stat::make('Purchase Orders', (string) PurchaseOrder::where('status', 'pending_approval')->count())
                ->description('Awaiting approval')->icon('heroicon-o-shopping-cart')->color('warning'),
            Stat::make('Sales Orders', (string) SalesOrder::where('status', 'pending_approval')->count())
                ->description('Awaiting approval')->icon('heroicon-o-shopping-bag')->color('warning'),
            Stat::make('Stock Adjustments', (string) StockAdjustment::where('status', 'pending_approval')->count())
                ->description('Awaiting approval')->icon('heroicon-o-adjustments-horizontal')->color('warning'),
            Stat::make('Outgoing Payments', (string) OutgoingPayment::where('status', 'pending_approval')->count())
                ->description('Awaiting approval')->icon('heroicon-o-arrow-up-circle')->color('warning'),
        ];
    }
}
