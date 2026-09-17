<?php

namespace App\Filament\Pages\Reports;

use App\Models\Core\Company;
use App\Services\Reporting\ProfitLossService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ProfitLossReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Profit & Loss';

    protected static ?string $title = 'Profit & Loss';

    protected string $view = 'filament.pages.reports.profit-loss';

    public string $from = '';

    public string $to = '';

    public function mount(): void
    {
        $this->from = now()->startOfYear()->toDateString();
        $this->to = now()->toDateString();
    }

    public function getFiguresProperty(): array
    {
        $company = Company::first();

        if (! $company) {
            return ['revenue' => 0, 'cogs' => 0, 'gross_profit' => 0, 'expenses' => 0, 'net_profit' => 0];
        }

        return app(ProfitLossService::class)->generate($company->id, $this->from, $this->to);
    }
}
