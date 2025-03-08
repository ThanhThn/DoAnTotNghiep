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
        Schema::table('equipments', function (Blueprint $table) {
            $table->integer('quantity');
            $table->smallInteger('type');
            $table->text('thumbnail');
            $table->integer('remaining_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('type');
            $table->dropColumn('thumbnail');
            $table->dropColumn('remaining_quantity');
        });
    }
};
