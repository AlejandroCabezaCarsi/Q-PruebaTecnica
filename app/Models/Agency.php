<?php

namespace App\Models;

use Database\Factories\AgencyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    /** @use HasFactory<AgencyFactory> */
    use HasFactory;

    public const TABLE = 'agencies';

    public const NAME = 'name';

    public const API_TOKEN_HASH = 'api_token_hash';

    public const FILLABLE = [
        self::NAME,
        self::API_TOKEN_HASH,
    ];

    public const HIDDEN = [
        self::API_TOKEN_HASH,
    ];

    protected $table = self::TABLE;

    protected $fillable = self::FILLABLE;

    protected $hidden = self::HIDDEN;

    public function travellers(): HasMany
    {
        return $this->hasMany(Traveller::class);
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(Itinerary::class);
    }
}
