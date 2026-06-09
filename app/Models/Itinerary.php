<?php

namespace App\Models;

use App\Enums\ItineraryStatus;
use Database\Factories\ItineraryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Itinerary extends Model
{
    /** @use HasFactory<ItineraryFactory> */
    use HasFactory;

    public const TABLE = 'itineraries';

    public const AGENCY_ID = 'agency_id';

    public const TRAVELLER_ID = 'traveller_id';

    public const TITLE = 'title';

    public const DESTINATION = 'destination';

    public const STARTS_AT = 'starts_at';

    public const ENDS_AT = 'ends_at';

    public const STATUS = 'status';

    public const METADATA = 'metadata';

    public const FILLABLE = [
        self::AGENCY_ID,
        self::TRAVELLER_ID,
        self::TITLE,
        self::DESTINATION,
        self::STARTS_AT,
        self::ENDS_AT,
        self::STATUS,
        self::METADATA,
    ];

    protected $table = self::TABLE;

    protected $fillable = self::FILLABLE;

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function traveller(): BelongsTo
    {
        return $this->belongsTo(Traveller::class);
    }

    protected function casts(): array
    {
        return [
            self::STARTS_AT => 'date',
            self::ENDS_AT => 'date',
            self::STATUS => ItineraryStatus::class,
            self::METADATA => 'array',
        ];
    }
}
