<?php

namespace Tests\Feature\Reporting;

use App\Models\Core\Company;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Services\Reporting\ProfitLossService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitLossServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_computes_gross_and_net_profit_from_posted_entries(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $ar = ChartOfAccount::create(['company_id' => $company->id, 'code' => '1201', 'name' => 'AR', 'account_type' => 'asset']);
        $revenue = ChartOfAccount::create(['company_id' => $company->id, 'code' => '4101', 'name' => 'Sales Revenue', 'account_type' => 'revenue']);
        $cogs = ChartOfAccount::create(['company_id' => $company->id, 'code' => '5101', 'name' => 'COGS', 'account_type' => 'cogs']);
        $inventory = ChartOfAccount::create(['company_id' => $company->id, 'code' => '1301', 'name' => 'Inventory', 'account_type' => 'asset']);
        $expense = ChartOfAccount::create(['company_id' => $company->id, 'code' => '6101', 'name' => 'Operating Expense', 'account_type' => 'expense']);
        $cash = ChartOfAccount::create(['company_id' => $company->id, 'code' => '1101', 'name' => 'Cash', 'account_type' => 'asset']);

        // Revenue: Dr AR 1000 / Cr Revenue 1000.
        $sale = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-01-10', 'source_type' => 'customer_invoice', 'status' => 'posted']);
        $sale->lines()->createMany([
            ['chart_of_account_id' => $ar->id, 'debit' => 1000, 'credit' => 0],
            ['chart_of_account_id' => $revenue->id, 'debit' => 0, 'credit' => 1000],
        ]);

        // COGS: Dr COGS 600 / Cr Inventory 600.
        $cogsEntry = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-01-10', 'source_type' => 'delivery', 'status' => 'posted']);
        $cogsEntry->lines()->createMany([
            ['chart_of_account_id' => $cogs->id, 'debit' => 600, 'credit' => 0],
            ['chart_of_account_id' => $inventory->id, 'debit' => 0, 'credit' => 600],
        ]);

        // Expense: Dr Expense 100 / Cr Cash 100.
        $expenseEntry = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-01-15', 'source_type' => 'manual', 'status' => 'posted']);
        $expenseEntry->lines()->createMany([
            ['chart_of_account_id' => $expense->id, 'debit' => 100, 'credit' => 0],
            ['chart_of_account_id' => $cash->id, 'debit' => 0, 'credit' => 100],
        ]);

        $result = app(ProfitLossService::class)->generate($company->id, '2026-01-01', '2026-01-31');

        $this->assertSame(1000.0, $result['revenue']);
        $this->assertSame(600.0, $result['cogs']);
        $this->assertSame(400.0, $result['gross_profit']);
        $this->assertSame(100.0, $result['expenses']);
        $this->assertSame(300.0, $result['net_profit']);
    }

    public function test_it_excludes_draft_entries_and_entries_outside_the_range(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $ar = ChartOfAccount::create(['company_id' => $company->id, 'code' => '1201', 'name' => 'AR', 'account_type' => 'asset']);
        $revenue = ChartOfAccount::create(['company_id' => $company->id, 'code' => '4101', 'name' => 'Sales Revenue', 'account_type' => 'revenue']);

        $draft = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-01-10', 'source_type' => 'manual', 'status' => 'draft']);
        $draft->lines()->createMany([
            ['chart_of_account_id' => $ar->id, 'debit' => 999, 'credit' => 0],
            ['chart_of_account_id' => $revenue->id, 'debit' => 0, 'credit' => 999],
        ]);

        $outside = JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2025-01-10', 'source_type' => 'manual', 'status' => 'posted']);
        $outside->lines()->createMany([
            ['chart_of_account_id' => $ar->id, 'debit' => 888, 'credit' => 0],
            ['chart_of_account_id' => $revenue->id, 'debit' => 0, 'credit' => 888],
        ]);

        $result = app(ProfitLossService::class)->generate($company->id, '2026-01-01', '2026-01-31');

        $this->assertSame(0.0, $result['revenue']);
    }
}
