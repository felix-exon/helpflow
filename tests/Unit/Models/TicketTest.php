<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Email;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_can_be_created_with_required_attributes()
    {
        $creator = User::factory()->create();

        $ticket = Ticket::factory()->create([
            'creator_id' => $creator->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category' => 'general',
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'status' => 'open', // default value
            'priority' => 'medium', // default value
        ]);
    }

    public function test_ticket_generates_unique_reference_number()
    {
        $ticket1 = Ticket::factory()->create();
        $ticket2 = Ticket::factory()->create();

        $this->assertNotNull($ticket1->reference_number);
        $this->assertNotNull($ticket2->reference_number);
        $this->assertNotEquals($ticket1->reference_number, $ticket2->reference_number);
        $this->assertStringStartsWith('TIC-' . date('Y'), $ticket1->reference_number);
    }

    public function test_ticket_can_be_assigned_to_user()
    {
        $assignee = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $ticket->assigned_to_id = $assignee->id;
        $ticket->save();

        $this->assertEquals($assignee->id, $ticket->assignedTo->id);
    }

    public function test_ticket_can_be_assigned_to_department()
    {
        $department = Department::factory()->create();
        $ticket = Ticket::factory()->create();

        $ticket->department_id = $department->id;
        $ticket->save();

        $this->assertEquals($department->id, $ticket->department->id);
    }

    public function test_ticket_can_track_resolution_time()
    {
        $ticket = Ticket::factory()->create([
            'created_at' => now()->subHours(2)
        ]);

        $ticket->close();

        $this->assertNotNull($ticket->closed_at);
        $this->assertEquals('closed', $ticket->status);
        $this->assertEquals(120, $ticket->resolution_time); // 2 hours = 120 minutes
    }

    public function test_ticket_can_be_created_from_email()
    {
        $email = Email::factory()->create([
            'subject' => 'Help needed',
            'body_text' => 'I need assistance'
        ]);

        $ticket = Ticket::factory()->create([
            'email_source_id' => $email->id,
            'title' => $email->subject,
            'description' => $email->body_text
        ]);

        $this->assertEquals($email->id, $ticket->email_source_id);
        $this->assertEquals($email->subject, $ticket->title);
    }

    public function test_ticket_updates_last_activity_on_save()
    {
        $ticket = Ticket::factory()->create();
        $originalActivity = $ticket->last_activity_at;

        sleep(1); // Warte kurz um unterschiedliche Zeitstempel zu garantieren
        $ticket->title = 'Updated Title';
        $ticket->save();

        $this->assertTrue($ticket->last_activity_at->gt($originalActivity));
    }
}
