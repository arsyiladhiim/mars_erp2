<?php

namespace Tests\Feature\Foundation;

use App\Models\Audit\AuditLog;
use App\Models\Core\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_company_records_an_audit_log_entry(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);

        $log = AuditLog::where('entity_type', Company::class)
            ->where('entity_id', $company->id)
            ->where('action', 'created')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('Test Co', $log->after['name'] ?? null);
    }

    public function test_updating_a_company_records_before_and_after_state(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $company->update(['name' => 'Renamed Co']);

        $log = AuditLog::where('entity_type', Company::class)
            ->where('entity_id', $company->id)
            ->where('action', 'updated')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('Test Co', $log->before['name'] ?? null);
        $this->assertSame('Renamed Co', $log->after['name'] ?? null);
    }

    public function test_soft_deleting_records_a_distinct_action(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co', 'is_active' => true]);

        $company->delete();

        $log = AuditLog::where('entity_type', Company::class)
            ->where('entity_id', $company->id)
            ->where('action', 'soft_deleted')
            ->first();

        $this->assertNotNull($log);
    }

    public function test_the_acting_user_is_captured_when_authenticated(): void
    {
        $company = Company::create(['code' => 'C1', 'name' => 'Test Co']);
        $user = User::create(['name' => 'Tester', 'email' => 'tester@test.local', 'password' => 'x', 'company_id' => $company->id]);

        $this->actingAs($user);
        $another = Company::create(['code' => 'C2', 'name' => 'Another Co']);

        $log = AuditLog::where('entity_type', Company::class)->where('entity_id', $another->id)->first();

        $this->assertSame($user->id, $log->user_id);
    }
}
