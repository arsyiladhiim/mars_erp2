<?php

namespace Tests\Feature\Finance;

use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use App\Models\Finance\JournalEntry;
use App\Models\User;
use App\Services\Finance\PeriodClosingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PeriodClosingTest extends TestCase
{
    use RefreshDatabase;

    protected function makePeriod(Company $company): AccountingPeriod
    {
        $fy = FiscalYear::create(['company_id' => $company->id, 'code' => 'FY2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'open']);

        return AccountingPeriod::create([
            'fiscal_year_id' => $fy->id, 'name' => 'January 2026', 'period_number' => 1,
            'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => 'open',
        ]);
    }

    public function test_closing_an_open_period_with_no_draft_entries_succeeds(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $user = User::create(['name' => 'Accountant', 'email' => 'acct@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $period = $this->makePeriod($company);

        app(PeriodClosingService::class)->close($period, $user);

        $period->refresh();
        $this->assertSame('closed', $period->status);
        $this->assertNotNull($period->closed_at);
        $this->assertSame($user->id, $period->closed_by);
    }

    public function test_closing_is_blocked_while_a_draft_journal_entry_falls_within_the_period(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $user = User::create(['name' => 'Accountant', 'email' => 'acct2@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $period = $this->makePeriod($company);

        JournalEntry::create(['company_id' => $company->id, 'entry_date' => '2026-01-15', 'source_type' => 'manual', 'status' => 'draft']);

        $this->expectException(RuntimeException::class);
        app(PeriodClosingService::class)->close($period, $user);
    }

    public function test_closing_an_already_closed_period_is_rejected(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $user = User::create(['name' => 'Accountant', 'email' => 'acct3@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $period = $this->makePeriod($company);
        $period->forceFill(['status' => 'closed'])->save();

        $this->expectException(RuntimeException::class);
        app(PeriodClosingService::class)->close($period, $user);
    }

    public function test_reopening_a_closed_period_succeeds(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $user = User::create(['name' => 'Accountant', 'email' => 'acct4@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $period = $this->makePeriod($company);
        app(PeriodClosingService::class)->close($period, $user);

        app(PeriodClosingService::class)->reopen($period->fresh(), $user);

        $this->assertSame('open', $period->fresh()->status);
        $this->assertNull($period->fresh()->closed_at);
    }

    public function test_a_locked_period_cannot_be_reopened(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $user = User::create(['name' => 'Accountant', 'email' => 'acct5@test.local', 'password' => 'x', 'company_id' => $company->id]);
        $period = $this->makePeriod($company);
        $period->forceFill(['status' => 'locked'])->save();

        $this->expectException(RuntimeException::class);
        app(PeriodClosingService::class)->reopen($period, $user);
    }
}
