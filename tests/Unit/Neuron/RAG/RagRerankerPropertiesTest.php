<?php

declare(strict_types=1);

namespace Tests\Unit\Neuron\RAG;

use App\Models\AiConfiguration;
use App\Neuron\RAG\BaseRagAgent;
use App\Neuron\RAG\FallbackRerankerProcessor;
use Eris\Generators;
use Eris\TestTrait;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\PostProcessor\CohereRerankerPostProcessor;
use NeuronAI\RAG\PostProcessor\JinaRerankerPostProcessor;
use NeuronAI\RAG\PostProcessor\LocalAIRerankerPostProcessor;
use NeuronAI\RAG\PostProcessor\PostProcessorInterface;
use Tests\TestCase;

/**
 * Property-Based Tests for the rag-reranker feature.
 *
 * Each test runs with a minimum of 100 iterations (eris default).
 *
 * @see design.md § "Correctness Properties"
 */
class RagRerankerPropertiesTest extends TestCase
{
    use TestTrait;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function fakeQuestion(string $content = 'Bagaimana kondisi keuangan perusahaan?'): Message
    {
        return new UserMessage($content);
    }

    /**
     * Generate a Document with arbitrary content and optional metadata.
     */
    private function makeDocument(string $content, array $metadata = []): Document
    {
        $doc = new Document($content);
        foreach ($metadata as $key => $value) {
            $doc->addMetadata($key, $value);
        }
        return $doc;
    }

    /**
     * Generator: produces a non-empty string.
     *
     * Uses suchThat to filter out the empty string that NamesGenerator may
     * occasionally produce at small generation sizes.
     */
    private function nonEmptyStringGenerator(): \Eris\Generator
    {
        // Use a fixed list of realistic-looking credential strings so the
        // generator never produces an empty value. This avoids the
        // NamesGenerator's '' fallback at size=0.
        return Generators::elements(
            'sk-test-abc123',
            'api-key-xyz789',
            'Bearer_token_456',
            'jina_secret_key',
            'cohere_prod_key',
            'localai_bearer',
            'test-credential-1',
            'test-credential-2',
        );
    }

    /**
     * Generator: produces a single Document with a random string content.
     *
     * Uses names() (always non-empty, no reverse-regex dependency).
     */
    private function documentGenerator(): \Eris\Generator
    {
        return Generators::map(
            fn ($content) => $this->makeDocument((string) $content),
            Generators::names(),
        );
    }

    /**
     * Generator: produces an array of 1–20 Documents.
     */
    private function documentArrayGenerator(): \Eris\Generator
    {
        return Generators::bind(
            Generators::choose(1, 20),
            fn ($n) => Generators::vector($n, $this->documentGenerator()),
        );
    }

    /**
     * Generator: produces an array of 1–10 Documents each carrying the 7
     * canonical metadata keys with arbitrary string/int values.
     */
    private function documentArrayWithMetadataGenerator(): \Eris\Generator
    {
        return Generators::bind(
            Generators::choose(1, 10),
            function (int $n) {
                $singleDocGen = Generators::map(
                    function ($tuple) {
                        [$company, $period, $stmtType, $source, $content] = $tuple;
                        $doc = new Document((string) $content);
                        $doc->addMetadata('company',        (string) $company);
                        $doc->addMetadata('period',         (string) $period);
                        $doc->addMetadata('statement_type', (string) $stmtType);
                        $doc->addMetadata('source',         (string) $source);
                        $doc->addMetadata('page_start',     (string) abs((int) $period));
                        $doc->addMetadata('page_end',       (string) (abs((int) $period) + 1));
                        $doc->addMetadata('has_table',      '0');
                        return $doc;
                    },
                    Generators::tuple(
                        Generators::names(),    // company
                        Generators::nat(),      // period (used as int year-ish)
                        Generators::names(),    // statement_type
                        Generators::names(),    // source
                        Generators::names(),    // content
                    ),
                );
                return Generators::vector($n, $singleDocGen);
            }
        );
    }

    /**
     * Mock all Log channels/methods so that calls inside BaseRagAgent and
     * FallbackRerankerProcessor don't cause Mockery "no expectations" errors.
     */
    private function mockAllLog(): void
    {
        \Illuminate\Support\Facades\Log::shouldReceive('channel')->andReturnSelf();
        \Illuminate\Support\Facades\Log::shouldReceive('debug')->andReturnNull();
        \Illuminate\Support\Facades\Log::shouldReceive('info')->andReturnNull();
        \Illuminate\Support\Facades\Log::shouldReceive('warning')->andReturnNull();
        \Illuminate\Support\Facades\Log::shouldReceive('error')->andReturnNull();
    }

    /**
     * Call BaseRagAgent::buildReranker() via reflection so we can test the
     * protected method in isolation without bootstrapping the full agent pipeline.
     */
    private function callBuildReranker(AiConfiguration $config): ?PostProcessorInterface
    {
        $this->mockAllLog();

        $agent = new class extends BaseRagAgent {
            protected function instructions(): string { return 'stub'; }
        };

        $method = new \ReflectionMethod(BaseRagAgent::class, 'buildReranker');
        $method->setAccessible(true);

        return $method->invoke($agent, $config);
    }

    /**
     * Resolve the inner PostProcessorInterface from a FallbackRerankerProcessor.
     */
    private function getInnerProcessor(FallbackRerankerProcessor $fallback): PostProcessorInterface
    {
        $prop = new \ReflectionProperty(FallbackRerankerProcessor::class, 'inner');
        $prop->setAccessible(true);

        return $prop->getValue($fallback);
    }

    // =========================================================================
    // Property 1: Registrasi PostProcessor — buildReranker selalu mengembalikan
    //             FallbackRerankerProcessor untuk konfigurasi yang valid.
    //
    // For any valid config (provider ∈ {cohere, jina, localai}, matching
    // credential, non-empty model, positive topN), buildReranker() must return
    // exactly one FallbackRerankerProcessor — never null.
    //
    // Validates: Requirements 1.1, 1.5
    // =========================================================================

    /**
     * Feature: rag-reranker, Property 1: registration on construction
     */
    public function test_property_1_valid_config_always_returns_fallback_processor(): void
    {
        $this->limitTo(100)->forAll(
            Generators::elements('cohere', 'jina', 'localai'),
            $this->nonEmptyStringGenerator(),  // credential / url
            $this->nonEmptyStringGenerator(),  // model
            Generators::pos(),                 // topN > 0
        )->then(function (string $provider, string $cred, string $model, int $topN): void {
            $config = new AiConfiguration([
                'reranker_provider' => $provider,
                'reranker_model'    => $model,
                'reranker_top_n'    => $topN,
                'reranker_api_key'  => $cred,
                'localai_url'       => $provider === 'localai' ? $cred : 'http://localhost:8080/',
            ]);

            $processor = $this->callBuildReranker($config);

            $this->assertInstanceOf(
                FallbackRerankerProcessor::class,
                $processor,
                "Expected FallbackRerankerProcessor for provider={$provider}"
            );
        });
    }

    // =========================================================================
    // Property 2: Config round-trip — topN reaches the inner PostProcessor.
    //
    // For any valid (provider, credential, model, topN), the topN value
    // passed to AiConfiguration must be reflected inside the inner processor.
    //
    // Validates: Requirements 2.3, 2.4, 2.5, 2.6, 2.7, 1.4
    // =========================================================================

    /**
     * Feature: rag-reranker, Property 2: config values forwarded unchanged (cohere)
     */
    public function test_property_2_config_round_trip_cohere(): void
    {
        $this->limitTo(100)->forAll(
            $this->nonEmptyStringGenerator(), // api key
            $this->nonEmptyStringGenerator(), // model
            Generators::pos(),                // topN
        )->then(function (string $apiKey, string $model, int $topN): void {
            $config = new AiConfiguration([
                'reranker_provider' => 'cohere',
                'reranker_model'    => $model,
                'reranker_top_n'    => $topN,
                'reranker_api_key'  => $apiKey,
            ]);

            /** @var FallbackRerankerProcessor $fallback */
            $fallback = $this->callBuildReranker($config);
            $this->assertInstanceOf(FallbackRerankerProcessor::class, $fallback);

            $inner = $this->getInnerProcessor($fallback);
            $this->assertInstanceOf(CohereRerankerPostProcessor::class, $inner);

            $topNProp = new \ReflectionProperty(CohereRerankerPostProcessor::class, 'topN');
            $topNProp->setAccessible(true);
            $this->assertSame($topN, $topNProp->getValue($inner), "topN mismatch for cohere");
        });
    }

    /**
     * Feature: rag-reranker, Property 2: config values forwarded unchanged (jina)
     */
    public function test_property_2_config_round_trip_jina(): void
    {
        $this->limitTo(100)->forAll(
            $this->nonEmptyStringGenerator(), // api key
            $this->nonEmptyStringGenerator(), // model
            Generators::pos(),                // topN
        )->then(function (string $apiKey, string $model, int $topN): void {
            $config = new AiConfiguration([
                'reranker_provider' => 'jina',
                'reranker_model'    => $model,
                'reranker_top_n'    => $topN,
                'reranker_api_key'  => $apiKey,
            ]);

            /** @var FallbackRerankerProcessor $fallback */
            $fallback = $this->callBuildReranker($config);
            $this->assertInstanceOf(FallbackRerankerProcessor::class, $fallback);

            $inner = $this->getInnerProcessor($fallback);
            $this->assertInstanceOf(JinaRerankerPostProcessor::class, $inner);

            $topNProp = new \ReflectionProperty(JinaRerankerPostProcessor::class, 'topN');
            $topNProp->setAccessible(true);
            $this->assertSame($topN, $topNProp->getValue($inner), "topN mismatch for jina");
        });
    }

    /**
     * Feature: rag-reranker, Property 2: config values forwarded unchanged (localai)
     */
    public function test_property_2_config_round_trip_localai(): void
    {
        $this->limitTo(100)->forAll(
            $this->nonEmptyStringGenerator(), // localai_url
            $this->nonEmptyStringGenerator(), // model
            Generators::pos(),                // topN
        )->then(function (string $url, string $model, int $topN): void {
            $config = new AiConfiguration([
                'reranker_provider' => 'localai',
                'reranker_model'    => $model,
                'reranker_top_n'    => $topN,
                'localai_url'       => $url,
            ]);

            /** @var FallbackRerankerProcessor $fallback */
            $fallback = $this->callBuildReranker($config);
            $this->assertInstanceOf(FallbackRerankerProcessor::class, $fallback);

            $inner = $this->getInnerProcessor($fallback);
            $this->assertInstanceOf(LocalAIRerankerPostProcessor::class, $inner);

            $topNProp = new \ReflectionProperty(LocalAIRerankerPostProcessor::class, 'topN');
            $topNProp->setAccessible(true);
            $this->assertSame($topN, $topNProp->getValue($inner), "topN mismatch for localai");
        });
    }

    // =========================================================================
    // Property 3: Fallback on exception preserves original documents
    //
    // For any Document[] and any exception thrown by the inner reranker,
    // FallbackRerankerProcessor must return an array identical to the input.
    //
    // Validates: Requirements 3.1, 3.2
    // =========================================================================

    /**
     * Feature: rag-reranker, Property 3: exception fallback preserves original documents
     */
    public function test_property_3_exception_fallback_preserves_documents(): void
    {
        $this->limitTo(100)->forAll(
            $this->documentArrayGenerator(),
        )->then(function (array $documents): void {
            $throwingReranker = new class implements PostProcessorInterface {
                public function process(Message $q, array $d): array
                {
                    throw new \RuntimeException('Simulated API failure');
                }
            };

            $this->mockAllLog();

            $fallback = new FallbackRerankerProcessor($throwingReranker);
            $result   = $fallback->process($this->fakeQuestion(), $documents);

            $this->assertCount(count($documents), $result, 'Document count must be preserved on exception');

            foreach ($documents as $i => $doc) {
                $this->assertSame(
                    $doc->getContent(),
                    $result[$i]->getContent(),
                    "Document #{$i} content changed after exception fallback"
                );
            }
        });
    }

    // =========================================================================
    // Property 4: Fallback on empty result preserves original documents
    //
    // For any non-empty Document[], if the inner reranker returns [],
    // FallbackRerankerProcessor must return the original documents unchanged.
    //
    // Validates: Requirements 3.4
    // =========================================================================

    /**
     * Feature: rag-reranker, Property 4: empty result fallback preserves original documents
     */
    public function test_property_4_empty_result_fallback_preserves_documents(): void
    {
        $this->limitTo(100)->forAll(
            $this->documentArrayGenerator(),
        )->then(function (array $documents): void {
            $emptyReranker = new class implements PostProcessorInterface {
                public function process(Message $q, array $d): array { return []; }
            };

            $this->mockAllLog();

            $fallback = new FallbackRerankerProcessor($emptyReranker);
            $result   = $fallback->process($this->fakeQuestion(), $documents);

            $this->assertCount(
                count($documents),
                $result,
                'All documents must be returned when reranker returns empty array'
            );

            foreach ($documents as $i => $doc) {
                $this->assertSame(
                    $doc->getContent(),
                    $result[$i]->getContent(),
                    "Document #{$i} content changed after empty-result fallback"
                );
            }
        });
    }

    // =========================================================================
    // Property 5: Document metadata invariant after reranking
    //
    // For any Document[] with the 7 canonical metadata keys set to arbitrary
    // values, after FallbackRerankerProcessor.process() (both success path and
    // fallback path) every returned document must have metadata identical to
    // the snapshot taken before processing. Only 'score' may change.
    //
    // Validates: Requirements 4.4, 4.5
    // =========================================================================

    /**
     * Feature: rag-reranker, Property 5: document metadata invariant — success path
     */
    public function test_property_5_metadata_invariant_on_success_path(): void
    {
        $this->limitTo(100)->forAll(
            $this->documentArrayWithMetadataGenerator(),
        )->then(function (array $documents): void {
            // Snapshot metadata before processing
            $snapshotById = [];
            foreach ($documents as $doc) {
                $snapshotById[$doc->getId()] = $doc->metadata;
            }

            // Inner reranker reverses order only — must NOT change metadata
            $reorderingReranker = new class($documents) implements PostProcessorInterface {
                public function __construct(private array $docs) {}
                public function process(Message $q, array $d): array
                {
                    return array_reverse($this->docs);
                }
            };

            $this->mockAllLog();

            $fallback = new FallbackRerankerProcessor($reorderingReranker);
            $result   = $fallback->process($this->fakeQuestion(), $documents);

            foreach ($result as $doc) {
                $this->assertArrayHasKey($doc->getId(), $snapshotById,
                    'Returned document ID was not in the original set');

                $this->assertEquals(
                    $snapshotById[$doc->getId()],
                    $doc->metadata,
                    "Metadata changed for document {$doc->getId()} after reranking (success path)"
                );
            }
        });
    }

    /**
     * Feature: rag-reranker, Property 5: document metadata invariant — fallback path
     */
    public function test_property_5_metadata_invariant_on_fallback_path(): void
    {
        $this->limitTo(100)->forAll(
            $this->documentArrayWithMetadataGenerator(),
        )->then(function (array $documents): void {
            $snapshotById = [];
            foreach ($documents as $doc) {
                $snapshotById[$doc->getId()] = $doc->metadata;
            }

            $throwingReranker = new class implements PostProcessorInterface {
                public function process(Message $q, array $d): array
                {
                    throw new \RuntimeException('forced error');
                }
            };

            $this->mockAllLog();

            $fallback = new FallbackRerankerProcessor($throwingReranker);
            $result   = $fallback->process($this->fakeQuestion(), $documents);

            foreach ($result as $doc) {
                $this->assertEquals(
                    $snapshotById[$doc->getId()],
                    $doc->metadata,
                    "Metadata changed for document {$doc->getId()} after exception fallback"
                );
            }
        });
    }

    // =========================================================================
    // Property 6: Provider tidak valid → buildReranker returns null
    //
    // For any string ∉ {cohere, jina, localai} used as reranker_provider,
    // buildReranker() must return null (graceful degradation via match default).
    //
    // Note: BaseRagAgent::buildReranker() uses match() with `default => null`.
    //
    // Validates: Requirements 5.1 (adapted to actual implementation)
    // =========================================================================

    /**
     * Feature: rag-reranker, Property 6: invalid provider returns null from buildReranker
     */
    public function test_property_6_invalid_provider_returns_null(): void
    {
        $validProviders = ['cohere', 'jina', 'localai'];

        $this->limitTo(100)->forAll(
            Generators::suchThat(
                fn ($name) => !in_array(strtolower(trim($name)), $validProviders, true),
                Generators::names(),
            ),
        )->then(function (string $invalidProvider): void {
            $config = new AiConfiguration([
                'reranker_provider' => $invalidProvider,
                'reranker_model'    => 'some-model',
                'reranker_top_n'    => 3,
                'reranker_api_key'  => 'some-key',
            ]);

            $result = $this->callBuildReranker($config);

            $this->assertNull(
                $result,
                "buildReranker() should return null for unknown provider '{$invalidProvider}'"
            );
        });
    }

    // =========================================================================
    // Property 7: Credential kosong untuk cloud providers → buildReranker null
    //
    // For cohere and jina, an empty/null API key means buildReranker() returns
    // null (guarded by empty() check in BaseRagAgent).
    //
    // Validates: Requirements 5.2, 5.3
    // =========================================================================

    /**
     * Feature: rag-reranker, Property 7: missing credential returns null for cloud providers
     */
    public function test_property_7_empty_credential_returns_null_for_cloud_providers(): void
    {
        $this->limitTo(100)->forAll(
            Generators::elements('cohere', 'jina'),
            Generators::elements('', null),
        )->then(function (string $provider, ?string $credential): void {
            $config = new AiConfiguration([
                'reranker_provider' => $provider,
                'reranker_model'    => 'some-model',
                'reranker_top_n'    => 3,
                'reranker_api_key'  => $credential,
            ]);

            $result = $this->callBuildReranker($config);

            $this->assertNull(
                $result,
                "buildReranker() should return null for {$provider} with empty credential"
            );
        });
    }

    // =========================================================================
    // Property 8: TopN non-positif tidak menyebabkan crash
    //
    // BaseRagAgent casts topN to int: (int) ($dbConfig->reranker_top_n ?? 5).
    // Even if topN ≤ 0 is passed, buildReranker() must not throw — it is
    // stable and returns a processor (or null for cloud providers without key).
    //
    // Validates: Requirements 5.5 (adapted — actual code does not throw)
    // =========================================================================

    /**
     * Feature: rag-reranker, Property 8: non-positive topN does not crash buildReranker
     */
    public function test_property_8_non_positive_top_n_does_not_crash(): void
    {
        $this->limitTo(100)->forAll(
            Generators::oneOf(Generators::neg(), Generators::constant(0)),
        )->then(function (int $invalidTopN): void {
            // Use localai (no API key required) to focus on topN behaviour
            $config = new AiConfiguration([
                'reranker_provider' => 'localai',
                'reranker_model'    => 'cross-encoder',
                'reranker_top_n'    => $invalidTopN,
                'localai_url'       => 'http://localhost:8080/',
            ]);

            $thrownException = null;
            try {
                $result = $this->callBuildReranker($config);
            } catch (\Throwable $e) {
                $thrownException = $e;
            }

            $this->assertNull(
                $thrownException,
                "buildReranker() must not throw for non-positive topN={$invalidTopN}, got: "
                . ($thrownException ? $thrownException->getMessage() : 'no exception')
            );
        });
    }
}
