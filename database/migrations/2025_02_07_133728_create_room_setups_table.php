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
        Schema::create('room_setups', function (Blueprint $table) {
            $table->uuid('room_id');
            $table->uuid('equipment_id');
            $table->integer('quantity');
            $table->smallInteger('status');
            $table->timestamp('installation_date')->nullable();
            $table->timestamp('last_serviced')->nullable();
            $table->timestamps();

            $table->primary(['room_id', 'equipment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_setups');
    }
};
