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
        // Data Analisis ini tergantung dengan dokumen jika ada analisis periode Q3 2023 baru bisa ada Analisis Q3 2023
        Schema::create('analisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->constrained('perusahaan')->cascadeOnDelete();
            $table->enum('periode_type', [
                'annual',
                'quarterly',
                'monthly'
            ]);
            $table->unsignedSmallInteger('tahun');// tahun
            $table->unsignedTinyInteger('quarter')->nullable();// NULL jika annual
            $table->unsignedTinyInteger('bulan')->nullable(); // NULL jika annual / quarterly
            $table->enum('status', [
                'belum dianalisis',
                'sudah dianalisis',
                'Terjadi Perubahan Data!',
            ])->default('belum dianalisis');

            $table->text('AI_summary_insight')->nullable();
            $table->timestamps();
        });

        Schema::create('analisis_likuiditas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('current_ratio', 10, 2)->nullable();
            $table->decimal('quick_ratio', 10, 2)->nullable();
            $table->decimal('cash_ratio', 10, 2)->nullable();
            $table->text('narasi_likuiditas_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_profitabilitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('ROE', 10, 2)->nullable();
            $table->decimal('ROA', 10, 2)->nullable();
            $table->decimal('net_profit_margin', 10, 2)->nullable();
            $table->text('narasi_profitabilitas_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_solvabilitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('debt_to_equity', 10, 2)->nullable();
            $table->decimal('debt_to_asset', 10, 2)->nullable();
            $table->text('narasi_solvabilitas_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('total_asset_turnover', 10, 2)->nullable();
            $table->text('narasi_aktivitas_AI')->nullable();

            $table->timestamps();
        });

        // untuk sekarang ini saja dulu


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analisis_likuiditas');
        Schema::dropIfExists('analisis_profitabilitas');
        Schema::dropIfExists('analisis_solvabilitas');
        Schema::dropIfExists('analisis_aktivitas');
        Schema::dropIfExists('analisis');
    }
};
