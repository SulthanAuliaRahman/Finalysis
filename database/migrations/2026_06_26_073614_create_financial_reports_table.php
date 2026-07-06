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
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->id();
           
            $table->foreignId('company_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->enum('report_type', [
                'neraca',
                'laba_rugi',
                'arus_kas',
            ]);

            $table->string('report_title');

            $table->date('period_start');

            $table->date('period_end');

            $table->enum('period_type',[
                'MONTHLY',
                'QUARTERLY',
                'SEMESTER',
                'ANNUAL'
            ]);

            $table->string('url_path');

            $table->json('financial_data')->nullable();

            $table->unique([
                'company_id',
                'report_type',
                'period_start',
                'period_end',
                'period_type'
            ]);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_reports');
    }
};
