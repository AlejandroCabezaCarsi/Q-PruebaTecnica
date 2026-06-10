<?php

use App\Enums\ItineraryStatus;
use App\Models\Agency;
use App\Models\Itinerary;
use App\Models\Traveller;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Itinerary::TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId(Itinerary::AGENCY_ID)->constrained(Agency::TABLE)->cascadeOnDelete();
            $table->unsignedBigInteger(Itinerary::TRAVELLER_ID);
            $table->string(Itinerary::TITLE);
            $table->string(Itinerary::DESTINATION);
            $table->date(Itinerary::STARTS_AT)->nullable();
            $table->date(Itinerary::ENDS_AT)->nullable();
            $table->string(Itinerary::STATUS)->default(ItineraryStatus::Draft->value);
            $table->timestamps();

            $table->index([Itinerary::AGENCY_ID, Itinerary::STATUS]);
            $table->index([Itinerary::AGENCY_ID, Itinerary::STARTS_AT]);
            $table->index([Itinerary::TRAVELLER_ID, Itinerary::AGENCY_ID]);
            $table->index([Itinerary::TRAVELLER_ID, Itinerary::STARTS_AT]);
            $table
                ->foreign([Itinerary::TRAVELLER_ID, Itinerary::AGENCY_ID])
                ->references(['id', Traveller::AGENCY_ID])
                ->on(Traveller::TABLE)
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Itinerary::TABLE);
    }
};
