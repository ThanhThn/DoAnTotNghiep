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
        Schema::table('lodging_services', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('equipments', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('room_services', function (Blueprint $table) {
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lodging_services', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('equipments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('room_services', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
