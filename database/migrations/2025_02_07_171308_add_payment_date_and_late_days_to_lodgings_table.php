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
        Schema::table('lodgings', function (Blueprint $table) {
            $table->integer('payment_date');
            $table->integer('late_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lodgings', function (Blueprint $table) {
            $table->dropColumn('payment_date');
            $table->dropColumn('late_days');
        });
    }
};
