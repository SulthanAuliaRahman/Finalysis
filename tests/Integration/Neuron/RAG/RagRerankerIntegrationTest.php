<?php

declare(strict_types=1);

namespace Tests\Integration\Neuron\RAG;

use App\Neuron\RAG\FallbackRerankerProcessor;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\PostProcessor\PostProcessorInterface;
use Tests\TestCase;

/**
 * Integration tests for the rag-reranker feature.
 *
 * Actual test methods are added in tasks 7.2, 7.3, and 7.4.
 *
 * @see .kiro/specs/rag-reranker/design.md § "Integration Tests"
 */
class RagRerankerIntegrationTest extends TestCase
{
    // Test methods will be added in tasks 7.2, 7.3, 7.4

    // =========================================================================
    // Task 7.4 — Integration test: Jumlah dokumen < TopN
    //
    // RERANKER_TOP_N=5, sediakan 2 dokumen hasil retrieval.
    // Assert: semua 2 dokumen dikembalikan tanpa error atau padding ke 5.
    //
    // Validates: Requirement 4.3
    // =========================================================================

    /**
     * WHEN jumlah dokumen hasil retrieval lebih sedikit dari nilai TopN yang
     * dikonfigurasi (2 dokumen < topN=5), THEN semua dokumen yang tersedia
     * dikembalikan tanpa error dan tanpa padding ke jumlah TopN.
     *
     * @see requirements.md Requirement 4.3
     */
    public function test_fewer_documents_than_top_n_returns_all_without_padding(): void
    {
        // Arrange: two retrieval documents, TopN configured as 5
        $topN = 5;

        $doc1 = new Document('Pendapatan bersih perusahaan tahun 2023 sebesar Rp 10 miliar.');
        $doc1->addMetadata('company', 'PT Example');
        $doc1->addMetadata('period', '2023');

        $doc2 = new Document('Arus kas operasional positif selama tiga kuartal berturut-turut.');
        $doc2->addMetadata('company', 'PT Example');
        $doc2->addMetadata('period', '2023');

        $inputDocuments = [$doc1, $doc2];

        // Inner processor simulates a reranker configured with topN=5 but only
        // receives 2 documents — it returns however many it has (2), not padded.
        $innerProcessor = new class($topN) implements PostProcessorInterface {
            public function __construct(private int $topN) {}

            public function process(Message $question, array $documents): array
            {
                // Simulate real reranker behaviour: return up to topN docs,
                // but if fewer are available just return what we have.
                return array_slice($documents, 0, $this->topN);
            }
        };

        $fallback = new FallbackRerankerProcessor($innerProcessor);

        $question = new UserMessage('Bagaimana kinerja keuangan PT Example tahun 2023?');

        // Act
        $result = $fallback->process($question, $inputDocuments);

        // Assert: all 2 documents returned — no padding to 5, no exception
        $this->assertCount(
            2,
            $result,
            'FallbackRerankerProcessor must return all available documents (2) when count < topN (5), not pad to topN.'
        );

        // Assert document identity — same objects, no duplication or substitution
        $this->assertSame(
            $inputDocuments[0]->getId(),
            $result[0]->getId(),
            'First document identity must be preserved.'
        );
        $this->assertSame(
            $inputDocuments[1]->getId(),
            $result[1]->getId(),
            'Second document identity must be preserved.'
        );

        // Assert content integrity
        $this->assertSame(
            $inputDocuments[0]->getContent(),
            $result[0]->getContent(),
            'Content of first document must not change.'
        );
        $this->assertSame(
            $inputDocuments[1]->getContent(),
            $result[1]->getContent(),
            'Content of second document must not change.'
        );
    }

    /**
     * WHEN inner reranker returns all available documents (2 < topN=5) AND
     * then an exception is thrown, THEN FallbackRerankerProcessor must still
     * return all 2 original documents — no padding, no crash.
     *
     * This verifies Requirement 4.3 holds even through the fallback code path.
     *
     * @see requirements.md Requirement 4.3
     */
    public function test_fewer_documents_than_top_n_via_fallback_path_returns_all(): void
    {
        // Arrange: 2 documents, topN=5 — inner throws so fallback path is taken
        $doc1 = new Document('Rasio lancar perusahaan berada di atas 2.0 selama dua tahun.');
        $doc2 = new Document('Beban operasional meningkat 15% dibandingkan tahun lalu.');

        $inputDocuments = [$doc1, $doc2];

        $throwingProcessor = new class implements PostProcessorInterface {
            public function process(Message $question, array $documents): array
            {
                throw new \RuntimeException('Simulated reranker unavailable (topN=5, docs=2)');
            }
        };

        \Illuminate\Support\Facades\Log::shouldReceive('channel')->andReturnSelf();
        \Illuminate\Support\Facades\Log::shouldReceive('warning')->andReturnNull();
        \Illuminate\Support\Facades\Log::shouldReceive('info')->andReturnNull();

        $fallback = new FallbackRerankerProcessor($throwingProcessor);
        $question = new UserMessage('Analisis likuiditas perusahaan?');

        // Act — must not throw
        $result = $fallback->process($question, $inputDocuments);

        // Assert: original 2 documents returned, no padding
        $this->assertCount(
            2,
            $result,
            'Fallback path must return all 2 original documents without padding when count < topN.'
        );

        $this->assertSame($doc1->getId(), $result[0]->getId(), 'First doc identity preserved via fallback.');
        $this->assertSame($doc2->getId(), $result[1]->getId(), 'Second doc identity preserved via fallback.');
    }
}
