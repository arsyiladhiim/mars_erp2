<?php

namespace App\Filament\Pages\Reports;

use App\Models\Core\Company;
use App\Services\Reporting\PurchasingDashboardService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class PurchasingDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Purchasing Dashboard';

    protected static ?string $title = 'Purchasing Dashboard';

    protected string $view = 'filament.pages.reports.purchasing-dashboard';

    public string $from = '';

    public string $to = '';

    public function mount(): void
    {
        $this->from = now()->startOfYear()->toDateString();
        $this->to = now()->toDateString();
    }

    public function getOutstandingPurchaseOrdersProperty(): Collection
    {
        $company = Company::first();

        return $company ? app(PurchasingDashboardService::class)->outstandingPurchaseOrders($company->id) : collect();
    }

    public function getSupplierSpendProperty(): Collection
    {
        $company = Company::first();

        return $company ? app(PurchasingDashboardService::class)->supplierSpend($company->id, $this->from, $this->to) : collect();
    }
}
