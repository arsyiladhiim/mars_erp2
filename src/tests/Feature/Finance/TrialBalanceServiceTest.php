<?php

namespace Tests\Feature\Finance;

use App\Models\Core\Company;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Services\Finance\TrialBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrialBalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sums_posted_lines_per_account_and_balances_globally(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $cash = ChartOfAccount::create(['company_id' => $company->id, 'code' => '1101', 'name' => 'Cash', 'account_type' => 'asset']);
        $revenue = ChartOfAccount::create(['company_id' => $company->id, 'code' => '4101', 'name' => 'Sales Revenue', 'account_type' => 'revenue']);

        $entry1 = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-01-10', 'source_type' => 'manual', 'status' => 'posted']);
        $entry1->lines()->createMany([
            ['chart_of_account_id' => $cash->id, 'debit' => 500, 'credit' => 0],
            ['chart_of_account_id' => $revenue->id, 'debit' => 0, 'credit' => 500],
        ]);

        $entry2 = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-01-15', 'source_type' => 'manual', 'status' => 'posted']);
        $entry2->lines()->createMany([
            ['chart_of_account_id' => $cash->id, 'debit' => 200, 'credit' => 0],
            ['chart_of_account_id' => $revenue->id, 'debit' => 0, 'credit' => 200],
        ]);

        $rows = app(TrialBalanceService::class)->generate($company->id, '2026-01-31');

        $cashRow = $rows->firstWhere('code', '1101');
        $revenueRow = $rows->firstWhere('code', '4101');

        $this->assertSame(700.0, $cashRow['debit']);
        $this->assertSame(0.0, $cashRow['credit']);
        $this->assertSame(700.0, $revenueRow['credit']);
        $this->assertSame($rows->sum('debit'), $rows->sum('credit'));
    }

    public function test_it_excludes_draft_entries_and_entries_after_the_as_of_date(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $cash = ChartOfAccount::create(['company_id' => $company->id, 'code' => '1101', 'name' => 'Cash', 'account_type' => 'asset']);
        $revenue = ChartOfAccount::create(['company_id' => $company->id, 'code' => '4101', 'name' => 'Sales Revenue', 'account_type' => 'revenue']);

        $draft = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-01-10', 'source_type' => 'manual', 'status' => 'draft']);
        $draft->lines()->createMany([
            ['chart_of_account_id' => $cash->id, 'debit' => 999, 'credit' => 0],
            ['chart_of_account_id' => $revenue->id, 'debit' => 0, 'credit' => 999],
        ]);

        $future = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-03-01', 'source_type' => 'manual', 'status' => 'posted']);
        $future->lines()->createMany([
            ['chart_of_account_id' => $cash->id, 'debit' => 888, 'credit' => 0],
            ['chart_of_account_id' => $revenue->id, 'debit' => 0, 'credit' => 888],
        ]);

        $rows = app(TrialBalanceService::class)->generate($company->id, '2026-01-31');

        $this->assertTrue($rows->isEmpty());
    }
}
