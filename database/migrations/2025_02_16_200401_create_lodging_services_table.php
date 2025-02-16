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
            $table->uuid('id')->primary();
            $table->string('service_name')->nullable();
            $table->integer('service_id')->nullable();
            $table->uuid('lodging_id');
            $table->integer('unit_id');
            $table->decimal('price_per_unit', 10);
            $table->boolean('is_enabled')->default(true);
            $table->integer('payment_date')->nullable();
            $table->integer('late_days')->nullable();

            $table->unique(['lodging_id', 'service_id'], 'unique_lodging_service');
            $table->unique(['lodging_id', 'service_name'], 'unique_lodging_service_name');
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
