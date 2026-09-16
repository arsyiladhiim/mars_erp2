<?php

namespace App\Filament\Widgets;

use App\Models\Procurement\PurchaseOrder;
use App\Models\Sales\SalesOrder;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Executive Dashboard — purchasing vs. sales order value, last 6 months (PRD §27).
 */
class PurchaseVsSalesChartWidget extends ChartWidget
{
    protected ?string $heading = 'Purchasing vs Sales (6 Bulan Terakhir)';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => Carbon::now()->subMonths($i)->startOfMonth());
        $start = $months->first();

        $monthlySum = function (string $model, string $dateColumn, array $excludeStatuses) use ($start, $months) {
            $rows = $model::query()
                ->whereNotIn('status', $excludeStatuses)
                ->where($dateColumn, '>=', $start)
                ->selectRaw("date_trunc('month', {$dateColumn}) as month, SUM(grand_total) as total")
                ->groupBy('month')
                ->pluck('total', 'month');

            return $months->map(function (Carbon $month) use ($rows) {
                foreach ($rows as $key => $total) {
                    if (Carbon::parse($key)->isSameMonth($month)) {
                        return (float) $total;
                    }
                }

                return 0;
            })->values();
        };

        return [
            'datasets' => [
                [
                    'label' => 'Purchasing (Rp)',
                    'data' => $monthlySum(PurchaseOrder::class, 'order_date', ['cancelled', 'rejected']),
                    'backgroundColor' => '#3C3C48',
                ],
                [
                    'label' => 'Sales (Rp)',
                    'data' => $monthlySum(SalesOrder::class, 'order_date', ['cancelled', 'rejected']),
                    'backgroundColor' => '#B47854',
                ],
            ],
            'labels' => $months->map(fn (Carbon $m) => $m->translatedFormat('M Y'))->values(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
