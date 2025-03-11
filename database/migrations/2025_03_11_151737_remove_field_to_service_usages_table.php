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
        // First, rename the table
        Schema::rename('service_usages', 'room_service_usages');

        // Then modify the table structure
        Schema::table('room_service_usages', function (Blueprint $table) {
            $table->dropColumn([
                'quantity',
                'payment_method',
                'payment_date',
                'last_payment_date',
                'previous_value',
                'current_value',
                'status'
            ]);

            $table->decimal('value', 10, 2);
            $table->boolean('finalized')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename the table back
        Schema::rename('room_service_usages', 'service_usages');

        // Restore the removed columns
        Schema::table('service_usages', function (Blueprint $table) {
            $table->float('quantity');
            $table->double('previous_value');
            $table->double('current_value');
            $table->smallInteger('status')->index();
            $table->timestamp('payment_date');
            $table->timestamp('last_payment_date');
            $table->string('payment_method');

            $table->dropColumn(['value', 'finalized']);
        });
    }
};
