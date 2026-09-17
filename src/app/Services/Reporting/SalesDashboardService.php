<?php

namespace App\Services\Reporting;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Sales Dashboard (PRD §27/§56): sales summary, top products, and top
 * customers for a date range, read from posted Customer Invoices.
 */
class SalesDashboardService
{
    public function summary(int $companyId, string $from, string $to): array
    {
        $row = DB::table('sales_customer_invoices')
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereDate('invoice_date', '>=', $from)
            ->whereDate('invoice_date', '<=', $to)
            ->selectRaw('COUNT(*) as invoice_count, COALESCE(SUM(grand_total), 0) as total_sales')
            ->first();

        return [
            'invoice_count' => (int) $row->invoice_count,
            'total_sales' => round((float) $row->total_sales, 2),
        ];
    }

    public function topProducts(int $companyId, string $from, string $to, int $limit = 10): Collection
    {
        return DB::table('sales_customer_invoice_lines as l')
            ->join('sales_customer_invoices as inv', 'inv.id', '=', 'l.customer_invoice_id')
            ->join('master_items as i', 'i.id', '=', 'l.item_id')
            ->where('inv.company_id', $companyId)
            ->whereNotIn('inv.status', ['draft', 'cancelled'])
            ->whereDate('inv.invoice_date', '>=', $from)
            ->whereDate('inv.invoice_date', '<=', $to)
            ->groupBy('i.id', 'i.name', 'i.sku')
            ->select(['i.sku', 'i.name', DB::raw('SUM(l.quantity) as quantity_sold'), DB::raw('SUM(l.line_total) as revenue')])
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'sku' => $row->sku, 'name' => $row->name,
                'quantity_sold' => (float) $row->quantity_sold, 'revenue' => round((float) $row->revenue, 2),
            ]);
    }

    public function topCustomers(int $companyId, string $from, string $to, int $limit = 10): Collection
    {
        return DB::table('sales_customer_invoices as inv')
            ->join('master_business_partners as bp', 'bp.id', '=', 'inv.business_partner_id')
            ->where('inv.company_id', $companyId)
            ->whereNotIn('inv.status', ['draft', 'cancelled'])
            ->whereDate('inv.invoice_date', '>=', $from)
            ->whereDate('inv.invoice_date', '<=', $to)
            ->groupBy('bp.id', 'bp.name')
            ->select(['bp.name as customer', DB::raw('COUNT(inv.id) as invoice_count'), DB::raw('SUM(inv.grand_total) as total_revenue')])
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'customer' => $row->customer, 'invoice_count' => (int) $row->invoice_count,
                'total_revenue' => round((float) $row->total_revenue, 2),
            ]);
    }
}
