<?php

namespace App\Neuron\DataLoader;

use App\Neuron\RAG\IndexerAgent; // Gunakan Agent yang baru dibuat
use NeuronAI\RAG\DataLoader\StringDataLoader;
use NeuronAI\RAG\Splitter\SentenceTextSplitter;
use NeuronAI\RAG\Document;

class DataLoader
{
    private const METADATA_KEYS = [
        'company',
        'period',
        'statement_type',
        'source',
        'found_page',
        'found_at',
    ];

    public static function embedChunks(array $chunks): int
    {
        $allDocuments = [];

        foreach ($chunks as $chunk) {
            $text     = $chunk['text']     ?? '';
            $metadata = $chunk['metadata'] ?? [];

            if (trim($text) === '') {
                continue;
            }

            $documents = StringDataLoader::for($text)
                ->withSplitter(new SentenceTextSplitter(maxWords: 10000))// Biar chunk nya gak ke-split
                ->getDocuments();

            foreach ($documents as $document) {
                self::attachMetadata($document, $metadata);
                $allDocuments[] = $document;
            }
        }

        if (empty($allDocuments)) {
            return 0;
        }

        IndexerAgent::make()->addDocuments($allDocuments);

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
