<?php

namespace App\Filament\Pages\Reports;

use App\Models\Core\Company;
use App\Services\Finance\TrialBalanceService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class TrialBalanceReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Trial Balance';

    protected static ?string $title = 'Trial Balance';

    protected string $view = 'filament.pages.reports.trial-balance';

    public string $asOfDate = '';

    public function mount(): void
    {
        $this->asOfDate = now()->toDateString();
    }

    public function getRowsProperty(): Collection
    {
        $company = Company::first();

        if (! $company) {
            return collect();
        }

        return app(TrialBalanceService::class)->generate($company->id, $this->asOfDate);
    }
}
