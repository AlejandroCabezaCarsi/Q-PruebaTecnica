<?php

namespace App\Actions\Itineraries;

use App\DTOs\Itineraries\ListItinerariesData;
use App\Models\Agency;
use App\Queries\Itineraries\ItineraryQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListItinerariesAction
{
    public function __construct(private readonly ItineraryQuery $query) {}

    public function execute(Agency $agency, ListItinerariesData $data): LengthAwarePaginator
    {
        return $this->query->paginateForAgency($agency, $data);
    }
}
