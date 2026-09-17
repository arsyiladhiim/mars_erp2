<?php

namespace Tests\Feature\Finance;

use App\Models\Core\Company;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Services\Finance\GeneralLedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralLedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_lines_in_date_order_with_a_running_balance(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $cash = ChartOfAccount::create(['company_id' => $company->id, 'code' => '1101', 'name' => 'Cash', 'account_type' => 'asset']);
        $revenue = ChartOfAccount::create(['company_id' => $company->id, 'code' => '4101', 'name' => 'Sales Revenue', 'account_type' => 'revenue']);

        $e1 = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-01-05', 'source_type' => 'manual', 'status' => 'posted']);
        $e1->lines()->createMany([
            ['chart_of_account_id' => $cash->id, 'debit' => 500, 'credit' => 0],
            ['chart_of_account_id' => $revenue->id, 'debit' => 0, 'credit' => 500],
        ]);

        $e2 = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-01-10', 'source_type' => 'manual', 'status' => 'posted']);
        $e2->lines()->createMany([
            ['chart_of_account_id' => $revenue->id, 'debit' => 100, 'credit' => 0],
            ['chart_of_account_id' => $cash->id, 'debit' => 0, 'credit' => 100],
        ]);

        $rows = app(GeneralLedgerService::class)->forAccount($cash->id, '2026-01-01', '2026-01-31');

        $this->assertCount(2, $rows);
        $this->assertSame(500.0, $rows[0]['debit']);
        $this->assertSame(500.0, $rows[0]['running_balance']);
        $this->assertSame(100.0, $rows[1]['credit']);
        $this->assertSame(400.0, $rows[1]['running_balance']);
    }

    public function test_it_excludes_lines_outside_the_date_range(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $cash = ChartOfAccount::create(['company_id' => $company->id, 'code' => '1101', 'name' => 'Cash', 'account_type' => 'asset']);
        $revenue = ChartOfAccount::create(['company_id' => $company->id, 'code' => '4101', 'name' => 'Sales Revenue', 'account_type' => 'revenue']);

        $outside = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-02-05', 'source_type' => 'manual', 'status' => 'posted']);
        $outside->lines()->createMany([
            ['chart_of_account_id' => $cash->id, 'debit' => 500, 'credit' => 0],
            ['chart_of_account_id' => $revenue->id, 'debit' => 0, 'credit' => 500],
        ]);

        $rows = app(GeneralLedgerService::class)->forAccount($cash->id, '2026-01-01', '2026-01-31');

        $this->assertCount(0, $rows);
    }
}
