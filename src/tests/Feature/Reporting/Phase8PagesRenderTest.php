<?php

namespace Tests\Feature\Reporting;

use App\Models\Core\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase8PagesRenderTest extends TestCase
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

    public function test_profit_loss_report_renders(): void
    {
        $this->actingAs($this->authenticatedUser())
            ->get('/admin/profit-loss-report')
            ->assertOk()
            ->assertSee('Profit &amp; Loss', false);
    }

    public function test_balance_sheet_report_renders(): void
    {
        $this->actingAs($this->authenticatedUser())
            ->get('/admin/balance-sheet-report')
            ->assertOk()
            ->assertSee('Balance Sheet');
    }

    public function test_inventory_dashboard_renders(): void
    {
        $this->actingAs($this->authenticatedUser())
            ->get('/admin/inventory-dashboard')
            ->assertOk()
            ->assertSee('Inventory Dashboard');
    }

    public function test_purchasing_dashboard_renders(): void
    {
        $this->actingAs($this->authenticatedUser())
            ->get('/admin/purchasing-dashboard')
            ->assertOk()
            ->assertSee('Purchasing Dashboard');
    }

    public function test_sales_dashboard_renders(): void
    {
        $this->actingAs($this->authenticatedUser())
            ->get('/admin/sales-dashboard')
            ->assertOk()
            ->assertSee('Sales Dashboard');
    }
}
