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
        // database/migrations/xxxx_create_documents_table.php
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('company');
            $table->string('period');
            $table->string('source_filename');
            $table->integer('total_chunks');
            $table->json('chunks');        // simpan raw chunks dari Python
            $table->json('statements');    // summary per statement type
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
