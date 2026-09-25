<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->enum('fuel_type', ['95', '98', 'Diesel', 'LPG']);
            $table->decimal('price', 8, 3);
            $table->timestamp('fetched_at')->index();
            $table->timestamps();

            $table->index(['fuel_type', 'price']);
            $table->index(['station_id', 'fuel_type', 'fetched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
