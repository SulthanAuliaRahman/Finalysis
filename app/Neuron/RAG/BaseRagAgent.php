<?php

namespace App\Neuron\RAG;

use NeuronAI\RAG\RAG;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\VectorStore\ChromaVectorStore;

use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\RAG\Embeddings\OllamaEmbeddingsProvider;

use NeuronAI\RAG\PostProcessor\PostProcessorInterface;
use NeuronAI\RAG\PostProcessor\CohereRerankerPostProcessor;
use NeuronAI\RAG\PostProcessor\JinaRerankerPostProcessor;
use NeuronAI\RAG\PostProcessor\LocalAIRerankerPostProcessor;

use App\Services\AiConfigurationService;
use App\Neuron\RAG\FallbackRerankerProcessor;
use App\Neuron\Providers\ConfigurableGemini;
use App\Neuron\Providers\ConfigurableGeminiEmbeddingsProvider;
use App\Neuron\Providers\ConfigurableOpenAI;
use App\Neuron\Providers\ConfigurableOpenAIEmbeddingsProvider;
use App\Models\AiConfiguration;
use Illuminate\Support\Facades\Log;
use NeuronAI\Chat\Messages\UserMessage;

use NeuronAI\HttpClient\GuzzleHttpClient;

abstract class BaseRagAgent extends RAG
{
    /** @var \NeuronAI\RAG\Document[] */
    private array $lastRetrievedReferences = [];
    protected string $providerName;
    protected string $apiKey;
    protected string $aiModel;

    protected string $embeddingProviderName;
    protected string $embeddingApiKey;
    protected string $embeddingModel;
    protected ?AiConfiguration $dbConfig = null;
    public function __construct(
        ?string $providerName = null,
        ?string $apiKey = null,
        ?string $aiModel = null,
        ?string $embeddingProviderName = null,
        ?string $embeddingApiKey = null,
        ?string $embeddingModel = null
    ) {
        try {
            $this->dbConfig = app(AiConfigurationService::class)->get();
        } catch (\Throwable $e) {
            Log::debug('[rag-config] Database configurations not loaded, using defaults/env.', [
                'exception' => $e->getMessage()
            ]);
        }
        $this->providerName = strtolower($providerName ?? ($this->dbConfig?->llm_provider ?? 'ollama'));
        $this->apiKey = $apiKey ?? ($this->dbConfig?->llm_api_key ?? '');
        $this->aiModel = $aiModel ?? ($this->dbConfig?->llm_model ?? 'qwen2.5:3b');

        $this->embeddingProviderName = strtolower($embeddingProviderName ?? ($this->dbConfig?->embedding_provider ?? 'ollama'));
        $this->embeddingApiKey = $embeddingApiKey ?? ($this->dbConfig?->embedding_api_key ?? $this->apiKey ?? '');
        $this->embeddingModel = $embeddingModel ?? ($this->dbConfig?->embedding_model ?? 'qwen3-embedding:4b');

        parent::__construct();
        $postProcessors = [];

        // Register Reranker dynamically if configured in database
        if ($this->dbConfig && filled($this->dbConfig->reranker_provider) && strtolower($this->dbConfig->reranker_provider) !== 'none') {
            $reranker = $this->buildReranker($this->dbConfig);
            if ($reranker) {
                $postProcessors[] = $reranker;
            }
        } else {
            Log::info('[rag-reranker] Reranker is disabled or not configured in database settings.');
        }

        if (filled($this->dbConfig?->system_prompt)) {
            $this->setInstructions($this->instructions()."\n\nKonfigurasi tambahan sistem:\n".$this->dbConfig->system_prompt);
        }

        // This runs after the reranker and captures the exact chunks injected
        // into the AI context for the current chat request.
        $postProcessors[] = new CaptureRetrievedDocumentsProcessor(function (array $documents, \NeuronAI\Chat\Messages\Message $question): void {
            $this->lastRetrievedReferences = $documents;

            Log::info(sprintf('[RAG Retrieval] Agent %s retrieved %d document(s) for query: "%s"', static::class, count($documents), $question->getContent()), [
                'agent' => static::class,
                'query' => $question->getContent(),
                'documents_count' => count($documents),
                'documents' => array_map(fn ($doc) => [
                    'score' => $doc->getScore(),
                    'metadata' => $doc->metadata,
                    'content_preview' => mb_strimwidth($doc->getContent(), 0, 200, '...'),
                ], $documents),
            ]);
        });

        $this->setPostProcessors($postProcessors);
    }

    protected function buildReranker(AiConfiguration $dbConfig): ?PostProcessorInterface
    {
        $provider   = strtolower((string) $dbConfig->reranker_provider);
        $apiKey     = $dbConfig->reranker_api_key;
        $model      = $dbConfig->reranker_model;
        $topN       = (int) ($dbConfig->reranker_top_n ?? 5);
        $localaiUrl = $dbConfig->localai_url ?? 'http://localhost:8080/';

        // Check if API key is empty for cloud providers (Cohere, Jina)
        if (in_array($provider, ['cohere', 'jina']) && empty($apiKey)) {
            Log::info('[rag-reranker] Reranker initialization skipped: API Key is empty.', [
                'provider' => $provider,
            ]);
            return null;
        }

        try {
            $innerReranker = match ($provider) {
                'cohere' => new CohereRerankerPostProcessor(
                    key: $apiKey,
                    model: $model ?: 'rerank-v3.5',
                    topN: $topN
                ),
                'jina' => new JinaRerankerPostProcessor(
                    key: $apiKey,
                    model: $model ?: 'jina-reranker-v2-base-multilingual',
                    topN: $topN
                ),
                'localai' => new LocalAIRerankerPostProcessor(
                    key: $apiKey ?? '',
                    model: $model ?: 'cross-encoder',
                    topN: $topN,
                    host: $localaiUrl
                ),
                default => null,
            };

            if ($innerReranker) {
                Log::info('[rag-reranker] Reranker initialized successfully.', [
                    'provider' => $provider,
                    'model'    => $model ?: 'default',
                    'top_n'    => $topN,
                ]);
                return new FallbackRerankerProcessor($innerReranker);
            }
        } catch (\Throwable $e) {
            Log::error('[rag-reranker] Failed to build reranker.', [
                'provider'  => $provider,
                'exception' => $e->getMessage()
            ]);
        }

        return null;
    }

    protected function provider(): AIProviderInterface
    {
        return match ($this->providerName) {
            'gemini' => new ConfigurableGemini(
                key: $this->apiKey,
                model: $this->aiModel,
                baseUrl: $this->dbConfig?->llm_url,
            ),

            'openai', 'localai' => new ConfigurableOpenAI(
                key: $this->apiKey,
                model: $this->aiModel,
                baseUrl: $this->providerName === 'localai'
                    ? ($this->dbConfig?->llm_url ?: $this->dbConfig?->localai_url)
                    : $this->dbConfig?->llm_url,
            ),

            default => new Ollama(
                url: $this->dbConfig?->llm_url ?: 'http://host.docker.internal:11434/api',
                model: $this->aiModel,
                httpClient: new GuzzleHttpClient(timeout: 100)
            ),
        };
    }

    protected function embeddings(): EmbeddingsProviderInterface
    {
        return match ($this->embeddingProviderName) {
            'gemini' => new ConfigurableGeminiEmbeddingsProvider(
                key: $this->embeddingApiKey,
                model: $this->embeddingModel,
                baseUrl: $this->dbConfig?->embedding_url,
            ),

            'openai', 'localai' => new ConfigurableOpenAIEmbeddingsProvider(
                key: $this->embeddingApiKey,
                model: $this->embeddingModel,
                baseUrl: $this->embeddingProviderName === 'localai'
                    ? ($this->dbConfig?->embedding_url ?: $this->dbConfig?->localai_url)
                    : $this->dbConfig?->embedding_url,
            ),

            default => new OllamaEmbeddingsProvider(
                url: $this->dbConfig?->embedding_url ?: 'http://host.docker.internal:11434/api',
                model: $this->embeddingModel,
            ),
        };
    }

    protected function vectorStore(): VectorStoreInterface
    {
        $directory = __DIR__;
        $name = 'demo';

        try {
            $dbConfig = $this->dbConfig ?? app(AiConfigurationService::class)->get();
            if ($dbConfig->vector_store_path) {
                $directory = $dbConfig->vector_store_path;
            }
            if ($dbConfig->vector_store_name) {
                $name = $dbConfig->vector_store_name;
                }
        } catch (\Throwable $e) {
            Log::debug('[rag-config] Failed to resolve vector store settings from database, using defaults.', [
                'exception' => $e->getMessage()
            ]);
        }

        return new ScopedFileVectorStore(
            directory: $directory,
            topK: 12,
            name: $name
        );
    }

    /**
     * Limit retrieval to a metadata scope (currently used to isolate a company).
     * The scope is applied before top-K selection, so unrelated documents cannot
     * displace relevant chunks from the result set.
     *
     * @param array<string, string|int> $metadataFilters
     */
    public function withRetrievalScope(array $metadataFilters): static
    {
        $this->setRetrieval(new MetadataFilteredRetrieval(
            $this->resolveVectorStore(),
            $this->resolveEmbeddingsProvider(),
            $metadataFilters,
        ));

        return $this;
    }

    /**
     * Retrieve the same contextual documents used by the RAG pipeline,
     * including any configured reranker.
     *
     * @return \NeuronAI\RAG\Document[]
     */
    public function retrieveReferences(string $query): array
    {
        $message = new UserMessage($query);
        $documents = $this->resolveRetrieval()->retrieve($message);

        foreach ($this->postProcessors() as $processor) {
            $documents = $processor->process($message, $documents);
        }

        return $documents;
    }

    /** @return \NeuronAI\RAG\Document[] */
    public function lastRetrievedReferences(): array
    {
        return $this->lastRetrievedReferences;
    }
}
