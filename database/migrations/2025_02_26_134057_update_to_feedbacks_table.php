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
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropColumn('object_to_type');
            $table->dropColumn('object_from_type');
            $table->renameColumn('object_to_id', 'room_id');
            $table->renameColumn('object_from_id', 'lodging_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->renameColumn('room_id', 'object_to_id');
            $table->renameColumn('lodging_id', 'object_from_id');
            $table->string('object_to_type');
            $table->string('object_from_type');
        });
    }
};
