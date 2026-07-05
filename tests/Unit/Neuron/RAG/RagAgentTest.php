<?php

declare(strict_types=1);

namespace Tests\Unit\Neuron\RAG;

use App\Neuron\RAG\FallbackRerankerProcessor;
use App\Neuron\RAG\RagAgent;
use NeuronAI\RAG\PostProcessor\CohereRerankerPostProcessor;
use NeuronAI\RAG\PostProcessor\JinaRerankerPostProcessor;
use NeuronAI\RAG\PostProcessor\LocalAIRerankerPostProcessor;
use Tests\TestCase;

/**
 * TestableRagAgent exposes the protected postProcessors() method as public
 * so it can be asserted on in unit tests.
 */
class TestableRagAgent extends RagAgent
{
    public function postProcessors(): array
    {
        return parent::postProcessors();
    }
}

class RagAgentTest extends TestCase
{
    /**
     * Helper: override the services.reranker config for a single test.
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
    // Test 1: Valid localai config → postProcessors() contains exactly one
    //         FallbackRerankerProcessor
    // -------------------------------------------------------------------------

    public function test_valid_localai_config_registers_exactly_one_fallback_reranker_processor(): void
    {
        $this->setRerankerConfig([
            'provider'    => 'localai',
            'model'       => 'cross-encoder',
            'top_n'       => 3,
            'localai_url' => 'http://localhost:8080/',
        ]);

        $agent     = new TestableRagAgent();
        $processors = $agent->postProcessors();

        $this->assertCount(1, $processors);
        $this->assertInstanceOf(FallbackRerankerProcessor::class, $processors[0]);
    }

    // -------------------------------------------------------------------------
    // Test 2: RERANKER_PROVIDER=null → falls back to default 'localai', no exception
    // -------------------------------------------------------------------------

    public function test_null_provider_uses_default_localai_without_exception(): void
    {
        $this->setRerankerConfig([
            'provider'    => null,
            'model'       => 'cross-encoder',
            'top_n'       => 3,
            'localai_url' => 'http://localhost:8080/',
        ]);

        // Should not throw; provider null ?? 'localai' picks the default
        $agent      = new TestableRagAgent();
        $processors = $agent->postProcessors();

        $this->assertCount(1, $processors);
        $this->assertInstanceOf(FallbackRerankerProcessor::class, $processors[0]);
    }

    // -------------------------------------------------------------------------
    // Test 3: RERANKER_PROVIDER=cohere + valid credential
    //         → inner processor is CohereRerankerPostProcessor
    // -------------------------------------------------------------------------

    public function test_cohere_provider_with_valid_credential_uses_cohere_inner_processor(): void
    {
        $this->setRerankerConfig([
            'provider'       => 'cohere',
            'model'          => 'rerank-v3.5',
            'top_n'          => 3,
            'cohere_api_key' => 'test-cohere-key',
            'localai_url'    => null,
            'jina_api_key'   => null,
        ]);

        $agent      = new TestableRagAgent();
        $processors = $agent->postProcessors();

        $this->assertCount(1, $processors);
        $fallback = $processors[0];
        $this->assertInstanceOf(FallbackRerankerProcessor::class, $fallback);

        $inner = $this->getPrivate($fallback, 'inner');
        $this->assertInstanceOf(CohereRerankerPostProcessor::class, $inner);
    }

    // -------------------------------------------------------------------------
    // Test 4: RERANKER_PROVIDER=jina + valid credential
    //         → inner processor is JinaRerankerPostProcessor
    // -------------------------------------------------------------------------

    public function test_jina_provider_with_valid_credential_uses_jina_inner_processor(): void
    {
        $this->setRerankerConfig([
            'provider'       => 'jina',
            'model'          => 'jina-reranker-v2-base-multilingual',
            'top_n'          => 3,
            'jina_api_key'   => 'test-jina-key',
            'localai_url'    => null,
            'cohere_api_key' => null,
        ]);

        $agent      = new TestableRagAgent();
        $processors = $agent->postProcessors();

        $this->assertCount(1, $processors);
        $fallback = $processors[0];
        $this->assertInstanceOf(FallbackRerankerProcessor::class, $fallback);

        $inner = $this->getPrivate($fallback, 'inner');
        $this->assertInstanceOf(JinaRerankerPostProcessor::class, $inner);
    }

    // -------------------------------------------------------------------------
    // Test 5: RERANKER_PROVIDER=localai + valid URL
    //         → inner processor is LocalAIRerankerPostProcessor
    // -------------------------------------------------------------------------

    public function test_localai_provider_with_valid_url_uses_localai_inner_processor(): void
    {
        $this->setRerankerConfig([
            'provider'       => 'localai',
            'model'          => 'cross-encoder',
            'top_n'          => 3,
            'localai_url'    => 'http://localhost:8080/',
            'cohere_api_key' => null,
            'jina_api_key'   => null,
        ]);

        $agent      = new TestableRagAgent();
        $processors = $agent->postProcessors();

        $this->assertCount(1, $processors);
        $fallback = $processors[0];
        $this->assertInstanceOf(FallbackRerankerProcessor::class, $fallback);

        $inner = $this->getPrivate($fallback, 'inner');
        $this->assertInstanceOf(LocalAIRerankerPostProcessor::class, $inner);
    }

    // -------------------------------------------------------------------------
    // Test 6: RERANKER_TOP_N=null → falls back to default 3, forwarded to processor
    // -------------------------------------------------------------------------

    public function test_null_top_n_uses_default_value_of_3(): void
    {
        $this->setRerankerConfig([
            'provider'    => 'localai',
            'model'       => 'cross-encoder',
            'top_n'       => null,   // simulates RERANKER_TOP_N not set in .env
            'localai_url' => 'http://localhost:8080/',
        ]);

        // top_n null ?? 3  → picks default 3, so no exception expected
        $agent      = new TestableRagAgent();
        $processors = $agent->postProcessors();

        $this->assertCount(1, $processors);
        $fallback = $processors[0];
        $this->assertInstanceOf(FallbackRerankerProcessor::class, $fallback);

        $inner = $this->getPrivate($fallback, 'inner');
        $this->assertInstanceOf(LocalAIRerankerPostProcessor::class, $inner);

        // Verify topN=3 was actually forwarded to the inner processor
        $topN = $this->getPrivate($inner, 'topN');
        $this->assertSame(3, $topN);
    }
}
