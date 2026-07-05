<?php

declare(strict_types=1);

namespace Tests\Unit\Neuron\RAG;

use App\Neuron\RAG\FallbackRerankerProcessor;
use App\Neuron\RAG\RagAgent;
use Eris\Generator;
use Eris\Generators;
use Eris\TestTrait;
use NeuronAI\Chat\Enums\MessageRole;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\PostProcessor\PostProcessorInterface;
use Tests\TestCase;

/**
 * TestableRagAgentForProperties exposes the protected postProcessors() method
 * as public so it can be asserted on in property-based tests.
 * A separate class from TestableRagAgent in RagAgentTest.php to avoid
 * class redeclaration conflicts when the two test files are loaded together.
 */
class TestableRagAgentForProperties extends RagAgent
{
    public function postProcessors(): array
    {
        return parent::postProcessors();
    }
}

class RagRerankerPropertiesTest extends TestCase
{
    use TestTrait;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Returns a simple USER Message instance used as the question argument
     * throughout all property tests.
     */
    private function fakeQuestion(): Message
    {
        return new Message(MessageRole::USER, 'test query');
    }

    /**
     * Returns an Eris Generator that produces Document instances with
     * arbitrary string content and arbitrary string metadata values.
     *
     * Uses Generators::map() to wrap Generators::string() and construct
     * Document objects inside the generator pipeline.
     *
     * @return \Eris\Generator
     */
    private function documentGenerator(): \Eris\Generator
    {
        return Generators::map(
            function (string $content): Document {
                $doc = new Document($content);
                return $doc;
            },
            Generators::string()
        );
    }

    /**
     * Returns an Eris Generator that produces Document instances with
     * all seven standard RAG metadata keys populated with arbitrary strings.
     *
     * @return \Eris\Generator
     */
    private function documentWithMetadataGenerator(): \Eris\Generator
    {
        return Generators::map(
            function (array $tuple): Document {
                [$content, $company, $period, $statementType, $source, $pageStart, $pageEnd, $hasTable] = $tuple;

                $doc = new Document($content);
                $doc->addMetadata('company',        $company);
                $doc->addMetadata('period',         $period);
                $doc->addMetadata('statement_type', $statementType);
                $doc->addMetadata('source',         $source);
                $doc->addMetadata('page_start',     (int) abs($pageStart));
                $doc->addMetadata('page_end',       (int) abs($pageEnd) + (int) abs($pageStart) + 1);
                $doc->addMetadata('has_table',      $hasTable ? 1 : 0);

                return $doc;
            },
            Generators::tuple(
                Generators::string(),                   // content
                Generators::string(),                   // company
                Generators::string(),                   // period
                Generators::string(),                   // statement_type
                Generators::string(),                   // source
                Generators::nat(),                      // page_start (non-negative int)
                Generators::nat(),                      // page_end offset
                Generators::bool()                      // has_table
            )
        );
    }

    /**
     * Override the services.reranker config for a single property run.
     */
    private function setRerankerConfig(array $overrides): void
    {
        config(['services.reranker' => array_merge([
            'provider'       => 'localai',
            'model'          => 'cross-encoder',
            'top_n'          => 3,
            'localai_url'    => 'http://localhost:8080/',
            'cohere_api_key' => null,
            'jina_api_key'   => null,
        ], $overrides)]);
    }

    /**
     * Use PHP Reflection to read a private/readonly property from an object.
     */
    private function getPrivate(object $object, string $property): mixed
    {
        $ref = new \ReflectionProperty($object, $property);
        $ref->setAccessible(true);
        return $ref->getValue($object);
    }

    // -------------------------------------------------------------------------
    // Property 1: Registrasi PostProcessor saat instansiasi
    // -------------------------------------------------------------------------

    /**
     * **Validates: Requirements 1.1, 1.5**
     *
     * @test
     * @group Feature:rag-reranker
     */
    public function property1_postProcessors_contains_exactly_one_FallbackRerankerProcessor_for_any_valid_config(): void

    {
        $this->limitTo(100);
        $this->forAll(
            Generators::elements('cohere', 'jina', 'localai'),
            Generators::suchThat(fn($s) => !empty(trim($s)), Generators::string()),  // non-empty credential
            Generators::suchThat(fn($s) => !empty(trim($s)), Generators::string()),  // non-empty model
            Generators::pos()  // topN > 0
        )
        ->then(function (string $provider, string $cred, string $model, int $topN) {
            $configKey = match ($provider) {
                'cohere'  => 'cohere_api_key',
                'jina'    => 'jina_api_key',
                'localai' => 'localai_url',
            };

            $this->setRerankerConfig([
                'provider'       => $provider,
                'model'          => $model,
                'top_n'          => $topN,
                $configKey       => $cred,
            ]);

            $agent      = new TestableRagAgentForProperties();
            $processors = $agent->postProcessors();

            $this->assertCount(1, $processors);
            $this->assertInstanceOf(FallbackRerankerProcessor::class, $processors[0]);
        });
    }

    // -------------------------------------------------------------------------
    // Property 6: Provider tidak valid selalu melempar InvalidArgumentException
    // -------------------------------------------------------------------------

    /**
     * **Validates: Requirements 5.1**
     *
     * For any string that is NOT one of 'cohere', 'jina', or 'localai',
     * constructing RagAgent always throws InvalidArgumentException.
     *
     * @test
     * @group Feature:rag-reranker
     */
    public function property6_invalid_provider_always_throws_InvalidArgumentException(): void
    {
        $this->limitTo(100);
        $this->forAll(
            Generators::suchThat(
                fn($s) => !in_array($s, ['cohere', 'jina', 'localai'], true),
                Generators::string()
            )
        )
        ->then(function (string $invalidProvider) {
            $this->setRerankerConfig([
                'provider'    => $invalidProvider,
                'model'       => 'some-model',
                'top_n'       => 3,
                'localai_url' => 'http://localhost:8080/',
            ]);

            $this->expectException(\InvalidArgumentException::class);
            new TestableRagAgentForProperties();
        });
    }

    // -------------------------------------------------------------------------
    // Property 3: Fallback exception mempertahankan dokumen asli
    // -------------------------------------------------------------------------

    /**
     * @test
     * @group Feature:rag-reranker
     *
     * Validates: Requirements 3.1, 3.2
     *
     * For any array of 1–20 Documents with arbitrary content, when the inner
     * reranker throws a RuntimeException, FallbackRerankerProcessor returns an
     * array identical to the input (same count, same content for each document).
     */
    public function property3_exception_fallback_preserves_original_documents(): void
    {
        $this->forAll(
            Generators::bind(
                Generators::choose(1, 20),
                fn($n) => Generators::vector($n, $this->documentGenerator())
            )
        )
        ->limitTo(100)
        ->then(function (array $documents) {
            $throwingReranker = new class implements PostProcessorInterface {
                public function process(Message $q, array $d): array {
                    throw new \RuntimeException('Simulated API failure');
                }
            };

            $fallback = new FallbackRerankerProcessor($throwingReranker);
            $result   = $fallback->process($this->fakeQuestion(), $documents);

            $this->assertCount(count($documents), $result);
            foreach ($documents as $i => $doc) {
                $this->assertSame($doc->getContent(), $result[$i]->getContent());
            }
        });
    }

    // -------------------------------------------------------------------------
    // Property 2: Config round-trip ke PostProcessor
    // -------------------------------------------------------------------------

    /**
     * **Validates: Requirements 2.3, 2.4, 2.5, 2.6, 2.7, 1.4**
     *
     * For any valid (provider, credential, model, topN) combination, the values
     * must be forwarded unchanged to the inner PostProcessor constructor — the
     * values read from config() are identical to the values stored on the inner
     * processor instance.
     *
     * @test
     * @group Feature:rag-reranker
     */
    public function property2_config_values_forwarded_unchanged_to_PostProcessor(): void
    {
        $this->limitTo(100);
        $this->forAll(
            Generators::elements('cohere', 'jina', 'localai'),
            Generators::suchThat(fn($s) => !empty(trim($s)), Generators::string()),
            Generators::suchThat(fn($s) => !empty(trim($s)), Generators::string()),
            Generators::pos()
        )
        ->then(function (string $provider, string $cred, string $model, int $topN) {
            $configKey = match ($provider) {
                'cohere'  => 'cohere_api_key',
                'jina'    => 'jina_api_key',
                'localai' => 'localai_url',
            };

            $this->setRerankerConfig([
                'provider' => $provider,
                'model'    => $model,
                'top_n'    => $topN,
                $configKey => $cred,
            ]);

            $agent      = new TestableRagAgentForProperties();
            $processors = $agent->postProcessors();
            $fallback   = $processors[0];

            // Get the inner processor via reflection
            $inner = $this->getPrivate($fallback, 'inner');

            // Verify topN was forwarded correctly
            $innerTopN = $this->getPrivate($inner, 'topN');
            $this->assertSame($topN, $innerTopN);

            // Verify model was forwarded correctly
            $innerModel = $this->getPrivate($inner, 'model');
            $this->assertSame($model, $innerModel);
        });
    }

    // -------------------------------------------------------------------------
    // Property 4: Fallback empty result mempertahankan dokumen asli
    // -------------------------------------------------------------------------

    /**
     * **Validates: Requirements 3.4**
     *
     * For any non-empty array of Documents, when the inner reranker returns an
     * empty array, FallbackRerankerProcessor must return an array identical to
     * the original input — same count and same document instances.
     *
     * @test
     * @group Feature:rag-reranker
     */
    public function property4_empty_result_fallback_preserves_original_documents(): void
    {
        $this->limitTo(100);
        $this->forAll(
            Generators::bind(
                Generators::choose(1, 20),
                fn($n) => Generators::vector($n, $this->documentGenerator())
            )
        )
        ->then(function (array $documents) {
            $emptyReranker = new class implements PostProcessorInterface {
                public function process(Message $q, array $d): array {
                    return [];
                }
            };

            $fallback = new FallbackRerankerProcessor($emptyReranker);
            $result   = $fallback->process($this->fakeQuestion(), $documents);

            $this->assertCount(count($documents), $result);
            $this->assertSame($documents, $result);
        });
    }

    // -------------------------------------------------------------------------
    // Property 5: Metadata dokumen tidak berubah setelah reranking
    // -------------------------------------------------------------------------

    /**
     * **Validates: Requirements 4.4, 4.5**
     *
     * For any array of Documents with arbitrary metadata, after passing through
     * FallbackRerankerProcessor (both success and fallback paths), each returned
     * document must have metadata identical to the snapshot taken before process().
     * Only the score field is allowed to change.
     *
     * Feature: rag-reranker, Property 5: document metadata invariant after reranking
     *
     * @test
     */
    public function property5_document_metadata_invariant_after_reranking(): void
    {
        $this->limitTo(100);
        $this->forAll(
            Generators::choose(1, 20),
            Generators::string(),  // company
            Generators::string(),  // period
            Generators::string(),  // statement_type
            Generators::string()   // source
        )
        ->then(function (int $count, string $company, string $period, string $statementType, string $source) {
            // Build documents with the 7 required metadata keys
            $documents = [];
            for ($i = 0; $i < $count; $i++) {
                $doc = new Document("Content {$i}");
                $doc->addMetadata('company',        $company);
                $doc->addMetadata('period',         $period);
                $doc->addMetadata('statement_type', $statementType);
                $doc->addMetadata('source',         $source);
                $doc->addMetadata('page_start',     $i);
                $doc->addMetadata('page_end',       $i + 1);
                $doc->addMetadata('has_table',      0);
                $documents[] = $doc;
            }

            // Snapshot metadata before processing
            $snapshot = array_map(fn(Document $d) => $d->metadata, $documents);

            // Test success path: inner reranker reverses order but doesn't change metadata
            $reorderingReranker = new class($documents) implements PostProcessorInterface {
                public function __construct(private array $docs) {}
                public function process(Message $q, array $d): array {
                    return array_reverse($this->docs);
                }
            };

            $fallback = new FallbackRerankerProcessor($reorderingReranker);
            $result   = $fallback->process($this->fakeQuestion(), $documents);

            foreach ($result as $doc) {
                // Find the original snapshot by matching content
                $originalMetadata = null;
                foreach ($snapshot as $i => $meta) {
                    if ($documents[$i]->getContent() === $doc->getContent()) {
                        $originalMetadata = $meta;
                        break;
                    }
                }
                if ($originalMetadata !== null) {
                    $this->assertEquals($originalMetadata, $doc->metadata);
                }
            }

            // Test fallback path: throwing reranker → original documents returned, metadata unchanged
            $throwingReranker = new class implements PostProcessorInterface {
                public function process(Message $q, array $d): array {
                    throw new \RuntimeException('Failure');
                }
            };

            $fallback2 = new FallbackRerankerProcessor($throwingReranker);
            $result2   = $fallback2->process($this->fakeQuestion(), $documents);

            foreach ($result2 as $i => $doc) {
                $this->assertEquals($snapshot[$i], $doc->metadata);
            }
        });
    }

    // -------------------------------------------------------------------------
    // Property 7: Credential kosong selalu melempar InvalidArgumentException
    // -------------------------------------------------------------------------

    /**
     * **Validates: Requirements 5.2, 5.3, 5.4**
     *
     * For each provider, when the required credential is empty or null,
     * constructing RagAgent always throws InvalidArgumentException.
     *
     * Feature: rag-reranker, Property 7: missing credential always throws
     *
     * @test
     */
    public function property7_missing_credential_always_throws_InvalidArgumentException(): void
    {
        $this->limitTo(100);
        $this->forAll(
            Generators::elements('cohere', 'jina', 'localai'),
            Generators::elements('', null)
        )
        ->then(function (string $provider, ?string $credential) {
            $configKey = match ($provider) {
                'cohere'  => 'cohere_api_key',
                'jina'    => 'jina_api_key',
                'localai' => 'localai_url',
            };

            $this->setRerankerConfig([
                'provider'       => $provider,
                'model'          => 'some-model',
                'top_n'          => 3,
                'cohere_api_key' => null,
                'jina_api_key'   => null,
                'localai_url'    => null,
                $configKey       => $credential,
            ]);

            $this->expectException(\InvalidArgumentException::class);
            new TestableRagAgentForProperties();
        });
    }

    // -------------------------------------------------------------------------
    // Property 8: Nilai TopN non-positif selalu melempar InvalidArgumentException
    // -------------------------------------------------------------------------

    /**
     * **Validates: Requirements 5.5**
     *
     * For any integer ≤ 0 used as RERANKER_TOP_N, constructing RagAgent
     * always throws InvalidArgumentException.
     *
     * Feature: rag-reranker, Property 8: non-positive topN always throws
     *
     * @test
     */
    public function property8_non_positive_topN_always_throws_InvalidArgumentException(): void
    {
        $this->limitTo(100);
        $this->forAll(
            Generators::oneOf(
                Generators::neg(),
                Generators::constant(0)
            )
        )
        ->then(function (int $invalidTopN) {
            $this->setRerankerConfig([
                'provider'    => 'localai',
                'model'       => 'cross-encoder',
                'top_n'       => $invalidTopN,
                'localai_url' => 'http://localhost:8080/',
            ]);

            $this->expectException(\InvalidArgumentException::class);
            new TestableRagAgentForProperties();
        });
    }
}
