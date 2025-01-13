<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'team_id' => function () {
                $team = Team::factory()->create();
                $user = User::factory()->create();
                $team->users()->attach($user, ['role' => 'admin']);
                return $team->id;
            },
            'name' => fake()->unique()->company(),
            'slug' => function (array $attributes) {
                return Str::slug($attributes['name']);
            },
            'description' => fake()->paragraph(),
            'manager_id' => function (array $attributes) {
                return Team::find($attributes['team_id'])->owner->id;
            },
            'is_active' => true,
            'settings' => [
                'auto_assign' => fake()->boolean(),
                'notification_enabled' => true,
            ],
        ];
    }

    public function forTeam(Team $team): self
    {
        return $this->state(function (array $attributes) use ($team) {
            $manager = $team->allUsers()->first() ?? User::factory()->create();
            if (!$team->hasUser($manager)) {
                $team->users()->attach($manager, ['role' => 'admin']);
            }

            return [
                'team_id' => $team->id,
                'manager_id' => $manager->id
            ];
        });
    }

    public function inactive(): self
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false
        ]);
    }

    public function withManager(User $manager): self
    {
        return $this->state(fn(array $attributes) => [
            'manager_id' => $manager->id
        ]);
    }
}
