<?php

namespace Database\Factories;

use App\Enums\ItineraryStatus;
use App\Models\Agency;
use App\Models\Itinerary;
use App\Models\Traveller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Itinerary>
 */
class ItineraryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('+1 week', '+6 months');
        $endsAt = (clone $startsAt)->modify('+'.fake()->numberBetween(3, 14).' days');

        return [
            Itinerary::AGENCY_ID => Agency::factory(),
            Itinerary::TRAVELLER_ID => fn (array $attributes) => Traveller::factory()->create([
                Traveller::AGENCY_ID => $attributes[Itinerary::AGENCY_ID],
            ])->id,
            Itinerary::TITLE => fake()->sentence(3),
            Itinerary::DESTINATION => fake()->city(),
            Itinerary::STARTS_AT => $startsAt,
            Itinerary::ENDS_AT => $endsAt,
            Itinerary::STATUS => fake()->randomElement(ItineraryStatus::cases())->value,
            Itinerary::METADATA => [
                'booking_reference' => strtoupper(fake()->bothify('???-#####')),
            ],
        ];
    }

    public function forTraveller(Traveller $traveller): static
    {
        return $this->state(fn () => [
            Itinerary::AGENCY_ID => $traveller->{Traveller::AGENCY_ID},
            Itinerary::TRAVELLER_ID => $traveller->id,
        ]);
    }

    public function withStatus(ItineraryStatus $status): static
    {
        return $this->state(fn () => [
            Itinerary::STATUS => $status->value,
        ]);
    }
}
