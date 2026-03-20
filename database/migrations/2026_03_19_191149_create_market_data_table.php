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
        Schema::create('market_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('asset_id')->constrained()->cascadeOnDelete();

            $table->decimal('price', 15, 6);
            $table->timestamp('fetched_at');

            $table->timestamps();

            $table->index('fetched_at');
            $table->index(['asset_id', 'fetched_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_data');
    }
};
