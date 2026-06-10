<?php

namespace Database\Factories;

use App\Models\Agency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Agency>
 */
class AgencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            Agency::NAME => fake()->company(),
            Agency::API_TOKEN_HASH => hash('sha256', Str::random(40)),
        ];
    }

    public function withToken(string $token): static
    {
        return $this->state(fn () => [
            Agency::API_TOKEN_HASH => hash('sha256', $token),
        ]);
    }
}
