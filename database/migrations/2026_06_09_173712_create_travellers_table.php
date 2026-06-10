<?php

use App\Models\Agency;
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
        Schema::create(Traveller::TABLE, function (Blueprint $table) {
            $table->id();
            $table->foreignId(Traveller::AGENCY_ID)->constrained(Agency::TABLE)->cascadeOnDelete();
            $table->string(Traveller::FIRST_NAME);
            $table->string(Traveller::LAST_NAME);
            $table->string(Traveller::EMAIL);
            $table->string(Traveller::PHONE_NUMBER)->nullable();
            $table->timestamps();

            $table->unique(['id', Traveller::AGENCY_ID]);
            $table->unique([Traveller::AGENCY_ID, Traveller::EMAIL]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Traveller::TABLE);
    }
};
