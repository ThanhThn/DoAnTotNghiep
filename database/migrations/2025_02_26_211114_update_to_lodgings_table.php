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
            $table->softDeletes();

            // Unique indexes bao gồm deleted_at
            $table->unique(['name', 'deleted_at'], 'unique_name');
            $table->unique(['user_id', 'deleted_at'], 'unique_user_id');
            $table->unique(['address', 'deleted_at'], 'unique_address');
            $table->unique(['province_id', 'deleted_at'], 'unique_province_id');
            $table->unique(['district_id', 'deleted_at'], 'unique_district_id');
            $table->unique(['ward_id', 'deleted_at'], 'unique_ward_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lodgings', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
