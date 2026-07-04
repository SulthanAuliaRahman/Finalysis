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
            $table->id();
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
            $table->json('statement_types')->nullable(); // ["balance_sheet","income_statement","cash_flow"]
            $table->unsignedBigInteger('ukuran_file')->nullable();
            $table->enum('status', [
                'menunggu', // butuh kah?
                'diekstrak',
                'dichunk',
                'diembed',
                'selesai'
            ])->default('menunggu');
            $table->timestamps();
        });

        Schema::create('neraca', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->unique()->constrained('dokumen')->cascadeOnDelete();
            $table->decimal('cash_equivalent', 20, 2)->nullable();
            $table->decimal('inventory', 20, 2)->nullable();
            $table->decimal('total_equity', 20, 2)->nullable();
            $table->decimal('total_liabilities', 20, 2)->nullable();
            $table->decimal('current_liabilities', 20, 2)->nullable();
            $table->decimal('total_assets', 20, 2)->nullable();
            $table->decimal('current_assets', 20, 2)->nullable();

            // menyimpan posisi hasil ekstraksi
            $table->json('found_at')->nullable();
            $table->timestamps();
        });

        Schema::create('laba_rugi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->unique()->constrained('dokumen')->cascadeOnDelete();
            $table->decimal('pendapatan', 20, 2)->nullable();
            $table->decimal('laba_kotor', 20, 2)->nullable();
            $table->decimal('laba_bersih', 20, 2)->nullable();

            $table->json('found_at')->nullable();
            $table->timestamps();
        });

        Schema::create('arus_kas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->unique()->constrained('dokumen')->cascadeOnDelete();
            $table->decimal('cash_flow_from_operations', 20, 2)->nullable();
            $table->decimal('cash_flow_from_investing', 20, 2)->nullable();
            $table->decimal('cash_flow_from_financing', 20, 2)->nullable();
            $table->decimal('kas_masuk', 20, 2)->nullable();
            $table->decimal('kas_keluar', 20, 2)->nullable();

            $table->json('found_at')->nullable();
            $table->timestamps();
        });

        Schema::create('chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('dokumen')->cascadeOnDelete();
            $table->unsignedInteger('chunk_index');
            $table->longText('text');
            // $table->vector('embedding', 1536)->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('has_table')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chunks');
        Schema::dropIfExists('arus_kas');
        Schema::dropIfExists('laba_rugi');
        Schema::dropIfExists('neraca');
        Schema::dropIfExists('dokumen');
        Schema::dropIfExists('perusahaan');
    }
};
