<?php

namespace App\Http\Resources;

use App\Models\Traveller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Traveller
 */
class TravellerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            Traveller::FIRST_NAME => $this->{Traveller::FIRST_NAME},
            Traveller::LAST_NAME => $this->{Traveller::LAST_NAME},
            Traveller::EMAIL => $this->{Traveller::EMAIL},
            Traveller::PHONE_NUMBER => $this->{Traveller::PHONE_NUMBER},
        ];
    }
}
