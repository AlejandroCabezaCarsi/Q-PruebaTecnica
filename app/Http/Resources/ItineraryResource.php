<?php

namespace App\Http\Resources;

use App\Models\Itinerary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Itinerary
 */
class ItineraryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            Itinerary::TITLE => $this->{Itinerary::TITLE},
            Itinerary::DESTINATION => $this->{Itinerary::DESTINATION},
            Itinerary::STARTS_AT => $this->{Itinerary::STARTS_AT}?->toDateString(),
            Itinerary::ENDS_AT => $this->{Itinerary::ENDS_AT}?->toDateString(),
            Itinerary::STATUS => $this->{Itinerary::STATUS}->value,
            'traveller' => new TravellerResource($this->whenLoaded('traveller')),
        ];
    }
}
