<?php

declare(strict_types=1);

namespace App\Neuron\RAG;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\Retrieval\RetrievalInterface;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;

class MetadataFilteredRetrieval implements RetrievalInterface
{
    /**
     * @param  array<string, string|int>  $metadataFilters
     */
    public function __construct(
        private readonly VectorStoreInterface $vectorStore,
        private readonly EmbeddingsProviderInterface $embeddingProvider,
        private readonly array $metadataFilters,
        private readonly int $topK = 12,
    ) {}

    /** @return Document[] */
    public function retrieve(Message $query): array
    {
        $embedding = $this->embeddingProvider->embedText($query->getContent());

        if ($this->vectorStore instanceof ScopedFileVectorStore) {
            return $this->vectorStore->similaritySearchByMetadata($embedding, $this->metadataFilters, $this->topK);
        }

        return array_values(array_filter(
            iterator_to_array($this->vectorStore->similaritySearch($embedding)),
            fn (Document $document): bool => $this->matchesMetadata($document->metadata),
        ));
    }

    /** @param array<string, mixed> $metadata */
    private function matchesMetadata(array $metadata): bool
    {
        foreach ($this->metadataFilters as $key => $value) {
            if (! array_key_exists($key, $metadata) || (string) $metadata[$key] !== (string) $value) {
                return false;
            }
        }

        return true;
    }
}
