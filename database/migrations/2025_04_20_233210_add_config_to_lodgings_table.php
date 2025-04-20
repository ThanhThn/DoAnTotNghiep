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
            $table->json('config')->default('{
        "password_for_client": "nestify123"
    }');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lodgings', function (Blueprint $table) {
            $table->dropColumn('config');
        });
    }
};
