<?php

namespace App\Filament\Pages\Reports;

use App\Models\Core\Company;
use App\Services\Reporting\BalanceSheetService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BalanceSheetReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Balance Sheet';

    protected static ?string $title = 'Balance Sheet';

    protected string $view = 'filament.pages.reports.balance-sheet';

    public string $asOfDate = '';

    public function mount(): void
    {
        $this->asOfDate = now()->toDateString();
    }

    public function getFiguresProperty(): array
    {
        $company = Company::first();

        if (! $company) {
            return ['accounts' => collect(), 'total_assets' => 0, 'total_liabilities' => 0, 'total_equity' => 0, 'total_liabilities_and_equity' => 0];
        }

        return app(BalanceSheetService::class)->generate($company->id, $this->asOfDate);
    }
}
