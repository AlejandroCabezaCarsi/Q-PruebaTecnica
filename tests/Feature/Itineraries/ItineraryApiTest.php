<?php

namespace Tests\Feature\Itineraries;

use App\Enums\ItineraryStatus;
use App\Models\Agency;
use App\Models\Itinerary;
use App\Models\Traveller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItineraryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_itineraries_for_the_authenticated_agency(): void
    {
        $token = 'agency-token';
        $agency = Agency::factory()->withToken($token)->create();
        $otherAgency = Agency::factory()->create();
        $traveller = Traveller::factory()->create([Traveller::AGENCY_ID => $agency->id]);
        $otherTraveller = Traveller::factory()->create([Traveller::AGENCY_ID => $otherAgency->id]);
        $visibleItinerary = Itinerary::factory()->forTraveller($traveller)->create([
            Itinerary::STATUS => ItineraryStatus::Confirmed,
        ]);
        $hiddenItinerary = Itinerary::factory()->forTraveller($otherTraveller)->create();

        $response = $this
            ->withToken($token)
            ->getJson('/api/v1/itineraries?status=confirmed');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $visibleItinerary->id)
            ->assertJsonMissing(['id' => $hiddenItinerary->id]);
    }

    public function test_it_returns_itinerary_detail_scoped_to_the_authenticated_agency(): void
    {
        $token = 'agency-token';
        $agency = Agency::factory()->withToken($token)->create();
        $traveller = Traveller::factory()->create([Traveller::AGENCY_ID => $agency->id]);
        $itinerary = Itinerary::factory()->forTraveller($traveller)->create();

        $response = $this
            ->withToken($token)
            ->getJson("/api/v1/itineraries/{$itinerary->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $itinerary->id)
            ->assertJsonPath('data.traveller.id', $traveller->id);
    }

    public function test_it_returns_not_found_for_another_agencys_itinerary(): void
    {
        $token = 'agency-token';
        Agency::factory()->withToken($token)->create();
        $otherTraveller = Traveller::factory()->create();
        $itinerary = Itinerary::factory()->forTraveller($otherTraveller)->create();

        $this
            ->withToken($token)
            ->getJson("/api/v1/itineraries/{$itinerary->id}")
            ->assertNotFound();
    }

    public function test_it_updates_an_itinerary_status(): void
    {
        $token = 'agency-token';
        $agency = Agency::factory()->withToken($token)->create();
        $traveller = Traveller::factory()->create([Traveller::AGENCY_ID => $agency->id]);
        $itinerary = Itinerary::factory()->forTraveller($traveller)->create([
            Itinerary::STATUS => ItineraryStatus::Draft,
        ]);

        $response = $this
            ->withToken($token)
            ->patchJson("/api/v1/itineraries/{$itinerary->id}/status", [
                'status' => ItineraryStatus::Confirmed->value,
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', ItineraryStatus::Confirmed->value);

        $this->assertDatabaseHas(Itinerary::TABLE, [
            'id' => $itinerary->id,
            Itinerary::STATUS => ItineraryStatus::Confirmed->value,
        ]);
    }

    public function test_it_validates_status_updates(): void
    {
        $token = 'agency-token';
        $agency = Agency::factory()->withToken($token)->create();
        $traveller = Traveller::factory()->create([Traveller::AGENCY_ID => $agency->id]);
        $itinerary = Itinerary::factory()->forTraveller($traveller)->create();

        $this
            ->withToken($token)
            ->patchJson("/api/v1/itineraries/{$itinerary->id}/status", [
                'status' => 'unknown',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_it_requires_an_agency_token(): void
    {
        $this
            ->getJson('/api/v1/itineraries')
            ->assertUnauthorized();
    }
}
