<?php

declare(strict_types=1);

namespace App\Neuron\RAG;

use NeuronAI\RAG\Document;
use NeuronAI\RAG\VectorSimilarity;
use NeuronAI\RAG\VectorStore\FileVectorStore;

/**
 * File vector store with metadata filtering applied before selecting top-K.
 *
 * FileVectorStore's native similaritySearch() selects its top-K globally. That
 * makes filtering afterwards unreliable: documents for another company can
 * exhaust the candidate list. This method scores only documents in scope.
 */
class ScopedFileVectorStore extends FileVectorStore
{
    /**
     * @param  float[]  $embedding
     * @param  array<string, string|int>  $metadataFilters
     * @return Document[]
     */
    public function similaritySearchByMetadata(array $embedding, array $metadataFilters, int $topK): array
    {
        $topItems = [];

        foreach ($this->getLine($this->getFilePath()) as $line) {
            $stored = json_decode((string) $line, true);

            if (! is_array($stored) || empty($stored['embedding']) || ! $this->matchesMetadata($stored['metadata'] ?? [], $metadataFilters)) {
                continue;
            }

            $topItems[] = [
                'distance' => VectorSimilarity::cosineDistance($embedding, $stored['embedding']),
                'document' => $stored,
            ];

            usort($topItems, fn (array $left, array $right): int => $left['distance'] <=> $right['distance']);
            if (count($topItems) > $topK) {
                array_pop($topItems);
            }
        }

        return array_map(function (array $item): Document {
            $stored = $item['document'];
            $document = new Document($stored['content']);
            $document->embedding = $stored['embedding'];
            $document->sourceType = $stored['sourceType'];
            $document->sourceName = $stored['sourceName'];
            $document->id = $stored['id'];
            $document->score = VectorSimilarity::similarityFromDistance($item['distance']);
            $document->metadata = $stored['metadata'] ?? [];

            // dd($document);

            return $document;
        }, $topItems);
    }

    /** @param array<string, mixed> $metadata @param array<string, string|int> $filters */
    private function matchesMetadata(array $metadata, array $filters): bool
    {
        foreach ($filters as $key => $value) {
            if (! array_key_exists($key, $metadata) || (string) $metadata[$key] !== (string) $value) {
                return false;
            }
        }

        return true;
    }
}
