<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Xóa primary key cũ
        Schema::dropIfExists('lodging_services');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};

