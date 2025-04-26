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
        Schema::table('rent_payments', function (Blueprint $table) {
            $table->renameColumn('room_rental_history_id', 'room_rent_invoice_id');
        });

        Schema::table('service_payments', function (Blueprint $table) {
            $table->renameColumn('room_service_usage_id', 'room_service_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rent_payments', function (Blueprint $table) {
            $table->renameColumn('room_rent_invoice_id', 'room_rental_history_id');
        });
        Schema::table('service_payments', function (Blueprint $table) {
            $table->renameColumn('room_service_invoice_id', 'room_service_usage_id');
        });
    }
};
