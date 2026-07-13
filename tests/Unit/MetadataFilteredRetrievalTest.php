<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Neuron\RAG\MetadataFilteredRetrieval;
use Mockery;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use Tests\TestCase;

class MetadataFilteredRetrievalTest extends TestCase
{
    public function test_it_returns_only_documents_in_the_requested_company_scope(): void
    {
        $allowed = new Document('Konteks perusahaan yang benar');
        $allowed->addMetadata('company_id', 10);

        $otherCompany = new Document('Konteks perusahaan lain');
        $otherCompany->addMetadata('company_id', 99);

        $embeddingProvider = Mockery::mock(EmbeddingsProviderInterface::class);
        $embeddingProvider->shouldReceive('embedText')->once()->andReturn([0.1, 0.2]);

        $vectorStore = Mockery::mock(VectorStoreInterface::class);
        $vectorStore->shouldReceive('similaritySearch')->once()->with([0.1, 0.2])->andReturn([$allowed, $otherCompany]);

        $retrieval = new MetadataFilteredRetrieval($vectorStore, $embeddingProvider, ['company_id' => 10]);

        $documents = $retrieval->retrieve(new UserMessage('risiko likuiditas'));

        $this->assertSame([$allowed], $documents);
    }
}
