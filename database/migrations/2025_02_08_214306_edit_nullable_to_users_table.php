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
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->nullable()->change();
            $table->boolean('gender')->nullable()->change();
            $table->char('identity_card', 12)->nullable()->change();
            $table->text("password")->nullable()->change();
            $table->date('date_of_birth')->nullable()->change();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default('true');
            $table->boolean('is_completed')->default('false');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->nullable(false)->change();
            $table->boolean('gender')->nullable(false)->change();
            $table->char('identity_card', 12)->nullable(false)->change();
            $table->text("password")->nullable(false)->change();
            $table->date('date_of_birth')->nullable(false)->change();
            $table->dropColumn('address');
            $table->dropColumn('is_active');
            $table->dropColumn('is_completed');
        });
    }
};
