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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organization_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->string('company_name',200);
            $table->string('company_type',100)
            ->nullable();

            $table->string('company_scale',50)
            ->nullable();

            $table->text('description')
            ->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
