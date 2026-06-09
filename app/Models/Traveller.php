<?php

namespace App\Models;

use Database\Factories\TravellerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Traveller extends Model
{
    /** @use HasFactory<TravellerFactory> */
    use HasFactory;

    public const TABLE = 'travellers';

    public const AGENCY_ID = 'agency_id';

    public const FIRST_NAME = 'first_name';

    public const LAST_NAME = 'last_name';

    public const EMAIL = 'email';

    public const PHONE_NUMBER = 'phone_number';

    public const FILLABLE = [
        self::AGENCY_ID,
        self::FIRST_NAME,
        self::LAST_NAME,
        self::EMAIL,
        self::PHONE_NUMBER,
    ];

    protected $table = self::TABLE;

    protected $fillable = self::FILLABLE;

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(Itinerary::class);
    }
}
