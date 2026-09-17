<?php

namespace Tests\Feature\Finance;

use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Services\Finance\JournalEntryPostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class JournalEntryPostingTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected ChartOfAccount $cash;

    protected ChartOfAccount $revenue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $fy = FiscalYear::create(['company_id' => $this->company->id, 'code' => 'FY2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'open']);
        AccountingPeriod::create(['fiscal_year_id' => $fy->id, 'name' => 'January 2026', 'period_number' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'open']);

        $this->cash = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '1101', 'name' => 'Cash', 'account_type' => 'asset']);
        $this->revenue = ChartOfAccount::create(['company_id' => $this->company->id, 'code' => '4101', 'name' => 'Sales Revenue', 'account_type' => 'revenue']);
    }

    protected function makeEntry(): JournalEntry
    {
        return JournalEntry::create(['company_id' => $this->company->id, 'entry_date' => '2026-01-10', 'source_type' => 'manual']);
    }

    public function test_number_is_auto_generated(): void
    {
        $entry = $this->makeEntry();

        $this->assertStringStartsWith('JE-', $entry->number);
    }

    public function test_a_balanced_entry_posts_and_derives_header_totals_from_lines(): void
    {
        $entry = $this->makeEntry();
        $entry->lines()->createMany([
            ['chart_of_account_id' => $this->cash->id, 'debit' => 500, 'credit' => 0],
            ['chart_of_account_id' => $this->revenue->id, 'debit' => 0, 'credit' => 500],
        ]);

        app(JournalEntryPostingService::class)->post($entry->fresh());

        $entry->refresh();
        $this->assertSame('posted', $entry->status);
        $this->assertSame('500.00', $entry->total_debit);
        $this->assertSame('500.00', $entry->total_credit);
        $this->assertTrue($entry->isBalanced());
    }

    public function test_an_unbalanced_entry_is_rejected(): void
    {
        $entry = $this->makeEntry();
        $entry->lines()->createMany([
            ['chart_of_account_id' => $this->cash->id, 'debit' => 500, 'credit' => 0],
            ['chart_of_account_id' => $this->revenue->id, 'debit' => 0, 'credit' => 300],
        ]);

        $this->expectException(RuntimeException::class);
        app(JournalEntryPostingService::class)->post($entry->fresh());
    }

    public function test_an_entry_with_no_lines_is_rejected(): void
    {
        $entry = $this->makeEntry();

        $this->expectException(RuntimeException::class);
        app(JournalEntryPostingService::class)->post($entry);
    }

    public function test_posting_is_blocked_when_the_accounting_period_is_closed(): void
    {
        AccountingPeriod::query()->update(['status' => 'closed']);

        $entry = $this->makeEntry();
        $entry->lines()->createMany([
            ['chart_of_account_id' => $this->cash->id, 'debit' => 100, 'credit' => 0],
            ['chart_of_account_id' => $this->revenue->id, 'debit' => 0, 'credit' => 100],
        ]);

        $this->expectException(RuntimeException::class);
        app(JournalEntryPostingService::class)->post($entry->fresh());
    }

    public function test_posting_twice_is_rejected(): void
    {
        $entry = $this->makeEntry();
        $entry->lines()->createMany([
            ['chart_of_account_id' => $this->cash->id, 'debit' => 100, 'credit' => 0],
            ['chart_of_account_id' => $this->revenue->id, 'debit' => 0, 'credit' => 100],
        ]);

        app(JournalEntryPostingService::class)->post($entry->fresh());

        $this->expectException(RuntimeException::class);
        app(JournalEntryPostingService::class)->post($entry->fresh());
    }
}
