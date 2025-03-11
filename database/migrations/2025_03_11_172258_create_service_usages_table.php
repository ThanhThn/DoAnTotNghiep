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
        Schema::create('service_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lodging_service_id');
            $table->uuid('contract_id');
            $table->uuid('room_service_usage_id');
            $table->decimal('total_cost')->comment("Chi phí tổng");
            $table->boolean('finalized')->default(false);
            $table->boolean('is_finalized_early')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_usages');
    }
};
