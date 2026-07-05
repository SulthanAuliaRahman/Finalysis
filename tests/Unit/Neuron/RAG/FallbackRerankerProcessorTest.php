<?php

declare(strict_types=1);

namespace Tests\Unit\Neuron\RAG;

use App\Neuron\RAG\FallbackRerankerProcessor;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
use Mockery;
use NeuronAI\Chat\Enums\MessageRole;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\PostProcessor\PostProcessorInterface;
use RuntimeException;
use Tests\TestCase;

class FallbackRerankerProcessorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeQuestion(): Message
    {
        return new Message(MessageRole::USER, 'Apa itu laporan keuangan?');
    }

    private function makeDocuments(int $count = 2): array
    {
        $docs = [];
        for ($i = 0; $i < $count; $i++) {
            $doc = new Document("Konten dokumen ke-{$i}");
            $doc->addMetadata('company', "Company {$i}");
            $docs[] = $doc;
        }
        return $docs;
    }

    /**
     * Test 1: Input $documents kosong → kembalikan array kosong (early return, tidak memanggil inner)
     */
    public function test_empty_documents_returns_empty_without_calling_inner(): void
    {
        $inner = Mockery::mock(PostProcessorInterface::class);
        // inner->process() should never be called
        $inner->shouldNotReceive('process');

        $processor = new FallbackRerankerProcessor($inner);
        $result = $processor->process($this->makeQuestion(), []);

        $this->assertSame([], $result);
    }

    /**
     * Test 2: Mock inner reranker sukses → output dari inner dikembalikan
     */
    public function test_successful_inner_reranker_returns_inner_output(): void
    {
        $documents  = $this->makeDocuments(3);
        $reranked   = array_reverse($documents); // reversed order = different from input

        $inner = Mockery::mock(PostProcessorInterface::class);
        $inner->shouldReceive('process')
            ->once()
            ->andReturn($reranked);

        $processor = new FallbackRerankerProcessor($inner);
        $result    = $processor->process($this->makeQuestion(), $documents);

        $this->assertSame($reranked, $result);
    }

    /**
     * Test 3: Mock inner melempar RuntimeException → dokumen asli dikembalikan
     */
    public function test_runtime_exception_returns_original_documents(): void
    {
        $documents = $this->makeDocuments(2);

        $inner = Mockery::mock(PostProcessorInterface::class);
        $inner->shouldReceive('process')
            ->once()
            ->andThrow(new RuntimeException('Simulated RuntimeException'));

        $processor = new FallbackRerankerProcessor($inner);
        $result    = $processor->process($this->makeQuestion(), $documents);

        $this->assertSame($documents, $result);
    }

    /**
     * Test 4: Mock inner melempar GuzzleHttp\Exception\ConnectException → dokumen asli dikembalikan
     */
    public function test_connect_exception_returns_original_documents(): void
    {
        $documents = $this->makeDocuments(2);

        $connectException = new ConnectException(
            'cURL error 28: Connection timed out',
            new Request('POST', 'https://api.cohere.ai/rerank')
        );

        $inner = Mockery::mock(PostProcessorInterface::class);
        $inner->shouldReceive('process')
            ->once()
            ->andThrow($connectException);

        $processor = new FallbackRerankerProcessor($inner);
        $result    = $processor->process($this->makeQuestion(), $documents);

        $this->assertSame($documents, $result);
    }

    /**
     * Test 5: Mock inner mengembalikan array kosong → dokumen asli dikembalikan
     */
    public function test_empty_reranker_result_returns_original_documents(): void
    {
        $documents = $this->makeDocuments(3);

        $inner = Mockery::mock(PostProcessorInterface::class);
        $inner->shouldReceive('process')
            ->once()
            ->andReturn([]);

        $processor = new FallbackRerankerProcessor($inner);
        $result    = $processor->process($this->makeQuestion(), $documents);

        $this->assertSame($documents, $result);
    }

    /**
     * Test 6: Mock inner melempar exception → Log::warning() dipanggil dengan pesan exception dan provider class
     */
    public function test_exception_triggers_log_warning_with_exception_message_and_provider(): void
    {
        $documents        = $this->makeDocuments(2);
        $exceptionMessage = 'API authentication failed';

        $inner = Mockery::mock(PostProcessorInterface::class);
        $inner->shouldReceive('process')
            ->once()
            ->andThrow(new RuntimeException($exceptionMessage));

        $innerClass = $inner::class;

        Log::shouldReceive('warning')
            ->once()
            ->with(
                '[rag-reranker] Reranker failed, falling back to original documents.',
                Mockery::on(function (array $context) use ($exceptionMessage, $innerClass) {
                    return $context['exception'] === $exceptionMessage
                        && $context['provider'] === $innerClass;
                })
            );

        $processor = new FallbackRerankerProcessor($inner);
        $processor->process($this->makeQuestion(), $documents);

        $this->addToAssertionCount(1);
    }

    /**
     * Test 7: Mock inner mengembalikan kosong → Log::warning() dipanggil dengan provider class
     */
    public function test_empty_result_triggers_log_warning_with_provider(): void
    {
        $documents = $this->makeDocuments(2);

        $inner = Mockery::mock(PostProcessorInterface::class);
        $inner->shouldReceive('process')
            ->once()
            ->andReturn([]);

        $innerClass = $inner::class;

        Log::shouldReceive('warning')
            ->once()
            ->with(
                '[rag-reranker] Reranker returned empty documents, falling back to original.',
                Mockery::on(function (array $context) use ($innerClass) {
                    return $context['provider'] === $innerClass;
                })
            );

        $processor = new FallbackRerankerProcessor($inner);
        $processor->process($this->makeQuestion(), $documents);

        $this->addToAssertionCount(1);
    }
}
