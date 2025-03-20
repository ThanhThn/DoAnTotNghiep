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
            $table->integer('service_id')->nullable();
            $table->string('service_name')->nullable();
            $table->integer('unit_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_service_usages', function (Blueprint $table) {
            $table->dropColumn('service_id');
            $table->dropColumn('service_name');
            $table->dropColumn('unit_id');
        });
    }
};
