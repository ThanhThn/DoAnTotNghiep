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
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->uuid("object_id");
            $table->string("object_type");
            $table->smallInteger("status")->default(1);
            $table->decimal("balance", 10, 2)->default(0);
            $table->timestamps();

            $table->index(["object_id", "object_type", "status"]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
