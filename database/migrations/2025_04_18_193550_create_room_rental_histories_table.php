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
        Schema::create('room_rental_histories', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->uuid("room_id")->index();
            $table->decimal('total_price', 10, 2)->index();
            $table->decimal('amount_paid', 10, 2)->index();
            $table->integer('month_billing')->index();
            $table->integer('year_billing')->index();
            $table->boolean('finalized')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_rental_histories');
    }
};
