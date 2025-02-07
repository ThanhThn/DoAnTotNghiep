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
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->index();
            $table->uuid('room_id')->index();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('monthly_rent', 10)->comment('Giá thuê hằng tháng')->nullable();
            $table->decimal('deposit_amount', 10)->comment("Số tiền cọc");
            $table->decimal('remain_amount', 10)->comment("Số tiền còn tại (remain_amount = deposit_amount - monthly_rent)");
            $table->smallInteger('status')->index();
            $table->smallInteger('lease_duration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
