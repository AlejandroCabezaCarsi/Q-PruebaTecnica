<?php

namespace Database\Seeders;

use App\Enums\ItineraryStatus;
use App\Models\Agency;
use App\Models\Itinerary;
use App\Models\Traveller;
use Illuminate\Database\Seeder;

class DemoItinerarySeeder extends Seeder
{
    public const DEMO_TOKEN = 'qynvo-demo-token';

    public function run(): void
    {
        $agency = Agency::query()->updateOrCreate(
            [Agency::API_TOKEN_HASH => hash('sha256', self::DEMO_TOKEN)],
            [Agency::NAME => 'Qynvo Travel'],
        );

        $traveller = Traveller::query()->updateOrCreate(
            [
                Traveller::AGENCY_ID => $agency->id,
                Traveller::EMAIL => 'alejandro@example.com',
            ],
            [
                Traveller::FIRST_NAME => 'Alejandro',
                Traveller::LAST_NAME => 'Demo',
                Traveller::PHONE_NUMBER => '+34 600 000 000',
            ],
        );

        $this->upsertItinerary(
            agency: $agency,
            traveller: $traveller,
            title: 'Madrid business trip',
            destination: 'Madrid',
            startsAt: '2026-07-10',
            endsAt: '2026-07-14',
            status: ItineraryStatus::Draft,
        );

        $this->upsertItinerary(
            agency: $agency,
            traveller: $traveller,
            title: 'Lisbon client visit',
            destination: 'Lisbon',
            startsAt: '2026-08-03',
            endsAt: '2026-08-07',
            status: ItineraryStatus::Confirmed,
        );

        $otherAgency = Agency::query()->updateOrCreate(
            [Agency::API_TOKEN_HASH => hash('sha256', 'other-agency-token')],
            [Agency::NAME => 'Other Agency'],
        );

        $otherTraveller = Traveller::query()->updateOrCreate(
            [
                Traveller::AGENCY_ID => $otherAgency->id,
                Traveller::EMAIL => 'hidden@example.com',
            ],
            [
                Traveller::FIRST_NAME => 'Hidden',
                Traveller::LAST_NAME => 'Traveller',
                Traveller::PHONE_NUMBER => null,
            ],
        );

        $this->upsertItinerary(
            agency: $otherAgency,
            traveller: $otherTraveller,
            title: 'Hidden tenant trip',
            destination: 'Paris',
            startsAt: '2026-09-01',
            endsAt: '2026-09-05',
            status: ItineraryStatus::Confirmed,
        );
    }

    private function upsertItinerary(
        Agency $agency,
        Traveller $traveller,
        string $title,
        string $destination,
        string $startsAt,
        string $endsAt,
        ItineraryStatus $status,
    ): void {
        Itinerary::query()->updateOrCreate(
            [
                Itinerary::AGENCY_ID => $agency->id,
                Itinerary::TRAVELLER_ID => $traveller->id,
                Itinerary::TITLE => $title,
            ],
            [
                Itinerary::DESTINATION => $destination,
                Itinerary::STARTS_AT => $startsAt,
                Itinerary::ENDS_AT => $endsAt,
                Itinerary::STATUS => $status,
            ],
        );
    }
}
