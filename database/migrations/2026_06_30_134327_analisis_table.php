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
                'belum dihitung',
                'sudah dihitung',
                'Terjadi Perubahan Data!',
            ])->default('belum dihitung');

            $table->text('AI_summary_insight')->nullable();
            $table->timestamps();
        });

        Schema::create('analisis_likuiditas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('current_ratio', 12, 2)->nullable();    // data = 2.53     (FE  2.53x)
            $table->decimal('quick_ratio', 12, 2)->nullable();      // data   = 1.73     (FE  1.73x)
            $table->decimal('cash_ratio', 12, 2)->nullable();       // data    = 0.65     (FE  0.65x)
            $table->text('narasi_likuiditas_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_profitabilitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('ROE', 12, 2)->nullable(); // data = 0.15     (FE  15.43%)
            $table->decimal('ROA', 12, 2)->nullable(); // data = 0.08     (FE  8.77%)
            $table->decimal('net_profit_margin', 12, 2)->nullable(); // data = 0.05     (FE  5.43%)
            $table->text('narasi_profitabilitas_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_solvabilitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('debt_to_equity', 12, 2)->nullable();   // data = 0.65     (FE  65.43%)
            $table->decimal('debt_to_asset', 12, 2)->nullable();    // data = 0.43     (FE  43.21%)
            $table->text('narasi_solvabilitas_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('total_asset_turnover', 12, 2)->nullable(); // data = 1.23  (FE  1.23x)
            $table->text('narasi_aktivitas_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_dupont', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('net_profit_margin', 12, 2)->nullable();    // data = 0.05  (FE 5.43%)
            $table->decimal('total_asset_turnover', 12, 2)->nullable(); // data = 1.23  (FE 1.23x)
            $table->decimal('leverage_multiplier', 12, 2)->nullable();  // data = 1.88  (FE 1.88x)
            $table->decimal('roe', 12, 2)->nullable();                  // data = 0.15  (FE 15.43%)
            $table->text('narasi_dupont_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_commonsize', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();

            // Common-size Income Statement (basis Pendapatan = 100%)
            $table->decimal('hpp_persen', 12, 2)->nullable();
            $table->decimal('laba_kotor_persen', 12, 2)->nullable();
            $table->decimal('beban_lain_pajak_persen', 12, 2)->nullable(); // gabungan OpEx+Bunga+Pajak
            $table->decimal('laba_bersih_persen', 12, 2)->nullable();

            // Common-size Balance Sheet (basis Total Aset = 100%)
            $table->decimal('aset_lancar_persen', 12, 2)->nullable();
            $table->decimal('aset_tetap_persen', 12, 2)->nullable();
            $table->decimal('liabilitas_lancar_persen', 12, 2)->nullable();
            $table->decimal('liabilitas_panjang_persen', 12, 2)->nullable();
            $table->decimal('ekuitas_persen', 12, 2)->nullable();

            $table->text('narasi_commonsize_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_trend', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->text('narasi_trend_akun_utama_AI')->nullable();
            $table->text('narasi_trend_rasio_AI')->nullable();
            $table->text('narasi_trend_dupont_AI')->nullable();
            $table->text('narasi_trend_commonsize_AI')->nullable();
            $table->text('narasi_trend_arus_kas_AI')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analisis_trend');
        Schema::dropIfExists('analisis_commonsize');
        Schema::dropIfExists('analisis_dupont');
        Schema::dropIfExists('analisis_likuiditas');
        Schema::dropIfExists('analisis_profitabilitas');
        Schema::dropIfExists('analisis_solvabilitas');
        Schema::dropIfExists('analisis_aktivitas');
        Schema::dropIfExists('analisis');
    }
};
