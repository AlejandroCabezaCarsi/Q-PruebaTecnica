<?php

namespace App\Actions\Itineraries;

use App\Models\Agency;
use App\Models\Itinerary;
use App\Queries\Itineraries\ItineraryQuery;

class GetItineraryAction
{
    public function __construct(private readonly ItineraryQuery $query) {}

    public function execute(Agency $agency, int $itineraryId): Itinerary
    {
        return $this->query->findForAgency($agency, $itineraryId);
    }
}
