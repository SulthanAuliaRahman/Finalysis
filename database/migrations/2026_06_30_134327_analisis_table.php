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
                'rasio tersedia',
                'sudah dianalisis',
                'Terjadi Perubahan Data!',
            ])->default('belum dianalisis');

            $table->text('AI_summary_insight')->nullable();
            $table->timestamps();
        });

        Schema::create('analisis_likuiditas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('current_ratio', 12, 6)->nullable();    // data = 2.534561     (FE  2.53x)
            $table->decimal('quick_ratio', 12, 6)->nullable();      // data   = 1.732511     (FE  1.73x)
            $table->decimal('cash_ratio', 12, 6)->nullable();       // data    = 0.654321     (FE  0.65x)
            $table->text('narasi_likuiditas_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_profitabilitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('ROE', 12, 6)->nullable(); // data = 0.154321     (FE  15.43%)
            $table->decimal('ROA', 12, 6)->nullable(); // data = 0.087654     (FE  8.77%)
            $table->decimal('net_profit_margin', 12, 6)->nullable(); // data = 0.054321     (FE  5.43%)
            $table->text('narasi_profitabilitas_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_solvabilitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('debt_to_equity', 12, 6)->nullable(); // data = 0.654321     (FE  65.43%)
            $table->decimal('debt_to_asset', 12, 6)->nullable(); // data = 0.432109     (FE  43.21%)
            $table->text('narasi_solvabilitas_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('total_asset_turnover', 12, 6)->nullable(); // data = 1.234567     (FE  1.23x)
            $table->text('narasi_aktivitas_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_dupont', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();
            $table->decimal('net_profit_margin', 12, 6)->nullable();    // data = 0.054321  (FE 5.43%)
            $table->decimal('total_asset_turnover', 12, 6)->nullable(); // data = 1.234567  (FE 1.23x)
            $table->decimal('leverage_multiplier', 12, 6)->nullable();  // data = 1.876543  (FE 1.88x)
            $table->decimal('roe', 12, 6)->nullable();                  // data = 0.154321  (FE 15.43%)
            $table->text('narasi_dupont_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_commonsize', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete();

            // Common-size Income Statement (basis Pendapatan = 100%)
            $table->decimal('hpp_persen', 12, 6)->nullable();
            $table->decimal('laba_kotor_persen', 12, 6)->nullable();
            $table->decimal('beban_lain_pajak_persen', 12, 6)->nullable(); // gabungan OpEx+Bunga+Pajak
            $table->decimal('laba_bersih_persen', 12, 6)->nullable();

            // Common-size Balance Sheet (basis Total Aset = 100%)
            $table->decimal('aset_lancar_persen', 12, 6)->nullable();
            $table->decimal('aset_tetap_persen', 12, 6)->nullable();
            $table->decimal('liabilitas_lancar_persen', 12, 6)->nullable();
            $table->decimal('liabilitas_panjang_persen', 12, 6)->nullable();
            $table->decimal('ekuitas_persen', 12, 6)->nullable();

            $table->text('narasi_commonsize_AI')->nullable();

            $table->timestamps();
        });

        Schema::create('analisis_trend', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete(); // pemilik trend (mis. Q4 2024)
            $table->boolean('is_data_ilustratif')->default(false); // true jika cuma 1 titik data (belum ada pembanding)
            $table->text('narasi_trend_AI')->nullable();
            $table->text('narasi_rasio_AI')->nullable()->after('narasi_trend_AI');
            $table->text('narasi_dupont_AI')->nullable()->after('narasi_rasio_AI');
            $table->text('narasi_commonsize_AI')->nullable()->after('narasi_dupont_AI');

            $table->timestamps();
        });

        Schema::create('analisis_trend_periode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analisis_trend_id')->constrained('analisis_trend')->cascadeOnDelete();
            $table->foreignId('analisis_id')->constrained('analisis')->cascadeOnDelete(); // periode historis yang dirujuk
            $table->unsignedTinyInteger('urutan'); // 1,2,3,4... urutan kronologis dalam scope trend ini

            // 7 Akun Utama (snapshot absolut per periode)
            $table->decimal('pendapatan', 20, 2)->nullable();
            $table->decimal('laba_kotor', 20, 2)->nullable();
            $table->decimal('laba_bersih', 20, 2)->nullable();
            $table->decimal('total_assets', 20, 2)->nullable();
            $table->decimal('kas_setara_kas', 20, 2)->nullable();
            $table->decimal('total_equity', 20, 2)->nullable();
            $table->decimal('net_cash_flow', 20, 2)->nullable();

            // Growth % dari titik sebelumnya (null untuk titik data pertama)
            $table->decimal('growth_pendapatan', 12, 6)->nullable();
            $table->decimal('growth_laba_kotor', 12, 6)->nullable();
            $table->decimal('growth_laba_bersih', 12, 6)->nullable();
            $table->decimal('growth_total_assets', 12, 6)->nullable();
            $table->decimal('growth_kas_setara_kas', 12, 6)->nullable();
            $table->decimal('growth_total_equity', 12, 6)->nullable();
            $table->decimal('growth_net_cash_flow', 12, 6)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analisis_trend_periode');
        Schema::table('analisis_trend', function (Blueprint $table) {
            $table->dropColumn(['narasi_rasio_AI', 'narasi_dupont_AI', 'narasi_commonsize_AI']);
        });

        Schema::dropIfExists('analisis_commonsize');
        Schema::dropIfExists('analisis_dupont');
        Schema::dropIfExists('analisis_likuiditas');
        Schema::dropIfExists('analisis_profitabilitas');
        Schema::dropIfExists('analisis_solvabilitas');
        Schema::dropIfExists('analisis_aktivitas');
        Schema::dropIfExists('analisis');
    }
};