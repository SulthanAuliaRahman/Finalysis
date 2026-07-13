<?php

declare(strict_types=1);

namespace App\Neuron\RAG;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\PostProcessor\PostProcessorInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class FallbackRerankerProcessor implements PostProcessorInterface
{
    public function __construct(
        private readonly PostProcessorInterface $inner
    ) {}

    /**
     * @param Document[] $documents
     * @return Document[]
     */
    public function process(Message $question, array $documents): array
    {
        Log::channel('reranker')->info('FallbackRerankerProcessor CALLED');

        if (empty($documents)) {
            return $documents;
        }

        $query = $question->getContent();

        $formatDocs = function (array $docs): array {
            return array_map(fn (Document $doc) => [
                'id'         => $doc->getId(),
                'source'     => $doc->getSourceType() . ':' . $doc->getSourceName(),
                'score'      => $doc->getScore(),
                'metadata'   => $doc->metadata,
                'snippet'    => mb_substr($doc->getContent(), 0, 120) . (mb_strlen($doc->getContent()) > 120 ? '...' : ''),
            ], $docs);
        };

        Log::channel('reranker')->info('[rag-reranker] Starting document reranking.', [
            'provider'         => $this->inner::class,
            'query'            => $query,
            'documents_count'  => count($documents),
            'documents_before' => $formatDocs($documents),
        ]);

        try {
            $reranked = $this->inner->process($question, $documents);

            if (empty($reranked)) {
                Log::channel('reranker')->warning('[rag-reranker] Reranker returned empty documents, falling back to original.', [
                    'provider' => $this->inner::class,
                    'query'    => $query,
                ]);
                return $documents;
            }

            Log::channel('reranker')->info('[rag-reranker] Reranking completed successfully.', [
                'provider'        => $this->inner::class,
                'query'           => $query,
                'documents_count' => count($reranked),
                'documents_after' => $formatDocs($reranked),
            ]);

            return $reranked;
        } catch (Throwable $e) {
            Log::channel('reranker')->warning('[rag-reranker] Reranker failed, falling back to original documents.', [
                'exception' => $e->getMessage(),
                'provider'  => $this->inner::class,
                'query'     => $query,
                'trace'     => $e->getTraceAsString(),
            ]);
            return $documents;
        }
    }
}