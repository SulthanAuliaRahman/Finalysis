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

            $table->foreignId('report_type_id')
            ->constrained('financial_report_types')
            ->cascadeOnDelete();

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

            $table->unique([
                'company_id',
                'report_type_id',
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
