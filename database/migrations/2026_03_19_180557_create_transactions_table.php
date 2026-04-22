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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('asset_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['buy', 'sell']);

            $table->integer('quantity')->nullable();
            $table->decimal('price_per_asset', 15, 2)->nullable();
            $table->decimal('total', 15, 2);

            $table->timestamp('executed_at');
            $table->timestamps();

            $table->index('type');
            $table->index('executed_at');
            $table->index(['asset_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
