<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::create('ai_configuration', function (Blueprint $table){
            $table->id();

            //LLM
            $table->enum('llm_provider', ['openai','gemini','anthropic','ollama']);
            $table->string('base_url')->nullable();
            $table->string('llm_model');
            $table->text('llm_api_key')->nullable();
            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_configuration');
    }
};
