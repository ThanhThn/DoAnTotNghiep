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
        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('room_code');
            $table->uuid('lodging_id');
            $table->integer('max_tenants');
            $table->decimal('price', 10);
            $table->smallInteger('status');
            $table->boolean('is_enabled')->default(true);
            $table->integer('size')->comment('Kích thước phòng')->nullable();
            $table->json('services_custom')->nullable();
            $table->json('priority')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
