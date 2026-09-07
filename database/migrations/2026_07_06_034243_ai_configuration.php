<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_configuration', function (Blueprint $table) {
            $table->uuid();

            // Nama konfigurasi
            $table->string('name')->default('Konfigurasi AI');

            // Status konfigurasi
            $table->boolean('is_active')->default(false);

            // LLM
            $table->enum('llm_provider', [
                'openai',
                'gemini',
                'anthropic',
                'ollama'
            ]);

            $table->string('base_url')->nullable();
            $table->string('llm_model');
            $table->text('llm_api_key')->nullable();

            $table->timestamps();
        });

        // Jika belum ada konfigurasi, tidak ada yang perlu diaktifkan.
        // Jika ada seeder/data awal, konfigurasi pertama dapat dibuat aktif.
        $first = DB::table('ai_configuration')
            ->orderBy('id')
            ->first();

        if ($first) {
            DB::table('ai_configuration')
                ->where('id', $first->id)
                ->update([
                    'name' => 'Konfigurasi Utama',
                    'is_active' => true,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_configuration');
    }
};