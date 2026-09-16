<?php

namespace App\Filament\Widgets;

use App\Models\Sales\CustomerInvoice;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Executive Dashboard — posted-sales trend over the last 6 months (PRD §27).
 * Reads directly from customer invoice headers; once Finance's GL posting is
 * hardened this should read from the ledger instead (see ARCHITECTURE-DECISIONS.md).
 */
class SalesTrendChartWidget extends ChartWidget
{
    protected ?string $heading = 'Sales Trend (6 Bulan Terakhir)';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => Carbon::now()->subMonths($i)->startOfMonth());

        $totals = CustomerInvoice::query()
            ->whereNotIn('status', ['cancelled'])
            ->where('invoice_date', '>=', $months->first())
            ->selectRaw("date_trunc('month', invoice_date) as month, SUM(grand_total) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $data = $months->map(function (Carbon $month) use ($totals) {
            foreach ($totals as $key => $total) {
                if (Carbon::parse($key)->isSameMonth($month)) {
                    return (float) $total;
                }
            }

            return 0;
        });

        return [
            'datasets' => [
                [
                    'label' => 'Penjualan (Rp)',
                    'data' => $data->values(),
                    'borderColor' => '#B47854',
                    'backgroundColor' => 'rgba(180, 120, 84, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $months->map(fn (Carbon $m) => $m->translatedFormat('M Y'))->values(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
