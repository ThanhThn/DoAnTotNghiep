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
        Schema::table('chat_histories', function (Blueprint $table) {
            $table->renameColumn('user_id', 'sender_id');
            $table->dropColumn('room_id');
            $table->enum('role_sender', ['manager', 'user'])->default('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_histories', function (Blueprint $table) {
            $table->renameColumn('sender_id', 'user_id');
            $table->dropColumn('role_sender');
            $table->uuid('room_id');
        });
    }
};
