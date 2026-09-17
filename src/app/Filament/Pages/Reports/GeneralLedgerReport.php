<?php

namespace App\Filament\Pages\Reports;

use App\Models\Core\Company;
use App\Models\Finance\ChartOfAccount;
use App\Services\Finance\GeneralLedgerService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class GeneralLedgerReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'General Ledger';

    protected static ?string $title = 'General Ledger';

    protected string $view = 'filament.pages.reports.general-ledger';

    public ?int $chartOfAccountId = null;

    public string $from = '';

    public string $to = '';

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();
        $this->chartOfAccountId = $this->getAccountsProperty()->first()?->id;
    }

    public function getAccountsProperty(): Collection
    {
        $company = Company::first();

        if (! $company) {
            return collect();
        }

        return ChartOfAccount::query()->where('company_id', $company->id)->orderBy('code')->get();
    }

    public function getRowsProperty(): Collection
    {
        if (! $this->chartOfAccountId) {
            return collect();
        }

        return app(GeneralLedgerService::class)->forAccount($this->chartOfAccountId, $this->from, $this->to);
    }
}
