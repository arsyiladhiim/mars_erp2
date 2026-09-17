<?php

namespace App\Filament\Pages\Reports;

use App\Models\Core\Company;
use App\Services\Reporting\SalesDashboardService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class SalesDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Sales Dashboard';

    protected static ?string $title = 'Sales Dashboard';

    protected string $view = 'filament.pages.reports.sales-dashboard';

    public string $from = '';

    public string $to = '';

    public function mount(): void
    {
        $this->from = now()->startOfYear()->toDateString();
        $this->to = now()->toDateString();
    }

    public function getSummaryProperty(): array
    {
        $company = Company::first();

        return $company ? app(SalesDashboardService::class)->summary($company->id, $this->from, $this->to) : ['invoice_count' => 0, 'total_sales' => 0];
    }

    public function getTopProductsProperty(): Collection
    {
        $company = Company::first();

        return $company ? app(SalesDashboardService::class)->topProducts($company->id, $this->from, $this->to) : collect();
    }

    public function getTopCustomersProperty(): Collection
    {
        $company = Company::first();

        return $company ? app(SalesDashboardService::class)->topCustomers($company->id, $this->from, $this->to) : collect();
    }
}
