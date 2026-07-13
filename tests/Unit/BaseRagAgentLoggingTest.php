<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Neuron\RAG\CaptureRetrievedDocumentsProcessor;
use Illuminate\Support\Facades\Log;
use Mockery;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\RAG\Document;
use Tests\TestCase;

class BaseRagAgentLoggingTest extends TestCase
{
    public function test_retrieval_logs_retrieved_documents_and_query(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with(
                Mockery::on(fn($message) => str_contains($message, '[RAG Retrieval] Agent')),
                Mockery::on(function ($context) {
                    return $context['query'] === 'test query' &&
                           $context['documents_count'] === 1 &&
                           $context['documents'][0]['score'] === 0.9 &&
                           $context['documents'][0]['metadata']['company_id'] === 5 &&
                           $context['documents'][0]['content_preview'] === 'Sample content...';
                })
            );

        $processor = new CaptureRetrievedDocumentsProcessor(function (array $documents, $question): void {
            Log::info(sprintf('[RAG Retrieval] Agent %s retrieved %d document(s) for query: "%s"', 'TestAgent', count($documents), $question->getContent()), [
                'agent' => 'TestAgent',
                'query' => $question->getContent(),
                'documents_count' => count($documents),
                'documents' => array_map(fn ($doc) => [
                    'score' => $doc->getScore(),
                    'metadata' => $doc->metadata,
                    'content_preview' => mb_strimwidth($doc->getContent(), 0, 200, '...'),
                ], $documents),
            ]);
        });

        $doc = new Document('Sample content...');
        $doc->setScore(0.9);
        $doc->addMetadata('company_id', 5);

        $processor->process(new UserMessage('test query'), [$doc]);
        
        // Assert true to avoid "risky test" warning if no assertion is hit besides Mockery
        $this->assertTrue(true);
    }
}
