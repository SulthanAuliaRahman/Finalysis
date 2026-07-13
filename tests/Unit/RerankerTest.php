<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use App\Neuron\RAG\FallbackRerankerProcessor;
use NeuronAI\RAG\Document;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\RAG\PostProcessor\PostProcessorInterface;

class RerankerTest extends TestCase
{

    public function test_fallback_reranker_processor_logs_data_and_falls_back_on_error(): void
    {
        // 1. Arrange: Create a mock inner post-processor that throws an exception
        $mockInner = $this->createMock(PostProcessorInterface::class);
        $mockInner->method('process')
            ->willThrowException(new \RuntimeException("API Connection timed out"));

        $processor = new FallbackRerankerProcessor($mockInner);

        $question = new UserMessage("Test question");
        $doc1 = new Document("Doc 1 content");
        $doc1->setScore(0.5);
        $doc2 = new Document("Doc 2 content");
        $doc2->setScore(0.8);

        $documents = [$doc1, $doc2];

        // We want to verify that logs are written
        Log::shouldReceive('channel')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('FallbackRerankerProcessor CALLED');

        Log::shouldReceive('info')
            ->once()
            ->with('[rag-reranker] Starting document reranking.', \Mockery::on(function ($ctx) {
                return $ctx['query'] === 'Test question' && $ctx['documents_count'] === 2;
            }));

        Log::shouldReceive('warning')
            ->once()
            ->with('[rag-reranker] Reranker failed, falling back to original documents.', \Mockery::on(function ($ctx) {
                return str_contains($ctx['exception'], 'API Connection timed out');
            }));

        // 2. Act
        $result = $processor->process($question, $documents);

        // 3. Assert: Fallback returned the original documents
        $this->assertCount(2, $result);
        $this->assertEquals($documents, $result);
    }

    public function test_fallback_reranker_processor_logs_successful_rerank(): void
    {
        // 1. Arrange: Create a mock inner post-processor that succeeds
        $mockInner = $this->createMock(PostProcessorInterface::class);
        
        $doc1 = new Document("Doc 1 content");
        $doc1->setScore(0.5);
        $doc2 = new Document("Doc 2 content");
        $doc2->setScore(0.8);
        $documents = [$doc1, $doc2];

        // Rerank reorders them and updates scores
        $rerankedDoc1 = clone $doc2;
        $rerankedDoc1->setScore(0.95);
        $rerankedDoc2 = clone $doc1;
        $rerankedDoc2->setScore(0.60);
        $expectedReranked = [$rerankedDoc1, $rerankedDoc2];

        $mockInner->method('process')
            ->willReturn($expectedReranked);

        $processor = new FallbackRerankerProcessor($mockInner);
        $question = new UserMessage("Test question");

        Log::shouldReceive('channel')
            ->andReturnSelf();

        Log::shouldReceive('info')
            ->with('FallbackRerankerProcessor CALLED');

        Log::shouldReceive('info')
            ->once()
            ->with('[rag-reranker] Starting document reranking.', \Mockery::any());

        Log::shouldReceive('info')
            ->once()
            ->with('[rag-reranker] Reranking completed successfully.', \Mockery::on(function ($ctx) {
                return $ctx['documents_count'] === 2 && isset($ctx['documents_after']);
            }));

        // 2. Act
        $result = $processor->process($question, $documents);

        // 3. Assert
        $this->assertCount(2, $result);
        $this->assertEquals($expectedReranked[0]->getScore(), $result[0]->getScore());
        $this->assertEquals($expectedReranked[1]->getScore(), $result[1]->getScore());
    }

    public function test_base_rag_agent_constructor_fallback_when_db_offline(): void
    {
        $agent = new class extends \App\Neuron\RAG\BaseRagAgent {
            protected function instructions(): string
            {
                return "Test instructions";
            }
            public function getProperties(): array
            {
                return [
                    'providerName' => $this->providerName,
                    'aiModel' => $this->aiModel,
                    'embeddingProviderName' => $this->embeddingProviderName,
                    'embeddingModel' => $this->embeddingModel,
                ];
            }
        };

        $props = $agent->getProperties();
        $this->assertEquals('ollama', $props['providerName']);
        $this->assertEquals('qwen2.5:3b', $props['aiModel']);
        $this->assertEquals('ollama', $props['embeddingProviderName']);
        $this->assertEquals('qwen3-embedding:4b', $props['embeddingModel']);
    }
}
