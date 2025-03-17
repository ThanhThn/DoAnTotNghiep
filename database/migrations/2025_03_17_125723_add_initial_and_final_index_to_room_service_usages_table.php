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
        Schema::table('room_service_usages', function (Blueprint $table) {
            $table->float('initial_index')->nullable();
            $table->float('final_index')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_service_usages', function (Blueprint $table) {
            $table->dropColumn('initial_index');
            $table->dropColumn('final_index');
        });
    }
};
