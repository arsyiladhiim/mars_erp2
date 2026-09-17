<?php

namespace Tests\Feature\Reporting;

use App\Models\Core\Company;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Services\Reporting\BalanceSheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceSheetServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_assets_equal_liabilities_plus_equity(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $cash = ChartOfAccount::create(['company_id' => $company->id, 'code' => '1101', 'name' => 'Cash', 'account_type' => 'asset']);
        $ap = ChartOfAccount::create(['company_id' => $company->id, 'code' => '2101', 'name' => 'Accounts Payable', 'account_type' => 'liability']);
        $equity = ChartOfAccount::create(['company_id' => $company->id, 'code' => '3101', 'name' => 'Common Stock', 'account_type' => 'equity']);

        // Owner invests 5000 cash: Dr Cash 5000 / Cr Equity 5000.
        $invest = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-01-01', 'source_type' => 'opening_balance', 'status' => 'posted']);
        $invest->lines()->createMany([
            ['chart_of_account_id' => $cash->id, 'debit' => 5000, 'credit' => 0],
            ['chart_of_account_id' => $equity->id, 'debit' => 0, 'credit' => 5000],
        ]);

        // Buys on credit: Dr Cash reduces via a purchase — simpler: books a payable directly.
        // Dr Cash is not touched; instead simulate an expense on credit: Dr Cash 0 skip, just book AP increase against equity draw for test simplicity:
        $creditPurchase = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-01-05', 'source_type' => 'supplier_invoice', 'status' => 'posted']);
        $creditPurchase->lines()->createMany([
            ['chart_of_account_id' => $cash->id, 'debit' => 1000, 'credit' => 0],
            ['chart_of_account_id' => $ap->id, 'debit' => 0, 'credit' => 1000],
        ]);

        $result = app(BalanceSheetService::class)->generate($company->id, '2026-01-31');

        $this->assertSame(6000.0, $result['total_assets']);
        $this->assertSame(1000.0, $result['total_liabilities']);
        $this->assertSame(5000.0, $result['total_equity']);
        $this->assertSame($result['total_assets'], $result['total_liabilities_and_equity']);
    }

    public function test_it_excludes_accounts_with_no_activity(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        ChartOfAccount::create(['company_id' => $company->id, 'code' => '1102', 'name' => 'Unused Account', 'account_type' => 'asset']);

        $result = app(BalanceSheetService::class)->generate($company->id, '2026-01-31');

        $this->assertTrue($result['accounts']->isEmpty());
    }
}
