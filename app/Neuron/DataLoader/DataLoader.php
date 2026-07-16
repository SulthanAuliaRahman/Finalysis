<?php

namespace App\Neuron\DataLoader;

use App\Neuron\RAG\IndexerAgent; // Gunakan Agent yang baru dibuat
use NeuronAI\RAG\DataLoader\StringDataLoader;
use NeuronAI\RAG\Splitter\SentenceTextSplitter;
use NeuronAI\RAG\Document;

class DataLoader
{
    private const METADATA_KEYS = [
        'company_id',
        'document_id',
        'company',
        'period',
        'statement_type',
        'source',
        'chunk_index',
    ];

    public static function embedChunks(array $chunks, array $documentMetadata = []): int
    {
        $allDocuments = [];

        foreach ($chunks as $chunk) {
            $text     = $chunk['text']     ?? '';
            $metadata = array_merge($chunk['metadata'] ?? [], $documentMetadata);

            if (trim($text) === '') {
                continue;
            }

            $documents = StringDataLoader::for($text)
                ->withSplitter(new SentenceTextSplitter(maxWords: 10000))// Biar chunk nya gak ke-split
                ->getDocuments();

            foreach ($documents as $document) {
                self::attachMetadata($document, $metadata);
                $document->sourceType = 'document';
                $document->sourceName = (string) ($metadata['document_id'] ?? 'unknown');
                $allDocuments[] = $document;
            }
        }

        if (empty($allDocuments)) {
            return 0;
        }

        // Re-embedding the same document must replace its previous vectors,
        // otherwise duplicate chunks bias retrieval results.
        IndexerAgent::make()->reindexBySource($allDocuments);

        return count($allDocuments);
    }


    private static function attachMetadata(Document $document, array $metadata): void
    {
        foreach (self::METADATA_KEYS as $key) {
            if (array_key_exists($key, $metadata)) {
                $document->addMetadata($key, $metadata[$key]);
            }
        }
    }
}
