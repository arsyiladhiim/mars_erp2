<?php

namespace Tests\Feature\Productivity;

use App\Models\Helpdesk\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TicketSlaTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(): User
    {
        return User::create(['name' => 'Requester', 'email' => 'req@test.local', 'password' => 'x']);
    }

    public function test_due_at_is_computed_from_sla_hours_on_create(): void
    {
        Carbon::setTestNow('2026-01-10 09:00:00');

        $ticket = Ticket::create(['number' => 'TKT-1', 'subject' => 'Printer jam', 'requester_id' => $this->makeUser()->id, 'sla_hours' => 8]);

        $this->assertEquals(Carbon::parse('2026-01-10 17:00:00'), $ticket->due_at);

        Carbon::setTestNow();
    }

    public function test_an_explicitly_provided_due_at_is_not_overridden(): void
    {
        $explicit = Carbon::parse('2026-03-01 12:00:00');

        $ticket = Ticket::create([
            'number' => 'TKT-2', 'subject' => 'VPN issue', 'requester_id' => $this->makeUser()->id,
            'sla_hours' => 4, 'due_at' => $explicit,
        ]);

        $this->assertTrue($ticket->due_at->equalTo($explicit));
    }

    public function test_changing_sla_hours_recomputes_due_at_from_the_original_created_at(): void
    {
        Carbon::setTestNow('2026-01-10 09:00:00');
        $ticket = Ticket::create(['number' => 'TKT-3', 'subject' => 'Slow computer', 'requester_id' => $this->makeUser()->id, 'sla_hours' => 8]);
        Carbon::setTestNow('2026-01-11 09:00:00'); // a day later, simulating a follow-up edit

        $ticket->update(['sla_hours' => 24]);

        $this->assertEquals(Carbon::parse('2026-01-11 09:00:00'), $ticket->fresh()->due_at);

        Carbon::setTestNow();
    }

    public function test_is_overdue_reflects_a_past_due_date_on_an_open_ticket(): void
    {
        $ticket = Ticket::create([
            'number' => 'TKT-4', 'subject' => 'Server down', 'requester_id' => $this->makeUser()->id,
            'due_at' => now()->subHour(), 'status' => 'open',
        ]);

        $this->assertTrue($ticket->isOverdue());
    }

    public function test_a_resolved_ticket_is_never_considered_overdue(): void
    {
        $ticket = Ticket::create([
            'number' => 'TKT-5', 'subject' => 'Server down', 'requester_id' => $this->makeUser()->id,
            'due_at' => now()->subHour(), 'status' => 'resolved',
        ]);

        $this->assertFalse($ticket->isOverdue());
    }
}
