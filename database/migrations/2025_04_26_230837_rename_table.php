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
        Schema::rename('room_rental_histories', 'room_rent_invoices');
        Schema::rename('room_service_usages', 'room_service_invoices');
        Schema::rename('rent_payments', 'rent_payments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('room_rent_invoices','room_rental_histories');
        Schema::rename('rent_payments','rental_histories');
        Schema::rename('room_service_invoices','room_service_usages');

    }
};
