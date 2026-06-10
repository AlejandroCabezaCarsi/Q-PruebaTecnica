<?php

namespace Tests\Feature\Itineraries;

use App\Enums\ItineraryStatus;
use App\Models\Agency;
use App\Models\Itinerary;
use App\Models\Traveller;
use Illuminate\Database\QueryException;
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

    public function test_database_rejects_itineraries_with_a_traveller_from_another_agency(): void
    {
        $agency = Agency::factory()->create();
        $otherTraveller = Traveller::factory()->create();

        $this->expectException(QueryException::class);

        Itinerary::factory()->create([
            Itinerary::AGENCY_ID => $agency->id,
            Itinerary::TRAVELLER_ID => $otherTraveller->id,
        ]);
    }

    public function test_it_filters_itineraries_by_traveller(): void
    {
        $token = 'agency-token';
        $agency = Agency::factory()->withToken($token)->create();
        $traveller = Traveller::factory()->create([Traveller::AGENCY_ID => $agency->id]);
        $otherTraveller = Traveller::factory()->create([Traveller::AGENCY_ID => $agency->id]);
        $visibleItinerary = Itinerary::factory()->forTraveller($traveller)->create();
        $hiddenItinerary = Itinerary::factory()->forTraveller($otherTraveller)->create();

        $response = $this
            ->withToken($token)
            ->getJson("/api/v1/itineraries?traveller_id={$traveller->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $visibleItinerary->id)
            ->assertJsonMissing(['id' => $hiddenItinerary->id]);
    }

    public function test_it_rejects_traveller_filters_from_another_agency(): void
    {
        $token = 'agency-token';
        Agency::factory()->withToken($token)->create();
        $otherTraveller = Traveller::factory()->create();

        $this
            ->withToken($token)
            ->getJson("/api/v1/itineraries?traveller_id={$otherTraveller->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('traveller_id');
    }

    public function test_it_filters_itineraries_by_start_date_range(): void
    {
        $token = 'agency-token';
        $agency = Agency::factory()->withToken($token)->create();
        $traveller = Traveller::factory()->create([Traveller::AGENCY_ID => $agency->id]);
        $insideRange = Itinerary::factory()->forTraveller($traveller)->create([
            Itinerary::STARTS_AT => '2026-07-10',
            Itinerary::ENDS_AT => '2026-07-14',
        ]);
        $outsideRange = Itinerary::factory()->forTraveller($traveller)->create([
            Itinerary::STARTS_AT => '2026-09-10',
            Itinerary::ENDS_AT => '2026-09-14',
        ]);

        $response = $this
            ->withToken($token)
            ->getJson('/api/v1/itineraries?from=2026-07-01&to=2026-07-31');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', $insideRange->id)
            ->assertJsonMissing(['id' => $outsideRange->id]);
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

    public function test_it_returns_not_found_when_updating_another_agencys_itinerary(): void
    {
        $token = 'agency-token';
        Agency::factory()->withToken($token)->create();
        $otherTraveller = Traveller::factory()->create();
        $itinerary = Itinerary::factory()->forTraveller($otherTraveller)->create([
            Itinerary::STATUS => ItineraryStatus::Draft,
        ]);

        $this
            ->withToken($token)
            ->patchJson("/api/v1/itineraries/{$itinerary->id}/status", [
                'status' => ItineraryStatus::Confirmed->value,
            ])
            ->assertNotFound();

        $this->assertDatabaseHas(Itinerary::TABLE, [
            'id' => $itinerary->id,
            Itinerary::STATUS => ItineraryStatus::Draft->value,
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
