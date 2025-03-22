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
        Schema::table('channel_members', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->string('member_type');
            $table->renameColumn('user_id', 'member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channel_members', function (Blueprint $table) {
            $table->enum('role', ['manager', 'user'])->default('user');
            $table->string('member_type');
            $table->renameColumn('member_id', 'user_id');
        });
    }
};
