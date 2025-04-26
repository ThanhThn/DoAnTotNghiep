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
        Schema::create('service_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contract_id')->index();
            $table->uuid('room_service_invoice_id')->index();
            $table->decimal('payment_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->timestamp('payment_date');
            $table->timestamp('last_payment_date');
            $table->timestamp('due_date')->comment('Hạn cuối đóng tiền');
            $table->string('payment_method')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_payments');
    }
};
