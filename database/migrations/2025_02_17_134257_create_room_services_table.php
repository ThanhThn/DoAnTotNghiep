<?php

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
        Schema::create('room_services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('room_id')->index();
            $table->uuid('lodging_service_id')->index();
            $table->float('last_recorded_value')->nullable();

            $table->unique(['room_id', 'lodging_service_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_services');
    }
};
