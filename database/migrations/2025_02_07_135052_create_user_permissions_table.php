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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->uuid('user_id');
            $table->uuid('lodging_id');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->primary(['permission_id', 'user_id', 'lodging_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
