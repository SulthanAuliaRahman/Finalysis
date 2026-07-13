<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_referensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->string('section', 40);
            $table->unsignedBigInteger('dokumen_id')->nullable();
            $table->unsignedInteger('chunk_index')->nullable();
            $table->unsignedTinyInteger('urutan');
            $table->decimal('score', 10, 6)->nullable();
            $table->longText('text');
            $table->timestamps();

            $table->index(['analisis_id', 'section', 'dokumen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_referensi');
    }
};
