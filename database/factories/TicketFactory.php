<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Jetstream\Jetstream;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        $team = Team::factory()->create();
        $creator = User::factory()->create();
        $team->users()->attach($creator, ['role' => 'admin']);

        return [
            'team_id' => $team->id,
            'creator_id' => $creator->id,
            'assigned_to_id' => null,
            'department_id' => null,
            'email_source_id' => null,
            'reference_number' => function (array $attributes) {
                return 'TIC-' . date('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT);
            },
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => 'open',
            'priority' => 'medium',
            'category' => fake()->randomElement(['general', 'technical', 'billing', 'support']),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'last_activity_at' => now(),
            'closed_at' => null,
            'resolution_time' => null,
        ];
    }

    public function forTeam(Team $team): self
    {
        return $this->state(function (array $attributes) use ($team) {
            $creator = $team->allUsers()->first() ?? User::factory()->create();
            if (!$team->hasUser($creator)) {
                $team->users()->attach($creator, ['role' => 'admin']);
            }

            return [
                'team_id' => $team->id,
                'creator_id' => $creator->id
            ];
        });
    }

    public function assigned(): self
    {
        return $this->state(function (array $attributes) {
            $team = Team::find($attributes['team_id']);
            $assignee = $team->allUsers()->inRandomOrder()->first() ?? User::factory()->create();
            if (!$team->hasUser($assignee)) {
                $team->users()->attach($assignee, ['role' => 'editor']);
            }

            return [
                'assigned_to_id' => $assignee->id,
                'department_id' => Department::factory()->create(['team_id' => $team->id])->id,
                'status' => 'in_progress'
            ];
        });
    }

    public function urgent(): self
    {
        return $this->state(fn(array $attributes) => [
            'priority' => 'urgent',
            'due_date' => now()->addDays(1),
        ]);
    }

    public function closed(): self
    {
        return $this->state(function (array $attributes) {
            $created = fake()->dateTimeBetween('-30 days', '-1 hour');
            $closed = fake()->dateTimeBetween($created, 'now');
            $resolutionTime = $closed->getTimestamp() - $created->getTimestamp();

            return [
                'status' => 'closed',
                'created_at' => $created,
                'closed_at' => $closed,
                'resolution_time' => ceil($resolutionTime / 60), // Konvertiere zu Minuten
            ];
        });
    }

    public function inProgress(): self
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'in_progress'
        ]);
    }

    public function resolved(): self
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'resolved'
        ]);
    }
}
