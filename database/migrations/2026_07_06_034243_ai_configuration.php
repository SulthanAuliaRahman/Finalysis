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
            $table->string('llm_provider',50);
            $table->string('llm_url',255)->nullable();
            $table->string('llm_model',100);
            $table->text('llm_api_key')->nullable();
            
            //Embedding
            $table->string('embedding_provider',50);
            $table->string('embedding_url',255)->nullable();
            $table->string('embedding_model',100);
            $table->text('embedding_api_key')->nullable();

            //Reranker
            $table->string('reranker_provider', 50);
            $table->string('reranker_model', 100);
            $table->unsignedTinyInteger('reranker_top_n')->default(3);
            $table->string('reranker_api_key')->nullable();
            $table->string('localai_url')->nullable();

            //Vector Store
            $table->string('vector_store_driver')->default('file');
            $table->string('vector_store_path')->nullable();
            $table->string('vector_store_name')->default('demo');

            // Prompt
            $table->longText('system_prompt')->nullable();

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
