<?php

namespace App\Filament\Pages\Reports;

use App\Models\Core\Company;
use App\Services\Reporting\InventoryDashboardService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class InventoryDashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Inventory Dashboard';

    protected static ?string $title = 'Inventory Dashboard';

    protected string $view = 'filament.pages.reports.inventory-dashboard';

    public function getTotalStockValueProperty(): float
    {
        $company = Company::first();

        return $company ? app(InventoryDashboardService::class)->totalStockValue($company->id) : 0.0;
    }

    public function getLowStockItemsProperty(): Collection
    {
        $company = Company::first();

        return $company ? app(InventoryDashboardService::class)->lowStockItems($company->id) : collect();
    }

    public function getRecentMovementsProperty(): Collection
    {
        $company = Company::first();

        return $company ? app(InventoryDashboardService::class)->recentMovements($company->id) : collect();
    }
}
