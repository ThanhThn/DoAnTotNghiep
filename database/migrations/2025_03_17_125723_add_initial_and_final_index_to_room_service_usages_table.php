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
            $table->renameColumn('is_finalized_early', 'is_need_close');
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
            $table->renameColumn('is_need_close', 'is_finalized_early');
        });
    }
};
