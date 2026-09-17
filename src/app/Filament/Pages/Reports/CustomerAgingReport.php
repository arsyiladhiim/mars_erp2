<?php

namespace App\Filament\Pages\Reports;

use App\Models\Core\Company;
use App\Services\Finance\AgingReportService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class CustomerAgingReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'AR Aging';

    protected static ?string $title = 'Accounts Receivable Aging';

    protected string $view = 'filament.pages.reports.aging';

    public string $asOfDate = '';

    public function mount(): void
    {
        $this->asOfDate = now()->toDateString();
    }

    public function getPartnerLabelProperty(): string
    {
        return 'Customer';
    }

    public function getRowsProperty(): Collection
    {
        $company = Company::first();

        if (! $company) {
            return collect();
        }

        return app(AgingReportService::class)->customerAging($company->id, $this->asOfDate);
    }
}
