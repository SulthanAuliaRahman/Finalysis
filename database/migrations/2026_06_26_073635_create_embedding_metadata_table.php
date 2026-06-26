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
        Schema::create('embedding_metadata', function (Blueprint $table) {
            $table->id();

            $table->foreignId('financial_report_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->integer('page_number');
            $table->integer('chunk_index');
            $table->longText('chunk_text');
            $table->string('vector_id');
            $table->string('embedding_model',100);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embedding_metadata');
    }
};
