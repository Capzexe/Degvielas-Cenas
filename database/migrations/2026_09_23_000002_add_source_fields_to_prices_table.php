<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prices', function (Blueprint $table): void {
            $table->string('source_type')->default('test_data')->after('price');
            $table->string('source_label')->default('Testa dati')->after('source_type');
            $table->string('source_url')->nullable()->after('source_label');
        });
    }

    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table): void {
            $table->dropColumn(['source_type', 'source_label', 'source_url']);
        });
    }
};
