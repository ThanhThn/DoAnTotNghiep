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
        Schema::create('lodging_services', function (Blueprint $table) {
            $table->uuid('service_id')->primary();
            $table->uuid('lodging_id')->primary();
            $table->string('unit');
            $table->decimal('price_per_unit', 10);
            $table->boolean('is_fixed');
            $table->boolean('is_enabled')->default(true);
            $table->integer('payment_date')->nullable();
            $table->integer('late_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lodging_services');
    }
};
