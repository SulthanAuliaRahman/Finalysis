<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Dokumen;
use App\Neuron\DataLoader\DataLoader;
use Illuminate\Console\Command;

class ReindexRagDocuments extends Command
{
    protected $signature = 'rag:reindex {--document= : Reindex only one document ID}';

    protected $description = 'Reindex document chunks with company-scoped RAG metadata.';

    public function handle(): int
    {
        $documents = Dokumen::query()
            ->when($this->option('document'), fn ($query, $id) => $query->whereKey($id))
            ->with('perusahaan:id,nama')
            ->orderBy('id')
            ->get();

        if ($documents->isEmpty()) {
            $this->warn('No documents matched the requested scope.');

            return self::SUCCESS;
        }

        foreach ($documents as $document) {
$chunks = $document->chunks()
    ->orderBy('chunk_index')
    ->get(['text', 'metadata'])
    ->map(fn ($chunk): array => [
        'text' => $chunk->text,
        'metadata' => $chunk->metadata ?: [],
    ])
    ->all();

            if ($chunks === []) {
                $this->warn("Document {$document->id} has no chunks; skipped.");

                continue;
            }

            $count = DataLoader::embedChunks($chunks, [
                'company_id' => $document->perusahaan_id,
                'document_id' => $document->id,
                'company' => $document->perusahaan->nama,
                'period' => (string) $document->periode,
            ]);

            $this->info("Document {$document->id}: {$count} chunks reindexed.");
        }

        return self::SUCCESS;
    }
}
