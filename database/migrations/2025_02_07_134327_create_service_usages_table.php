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
            $table->uuid('room_id')->index();
            $table->decimal('price', 10, 2);
            $table->float('quantity');
            $table->double('previous_value');
            $table->double('current_value');
            $table->uuid('lodging_service_id')->index();
            $table->decimal('total_price', 10, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->smallInteger('status')->index();
            $table->timestamp('payment_date')->useCurrent();
            $table->timestamp('last_payment_date')->useCurrent()->useCurrentOnUpdate();
            $table->string('payment_method');
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
