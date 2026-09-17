<?php

namespace App\Services\Reporting;

use App\Models\Procurement\PurchaseOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Purchasing Dashboard (PRD §27/§56): outstanding POs and supplier spend
 * ranking, read from existing Purchase Order / line data.
 */
class PurchasingDashboardService
{
    public function outstandingPurchaseOrders(int $companyId): Collection
    {
        return PurchaseOrder::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['approved', 'sent', 'partially_received'])
            ->with('businessPartner')
            ->orderBy('order_date')
            ->get()
            ->map(fn (PurchaseOrder $po) => [
                'number' => $po->number,
                'supplier' => $po->businessPartner?->name,
                'order_date' => $po->order_date,
                'grand_total' => (float) $po->grand_total,
                'status' => $po->status,
            ]);
    }

    public function supplierSpend(int $companyId, string $from, string $to): Collection
    {
        return DB::table('procurement_purchase_orders as po')
            ->join('master_business_partners as bp', 'bp.id', '=', 'po.business_partner_id')
            ->where('po.company_id', $companyId)
            ->whereNotIn('po.status', ['draft', 'cancelled', 'rejected'])
            ->whereDate('po.order_date', '>=', $from)
            ->whereDate('po.order_date', '<=', $to)
            ->groupBy('bp.id', 'bp.name')
            ->select(['bp.name as supplier', DB::raw('COUNT(po.id) as order_count'), DB::raw('SUM(po.grand_total) as total_spend')])
            ->orderByDesc('total_spend')
            ->get()
            ->map(fn ($row) => [
                'supplier' => $row->supplier,
                'order_count' => (int) $row->order_count,
                'total_spend' => round((float) $row->total_spend, 2),
            ]);
    }
}
