<?php

namespace App\Http\Requests\Itineraries;

use App\Enums\ItineraryStatus;
use App\Models\Agency;
use App\Models\Traveller;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListItinerariesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        /** @var Agency $agency */
        $agency = $this->attributes->get('agency');

        return [
            'status' => ['sometimes', 'string', Rule::enum(ItineraryStatus::class)],
            'traveller_id' => [
                'sometimes',
                'integer',
                Rule::exists(Traveller::TABLE, 'id')
                    ->where(Traveller::AGENCY_ID, $agency->id),
            ],
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
