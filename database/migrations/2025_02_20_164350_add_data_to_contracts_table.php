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
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('full_name');
            $table->boolean('gender');
            $table->char('phone', 10);
            $table->char('identity_card', 12);
            $table->date('date_of_birth');
            $table->json("relatives")->nullable();

            $table->index(['identity_card', 'phone', 'full_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('full_name');
            $table->dropColumn('gender');
            $table->dropColumn('phone');
            $table->dropColumn('identity_card');
            $table->dropColumn('date_of_birth');
            $table->dropColumn('relatives');
        });
    }
};
