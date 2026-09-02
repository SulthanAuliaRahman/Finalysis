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
        Schema::create('perusahaan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('sektor')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('dokumen', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('perusahaan_id')->constrained('perusahaan')->cascadeOnDelete();
            $table->string('nama_file');
            $table->string('storage_path');
            $table->enum('periode_type', [
                'annual',
                'quarterly',
                'monthly'
            ]);
            $table->unsignedSmallInteger('tahun');// tahun
            $table->unsignedTinyInteger('quarter')->nullable();// NULL jika annual
            $table->unsignedTinyInteger('bulan')->nullable(); // NULL jika annual / quarterly

            $table->timestamps();
        });

        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dokumen_id')->constrained('dokumen')->cascadeOnDelete();

            $table->string('nama_akun');
            $table->enum('kelompok_akun',[
                'aset',
                'liabilitas',
                'ekuitas',
                'pendapatan',
                'beban',
                'lainnya'
            ])->default('lainnya');

            $table->enum('sub_kelompok_akun',[
                'kas_setara_kas',
                'aset_lancar_selain_kas',
                'aset_tetap',
                'liabilitas_jangka_pendek',
                'liabilitas_jangka_panjang',
                'ekuitas',
                'pendapatan',
                'beban',
                'beban_pajak',
                'lainnya'
            ])->default('lainnya');

            $table->decimal('nilai_akun',20, 2)->nullable();

            $table->timestamps();
        });

        Schema::create('laba_rugi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dokumen_id')->constrained('dokumen')->cascadeOnDelete();
            $table->decimal('total_beban', 20, 2)->nullable();
            $table->decimal('total_biaya_pajak', 20, 2)->nullable();
            $table->decimal('total_pendapatan', 20, 2)->nullable();
            $table->decimal('laba_bersih_sebelum_pajak', 20, 2)->nullable();
            $table->decimal('laba_bersih_sesudah_pajak', 20, 2)->nullable();

            $table->timestamps();
        });

        Schema::create('neraca', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dokumen_id')->constrained('dokumen')->cascadeOnDelete();
            $table->decimal('total_kas_setara_kas', 20, 2)->nullable();
            $table->decimal('total_asset_lancar', 20, 2)->nullable();
            $table->decimal('total_asset_tetap', 20, 2)->nullable();
            $table->decimal('total_asset', 20, 2)->nullable();
            $table->decimal('total_liabilities_pendek', 20, 2)->nullable();
            $table->decimal('total_liabilities_panjang', 20, 2)->nullable();
            $table->decimal('total_liabilities', 20, 2)->nullable();
            $table->decimal('total_equitas', 20, 2)->nullable();

            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('laba_rugi');
        Schema::dropIfExists('neraca');
        Schema::dropIfExists('dokumen');
        Schema::dropIfExists('perusahaan');
    }
};
