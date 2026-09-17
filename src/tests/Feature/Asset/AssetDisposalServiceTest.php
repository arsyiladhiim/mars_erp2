<?php

namespace Tests\Feature\Asset;

use App\Models\Asset\AssetCategory;
use App\Models\Asset\FixedAsset;
use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use App\Models\Finance\BankAccount;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\GlAccountMapping;
use App\Services\Asset\AssetDisposalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use RuntimeException;
use Tests\TestCase;

class AssetDisposalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected FixedAsset $asset;

    protected BankAccount $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $fy = FiscalYear::create(['company_id' => $this->company->id, 'code' => 'FY2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'open']);
        AccountingPeriod::create(['fiscal_year_id' => $fy->id, 'name' => 'January 2026', 'period_number' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'open']);

        $category = AssetCategory::create(['code' => 'CAT1', 'name' => 'Computers']);

        $accumAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1501', 'name' => 'Accumulated Depreciation', 'account_type' => 'asset']);
        $fixedAssetsAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1502', 'name' => 'Fixed Assets at Cost', 'account_type' => 'asset']);
        $gainLossAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '6104', 'name' => 'Gain/Loss on Asset Disposal', 'account_type' => 'expense']);
        $bankChartAccount = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1101', 'name' => 'Bank BCA', 'account_type' => 'asset']);

        GlAccountMapping::ensureSeeded($this->company->id);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'accumulated_depreciation')->update(['chart_of_account_id' => $accumAccount->id]);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'fixed_assets')->update(['chart_of_account_id' => $fixedAssetsAccount->id]);
        GlAccountMapping::where('company_id', $this->company->id)->where('key', 'asset_disposal_gain_loss')->update(['chart_of_account_id' => $gainLossAccount->id]);

        $this->bankAccount = BankAccount::create([
            'company_id' => $this->company->id, 'chart_of_account_id' => $bankChartAccount->id,
            'account_name' => 'Main BCA', 'account_number' => '123456', 'bank_name' => 'BCA',
        ]);

        $this->asset = FixedAsset::create([
            'company_id' => $this->company->id, 'code' => 'AST-1', 'name' => 'Laptop',
            'asset_category_id' => $category->id, 'purchase_date' => '2024-01-01',
            'acquisition_cost' => 10000, 'residual_value' => 0, 'useful_life_months' => 12,
            'accumulated_depreciation' => 6000, 'status' => 'in_use',
        ]);
    }

    public function test_a_write_off_with_no_proceeds_books_a_loss_equal_to_book_value(): void
    {
        $journalEntry = app(AssetDisposalService::class)->dispose($this->asset, Carbon::parse('2026-01-15'));

        $this->assertTrue($journalEntry->isBalanced());
        // Dr Accum Dep 6000 + Dr Loss 4000 = Cr Fixed Assets 10000.
        $this->assertSame('10000.00', $journalEntry->total_debit);
        $this->assertSame('disposed', $this->asset->fresh()->status);

        $lossLine = $journalEntry->lines()->where('chart_of_account_id', ChartOfAccount::where('code', '6104')->first()->id)->first();
        $this->assertSame('4000.00', $lossLine->debit);
    }

    public function test_proceeds_exceeding_book_value_book_a_gain(): void
    {
        $journalEntry = app(AssetDisposalService::class)->dispose($this->asset, Carbon::parse('2026-01-15'), 7000, $this->bankAccount->id);

        // Dr Accum Dep 6000 + Dr Cash 7000 = Cr Fixed Assets 10000 + Cr Gain 3000.
        $this->assertTrue($journalEntry->isBalanced());
        $this->assertSame('13000.00', $journalEntry->total_debit);

        $gainLine = $journalEntry->lines()->where('chart_of_account_id', ChartOfAccount::where('code', '6104')->first()->id)->first();
        $this->assertSame('3000.00', $gainLine->credit);
    }

    public function test_proceeds_exactly_matching_book_value_needs_no_gain_loss_line(): void
    {
        $journalEntry = app(AssetDisposalService::class)->dispose($this->asset, Carbon::parse('2026-01-15'), 4000, $this->bankAccount->id);

        $this->assertTrue($journalEntry->isBalanced());
        $this->assertSame(3, $journalEntry->lines()->count()); // accum dep, cash, fixed assets only
    }

    public function test_proceeds_require_a_bank_account(): void
    {
        $this->expectException(RuntimeException::class);
        app(AssetDisposalService::class)->dispose($this->asset, Carbon::parse('2026-01-15'), 1000);
    }

    public function test_disposing_twice_is_rejected(): void
    {
        app(AssetDisposalService::class)->dispose($this->asset, Carbon::parse('2026-01-15'));

        $this->expectException(RuntimeException::class);
        app(AssetDisposalService::class)->dispose($this->asset->fresh(), Carbon::parse('2026-01-20'));
    }
}
