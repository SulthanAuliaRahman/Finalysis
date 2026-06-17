<?php

namespace App\Neuron\DataLoader;

use App\Neuron\RAG\RagAgent;
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
        'page_start',
        'page_end',
        'has_table',
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

            // StringDataLoader bisa menghasilkan >1 Document jika teks
            $documents = StringDataLoader::for($text)->withSplitter(new SentenceTextSplitter(maxWords:10000))->getDocuments(); // biar chunk nya gak ke split

            foreach ($documents as $document) {
                self::attachMetadata($document, $metadata);
                $allDocuments[] = $document;
            }
        }

        if (empty($allDocuments)) {
            return 0;
        }

        // RagAgent::addDocuments() menghasilkan embedding untuk setiap Document lalu menyimpannya ke FileVectorStore (lihat RagAgent.php).
        RagAgent::make()->addDocuments($allDocuments);

        return count($allDocuments);
    }

    /**
     * Attach subset metadata chunk ke Document.
     * Field yang tidak ada di $metadata akan diabaikan (tidak di-set null).
     */
    private static function attachMetadata(Document $document, array $metadata): void
    {
        foreach (self::METADATA_KEYS as $key) {
            if (array_key_exists($key, $metadata)) {
                $document->addMetadata($key, $metadata[$key]);
            }
        }
    }
}
