<?php

namespace App\Queries\Itineraries;

use App\DTOs\Itineraries\ListItinerariesData;
use App\Models\Agency;
use App\Models\Itinerary;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ItineraryQuery
{
    public function paginateForAgency(Agency $agency, ListItinerariesData $data): LengthAwarePaginator
    {
        return $this->forAgency($agency)
            ->with('traveller')
            ->when($data->status, fn (Builder $query) => $query->where(Itinerary::STATUS, $data->status->value))
            ->when($data->travellerId, fn (Builder $query) => $query->where(Itinerary::TRAVELLER_ID, $data->travellerId))
            ->when($data->from, fn (Builder $query) => $query->whereDate(Itinerary::STARTS_AT, '>=', $data->from))
            ->when($data->to, fn (Builder $query) => $query->whereDate(Itinerary::STARTS_AT, '<=', $data->to))
            ->orderBy(Itinerary::STARTS_AT)
            ->orderBy('id')
            ->paginate($data->perPage)
            ->withQueryString();
    }

    public function findForAgency(Agency $agency, int $itineraryId): Itinerary
    {
        return $this->forAgency($agency)
            ->with('traveller')
            ->findOrFail($itineraryId);
    }

    private function forAgency(Agency $agency): Builder
    {
        return Itinerary::query()
            ->where(Itinerary::AGENCY_ID, $agency->id);
    }
}
