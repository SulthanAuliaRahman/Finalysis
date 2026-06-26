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
        Schema::create('financial_ratio_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('financial_analysis_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->foreignId('financial_ratio_type_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->decimal('value',12,4);
            $table->timestamps();

            $table->unique([
                'financial_analysis_id',
                'financial_ratio_type_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_ratio_results');
    }
};
