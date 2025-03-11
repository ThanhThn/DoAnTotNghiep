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
        Schema::create('service_usage_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lodging_service_id');
            $table->uuid('contract_id');
            $table->uuid('service_usage_id');
            $table->float('total_usage')->comment('Tổng số lượng đã dùng');
            $table->decimal('price_per_unit', 10, 2)->comment('Giá tính trên từng đơn vị');
            $table->decimal('total_amount')->comment('Tổng giá toàn bộ lần sử dụng: total_amount * price_per_unit');
            $table->timestamp('usage_date')->comment('Thời điểm sử dụng dịch vụ');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_usage_log');
    }
};
