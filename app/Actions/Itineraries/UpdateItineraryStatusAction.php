<?php

namespace App\Actions\Itineraries;

use App\DTOs\Itineraries\UpdateItineraryStatusData;
use App\Models\Agency;
use App\Models\Itinerary;
use App\Queries\Itineraries\ItineraryQuery;

class UpdateItineraryStatusAction
{
    public function __construct(private readonly ItineraryQuery $query) {}

    public function execute(Agency $agency, int $itineraryId, UpdateItineraryStatusData $data): Itinerary
    {
        $itinerary = $this->query->findForAgency($agency, $itineraryId);

        $itinerary->update([
            Itinerary::STATUS => $data->status,
        ]);

        return $itinerary->refresh()->load('traveller');
    }
}
