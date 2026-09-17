<?php

namespace Tests\Feature\Asset;

use App\Models\Asset\AssetCategory;
use App\Models\Asset\FixedAsset;
use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\GlAccountMapping;
use App\Services\Asset\AssetDepreciationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use RuntimeException;
use Tests\TestCase;

class AssetDepreciationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected AssetCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $fy = FiscalYear::create(['company_id' => $this->company->id, 'code' => 'FY2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'open']);
        AccountingPeriod::create(['fiscal_year_id' => $fy->id, 'name' => 'January 2026', 'period_number' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'open']);
        AccountingPeriod::create(['fiscal_year_id' => $fy->id, 'name' => 'February 2026', 'period_number' => 2, 'start_date' => '2026-02-01', 'end_date' => '2026-02-28', 'status' => 'open']);

        $this->category = AssetCategory::create(['code' => 'CAT1', 'name' => 'Computers']);

        $expenseAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '6102', 'name' => 'Depreciation Expense', 'account_type' => 'expense']);
        $accumAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1501', 'name' => 'Accumulated Depreciation', 'account_type' => 'asset']);

        GlAccountMapping::ensureSeeded($this->company->id);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'depreciation_expense')->update(['chart_of_account_id' => $expenseAccount->id]);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'accumulated_depreciation')->update(['chart_of_account_id' => $accumAccount->id]);
    }

    protected function makeAsset(array $overrides = []): FixedAsset
    {
        return FixedAsset::create(array_merge([
            'company_id' => $this->company->id, 'code' => 'AST-1', 'name' => 'Laptop',
            'asset_category_id' => $this->category->id, 'purchase_date' => '2025-01-01',
            'acquisition_cost' => 12000, 'residual_value' => 0, 'useful_life_months' => 12,
            'depreciation_method' => 'straight_line', 'status' => 'in_use',
        ], $overrides));
    }

    public function test_it_posts_straight_line_depreciation_and_updates_the_asset(): void
    {
        $asset = $this->makeAsset();

        $journalEntry = app(AssetDepreciationService::class)->runForCompany($this->company->id, Carbon::parse('2026-01-15'));

        $this->assertNotNull($journalEntry);
        $this->assertTrue($journalEntry->isBalanced());
        $this->assertSame('1000.00', $journalEntry->total_debit); // 12000/12

        $asset->refresh();
        $this->assertSame('1000.00', $asset->accumulated_depreciation);

        $entry = $asset->depreciationEntries()->first();
        $this->assertSame('1000.00', $entry->depreciation_amount);
        $this->assertSame('11000.00', $entry->book_value_after);
    }

    public function test_running_the_same_period_twice_does_not_double_depreciate(): void
    {
        $asset = $this->makeAsset();

        app(AssetDepreciationService::class)->runForCompany($this->company->id, Carbon::parse('2026-01-15'));
        $second = app(AssetDepreciationService::class)->runForCompany($this->company->id, Carbon::parse('2026-01-20'));

        $this->assertNull($second); // same month (Jan 15 and Jan 20 both fall in January) — nothing new to post
        $this->assertSame('1000.00', $asset->fresh()->accumulated_depreciation);
        $this->assertSame(1, $asset->depreciationEntries()->count());
    }

    public function test_depreciation_stops_once_the_residual_value_is_reached(): void
    {
        $asset = $this->makeAsset(['acquisition_cost' => 1200, 'residual_value' => 200, 'useful_life_months' => 10, 'accumulated_depreciation' => 950]);
        // monthly = (1200-200)/10 = 100; remaining = 1000-950 = 50 -> capped at 50.

        app(AssetDepreciationService::class)->runForCompany($this->company->id, Carbon::parse('2026-01-15'));

        $asset->refresh();
        $this->assertSame('1000.00', $asset->accumulated_depreciation);
        $this->assertSame('50.00', $asset->depreciationEntries()->first()->depreciation_amount);
    }

    public function test_a_fully_depreciated_asset_is_skipped(): void
    {
        $asset = $this->makeAsset(['acquisition_cost' => 1000, 'residual_value' => 0, 'accumulated_depreciation' => 1000]);

        $journalEntry = app(AssetDepreciationService::class)->runForCompany($this->company->id, Carbon::parse('2026-01-15'));

        $this->assertNull($journalEntry);
    }

    public function test_declining_balance_assets_are_not_touched(): void
    {
        $this->makeAsset(['depreciation_method' => 'declining_balance']);

        $journalEntry = app(AssetDepreciationService::class)->runForCompany($this->company->id, Carbon::parse('2026-01-15'));

        $this->assertNull($journalEntry);
    }

    public function test_draft_and_disposed_assets_are_excluded(): void
    {
        $this->makeAsset(['code' => 'AST-DRAFT', 'status' => 'draft']);
        $this->makeAsset(['code' => 'AST-DISPOSED', 'status' => 'disposed']);

        $journalEntry = app(AssetDepreciationService::class)->runForCompany($this->company->id, Carbon::parse('2026-01-15'));

        $this->assertNull($journalEntry);
    }

    public function test_a_second_month_accumulates_on_top_of_the_first(): void
    {
        $asset = $this->makeAsset();

        app(AssetDepreciationService::class)->runForCompany($this->company->id, Carbon::parse('2026-01-15'));
        app(AssetDepreciationService::class)->runForCompany($this->company->id, Carbon::parse('2026-02-15'));

        $this->assertSame('2000.00', $asset->fresh()->accumulated_depreciation);
        $this->assertSame(2, $asset->depreciationEntries()->count());
    }

    public function test_posting_is_blocked_when_the_period_is_closed(): void
    {
        $this->makeAsset();
        AccountingPeriod::query()->update(['status' => 'closed']);

        $this->expectException(RuntimeException::class);
        app(AssetDepreciationService::class)->runForCompany($this->company->id, Carbon::parse('2026-01-15'));
    }
}
