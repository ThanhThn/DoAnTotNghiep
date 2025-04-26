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
        Schema::table('room_service_invoices', function (Blueprint $table) {
            $table->integer('month_billing');
            $table->integer('year_billing');
            $table->boolean('is_finalized_early')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_service_invoices', function (Blueprint $table) {
            $table->dropColumn('month_billing');
            $table->dropColumn('year_billing');
            $table->dropColumn('is_finalized_early');
        });
    }
};
