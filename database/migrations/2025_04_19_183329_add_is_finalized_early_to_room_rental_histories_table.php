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
        Schema::table('room_rent_invoices', function (Blueprint $table) {
            $table->boolean('is_finalized_early')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_rent_invoices', function (Blueprint $table) {
            $table->dropColumn('is_finalized_early');
        });
    }
};
