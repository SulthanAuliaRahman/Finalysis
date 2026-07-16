<?php

namespace App\Neuron\RAG;

class IndexerAgent extends BaseRagAgent
{
    // Untuk Data Loading  Chunk -> Vector Embedding sudah ada dalam Neuron jadi digunakan itu
    // disini yang belum ada saja

    public function deleteBySource(string $sourceType, string $sourceName): void
    {
        $this->vectorStore()->deleteBySource(sourceType: $sourceType, sourceName: $sourceName);
    }
}
