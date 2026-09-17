<?php

namespace Tests\Feature\Finance;

use App\Models\Core\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test for the custom Filament report Pages (Trial Balance, General
 * Ledger, AR/AP Aging) — these are plain Blade views, not covered by the
 * calculation-service unit tests, so this is the only thing that actually
 * exercises the Livewire mount()/computed-property/view wiring end to end.
 */
class ReportPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticatedUser(): User
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        return User::create([
            'name' => 'Admin', 'email' => 'admin@test.local', 'password' => 'x',
            'company_id' => $company->id, 'is_active' => true,
        ]);
    }

    public function test_trial_balance_report_renders(): void
    {
        $this->actingAs($this->authenticatedUser())
            ->get('/admin/trial-balance-report')
            ->assertOk()
            ->assertSee('Trial Balance');
    }

    public function test_general_ledger_report_renders(): void
    {
        $this->actingAs($this->authenticatedUser())
            ->get('/admin/general-ledger-report')
            ->assertOk()
            ->assertSee('General Ledger');
    }

    public function test_customer_aging_report_renders(): void
    {
        $this->actingAs($this->authenticatedUser())
            ->get('/admin/customer-aging-report')
            ->assertOk()
            ->assertSee('Accounts Receivable Aging');
    }

    public function test_supplier_aging_report_renders(): void
    {
        $this->actingAs($this->authenticatedUser())
            ->get('/admin/supplier-aging-report')
            ->assertOk()
            ->assertSee('Accounts Payable Aging');
    }
}
