<?php

namespace Database\Factories;

use App\Models\EmailAccount;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailAccountFactory extends Factory
{
    public function definition(): array
    {
        $team = Team::factory()->create();
        $user = User::factory()->create();
        $team->users()->attach($user, ['role' => 'admin']);

        return [
            'team_id' => $team->id,
            'user_id' => $user->id,
            'name' => fake()->company() . ' Email',
            'type' => 'imap',
            'email' => fake()->unique()->safeEmail(),
            'credentials' => [
                'type' => 'imap',
                'host' => 'imap.example.com',
                'port' => 993,
                'username' => fake()->userName(),
                'password' => fake()->password(),
                'encryption' => 'ssl'
            ],
            'settings' => [
                'check_interval' => 5,
                'folder' => 'INBOX',
                'archive_folder' => 'Processed',
                'create_tickets' => true
            ],
            'is_active' => true,
            'last_sync_at' => null,
            'sync_error' => null
        ];
    }

    public function forTeam(Team $team): self
    {
        return $this->state(function (array $attributes) use ($team) {
            $user = $team->allUsers()->first() ?? User::factory()->create();
            if (!$team->hasUser($user)) {
                $team->users()->attach($user, ['role' => 'admin']);
            }

            return [
                'team_id' => $team->id,
                'user_id' => $user->id
            ];
        });
    }

    public function msGraph(): self
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'msgraph',
            'credentials' => [
                'type' => 'msgraph',
                'client_id' => fake()->uuid(),
                'client_secret' => fake()->sha256(),
                'tenant_id' => fake()->uuid(),
                'refresh_token' => fake()->uuid(),
                'access_token' => null,
                'expires_at' => null
            ]
        ]);
    }

    public function gmail(): self
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'gmail',
            'credentials' => [
                'type' => 'gmail',
                'client_id' => fake()->uuid(),
                'client_secret' => fake()->sha256(),
                'refresh_token' => fake()->uuid(),
                'access_token' => null,
                'expires_at' => null
            ]
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false
        ]);
    }

    public function withError(): self
    {
        return $this->state(fn(array $attributes) => [
            'sync_error' => 'Failed to connect: Connection timed out',
            'last_sync_at' => now()->subMinutes(30)
        ]);
    }
}
