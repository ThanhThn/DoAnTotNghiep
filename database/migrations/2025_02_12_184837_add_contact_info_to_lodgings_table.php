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
            $table->char('phone_contact', 10)->nullable();
            $table->string('email_contact')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lodgings', function (Blueprint $table) {
            $table->dropColumn('phone_contact');
            $table->dropColumn('email_contact');
        });
    }
};
