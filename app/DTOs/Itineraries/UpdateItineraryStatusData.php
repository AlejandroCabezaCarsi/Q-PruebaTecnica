<?php

namespace App\DTOs\Itineraries;

use App\Enums\ItineraryStatus;
use App\Http\Requests\Itineraries\UpdateItineraryStatusRequest;

final readonly class UpdateItineraryStatusData
{
    public function __construct(public ItineraryStatus $status) {}

    public static function fromRequest(UpdateItineraryStatusRequest $request): self
    {
        return new self(
            status: ItineraryStatus::from($request->validated('status')),
        );
    }
}
