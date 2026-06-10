<?php

use App\Http\Controllers\Api\V1\ItineraryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('agency')
    ->group(function (): void {
        Route::get('itineraries', [ItineraryController::class, 'index']);
        Route::get('itineraries/{itinerary}', [ItineraryController::class, 'show'])
            ->whereNumber('itinerary');
        Route::patch('itineraries/{itinerary}/status', [ItineraryController::class, 'updateStatus'])
            ->whereNumber('itinerary');
    });
