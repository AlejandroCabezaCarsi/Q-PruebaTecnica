<?php

namespace App\DTOs\Itineraries;

use App\Enums\ItineraryStatus;
use App\Http\Requests\Itineraries\ListItinerariesRequest;

final readonly class ListItinerariesData
{
    public function __construct(
        public ?ItineraryStatus $status,
        public ?int $travellerId,
        public ?string $from,
        public ?string $to,
        public int $perPage,
    ) {}

    public static function fromRequest(ListItinerariesRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            status: isset($validated['status']) ? ItineraryStatus::from($validated['status']) : null,
            travellerId: isset($validated['traveller_id']) ? (int) $validated['traveller_id'] : null,
            from: $validated['from'] ?? null,
            to: $validated['to'] ?? null,
            perPage: isset($validated['per_page']) ? (int) $validated['per_page'] : 15,
        );
    }
}
