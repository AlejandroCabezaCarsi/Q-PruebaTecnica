<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Itineraries\GetItineraryAction;
use App\Actions\Itineraries\ListItinerariesAction;
use App\Actions\Itineraries\UpdateItineraryStatusAction;
use App\DTOs\Itineraries\ListItinerariesData;
use App\DTOs\Itineraries\UpdateItineraryStatusData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Itineraries\ListItinerariesRequest;
use App\Http\Requests\Itineraries\UpdateItineraryStatusRequest;
use App\Http\Resources\ItineraryDetailResource;
use App\Http\Resources\ItineraryResource;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ItineraryController extends Controller
{
    public function index(
        ListItinerariesRequest $request,
        ListItinerariesAction $action,
    ): AnonymousResourceCollection {
        $itineraries = $action->execute(
            agency: $this->agency($request),
            data: ListItinerariesData::fromRequest($request),
        );

        return ItineraryResource::collection($itineraries);
    }

    public function show(Request $request, int $itinerary, GetItineraryAction $action): ItineraryDetailResource
    {
        return new ItineraryDetailResource(
            $action->execute($this->agency($request), $itinerary),
        );
    }

    public function updateStatus(
        UpdateItineraryStatusRequest $request,
        int $itinerary,
        UpdateItineraryStatusAction $action,
    ): ItineraryDetailResource {
        return new ItineraryDetailResource(
            $action->execute(
                agency: $this->agency($request),
                itineraryId: $itinerary,
                data: UpdateItineraryStatusData::fromRequest($request),
            ),
        );
    }

    private function agency(Request $request): Agency
    {
        /** @var Agency $agency */
        $agency = $request->attributes->get('agency');

        return $agency;
    }
}
