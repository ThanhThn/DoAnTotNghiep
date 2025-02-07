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
        Schema::create('maintain_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id');
            $table->decimal('price', 10);
            $table->integer('quantity');
            $table->decimal('total_price', 10);
            $table->date('maintain_date');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintain_histories');
    }
};
