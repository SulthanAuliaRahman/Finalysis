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
        if (empty($documents)) {
            return $documents;
        }

        try {
            $reranked = $this->inner->process($question, $documents);

            if (empty($reranked)) {
                Log::warning('[rag-reranker] Reranker returned empty documents, falling back to original.', [
                    'provider' => $this->inner::class,
                ]);
                return $documents;
            }

            return $reranked;
        } catch (Throwable $e) {
            Log::warning('[rag-reranker] Reranker failed, falling back to original documents.', [
                'exception' => $e->getMessage(),
                'provider'  => $this->inner::class,
            ]);
            return $documents;
        }
    }
}
