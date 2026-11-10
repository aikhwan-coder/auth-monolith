<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Membership\Models\Member;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'member_number' => 'MBR-'.fake()->unique()->numerify('#####'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('08##########'),
            'status' => Member::STATUS_ACTIVE,
            'joined_at' => now()->subMonths(rand(1, 24))->toDateString(),
            'expires_at' => now()->addYear()->toDateString(),
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => Member::STATUS_SUSPENDED]);
    }
}
