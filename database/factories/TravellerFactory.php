<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Traveller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Traveller>
 */
class TravellerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            Traveller::AGENCY_ID => Agency::factory(),
            Traveller::FIRST_NAME => fake()->firstName(),
            Traveller::LAST_NAME => fake()->lastName(),
            Traveller::EMAIL => fake()->unique()->safeEmail(),
            Traveller::PHONE_NUMBER => fake()->optional()->phoneNumber(),
        ];
    }
}
