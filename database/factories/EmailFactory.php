<?php

namespace Database\Factories;

use App\Models\EmailAccount;
use App\Models\Team;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailFactory extends Factory
{
    public function definition(): array
    {
        $emailAccount = EmailAccount::factory()->create();

        return [
            'email_account_id' => $emailAccount->id,
            'ticket_id' => null,
            'message_id' => fake()->unique()->uuid() . '@example.com',
            'remote_id' => fake()->unique()->uuid(),
            'subject' => fake()->sentence(),
            'body_html' => fake()->randomHtml(),
            'body_text' => fake()->paragraph(),
            'from_email' => fake()->email(),
            'from_name' => fake()->name(),
            'to_email' => fake()->email(),
            'to_name' => fake()->name(),
            'cc' => fake()->boolean(30) ? [fake()->email(), fake()->email()] : null,
            'bcc' => fake()->boolean(20) ? [fake()->email()] : null,
            'headers' => [
                'Message-ID' => fake()->uuid(),
                'Date' => now()->toRfc2822String(),
                'In-Reply-To' => fake()->boolean(20) ? fake()->uuid() : null,
            ],
            'received_at' => now(),
            'imported_at' => now(),
        ];
    }

    public function forTeam(Team $team): self
    {
        return $this->state(function (array $attributes) use ($team) {
            $emailAccount = EmailAccount::factory()->forTeam($team)->create();

            return [
                'email_account_id' => $emailAccount->id
            ];
        });
    }

    public function withTicket(?Ticket $ticket = null): self
    {
        return $this->state(function (array $attributes) use ($ticket) {
            $ticket = $ticket ?? Ticket::factory()->create([
                'team_id' => EmailAccount::find($attributes['email_account_id'])->team_id
            ]);

            return [
                'ticket_id' => $ticket->id
            ];
        });
    }
}
