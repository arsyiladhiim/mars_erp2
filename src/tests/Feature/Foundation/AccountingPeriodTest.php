<?php

namespace Tests\Feature\Foundation;

use App\Models\Core\AccountingPeriod;
use App\Models\Core\Company;
use App\Models\Core\FiscalYear;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class AccountingPeriodTest extends TestCase
{
    use RefreshDatabase;

    protected function makeCompanyWithJanuaryPeriod(string $status = 'open'): array
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $fy = FiscalYear::create([
            'company_id' => $company->id, 'code' => 'FY2026',
            'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'status' => 'open',
        ]);
        $period = AccountingPeriod::create([
            'fiscal_year_id' => $fy->id, 'name' => 'January 2026', 'period_number' => 1,
            'start_date' => '2026-01-01', 'end_date' => '2026-01-31', 'status' => $status,
        ]);

        return [$company, $period];
    }

    public function test_it_finds_the_period_covering_a_date(): void
    {
        [$company, $period] = $this->makeCompanyWithJanuaryPeriod();

        $found = AccountingPeriod::forDate('2026-01-15', $company->id);

        $this->assertSame($period->id, $found?->id);
    }

    public function test_posting_is_allowed_in_an_open_period(): void
    {
        [$company] = $this->makeCompanyWithJanuaryPeriod('open');

        AccountingPeriod::assertOpenForPosting('2026-01-15', $company->id);
        $this->addToAssertionCount(1); // no exception thrown
    }

    public function test_posting_is_blocked_in_a_closed_period(): void
    {
        [$company] = $this->makeCompanyWithJanuaryPeriod('closed');

        $this->expectException(RuntimeException::class);
        AccountingPeriod::assertOpenForPosting('2026-01-15', $company->id);
    }

    public function test_posting_is_blocked_in_a_locked_period(): void
    {
        [$company] = $this->makeCompanyWithJanuaryPeriod('locked');

        $this->expectException(RuntimeException::class);
        AccountingPeriod::assertOpenForPosting('2026-01-20', $company->id);
    }

    public function test_posting_is_allowed_when_no_period_is_configured_for_the_date(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        AccountingPeriod::assertOpenForPosting('2099-01-01', $company->id);
        $this->addToAssertionCount(1); // no exception thrown — unconfigured calendar must not block posting
    }
}
