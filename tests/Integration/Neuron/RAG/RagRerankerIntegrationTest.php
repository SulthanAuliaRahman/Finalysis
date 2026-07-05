<?php

namespace Tests\Integration\Neuron\RAG;

use App\Neuron\RAG\FallbackRerankerProcessor;
use NeuronAI\Chat\Enums\MessageRole;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Exceptions\HttpException;
use NeuronAI\HttpClient\HttpClientInterface;
use NeuronAI\HttpClient\HttpRequest;
use NeuronAI\HttpClient\HttpResponse;
use NeuronAI\HttpClient\StreamInterface;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\PostProcessor\CohereRerankerPostProcessor;
use NeuronAI\RAG\PostProcessor\PostProcessorInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RagRerankerIntegrationTest extends TestCase
{
    #[Test]
    public function timeout_http_mengembalikan_dokumen_asli(): void
    {
        $mockHttpClient = new class implements HttpClientInterface {
            public function request(HttpRequest $request): HttpResponse
            {
                throw new HttpException('Connection timed out');
            }

            public function stream(HttpRequest $request): StreamInterface
            {
                throw new HttpException('Connection timed out');
            }

            public function withBaseUri(string $baseUri): HttpClientInterface
            {
                return $this;
            }

            public function withHeaders(array $headers): HttpClientInterface
            {
                return $this;
            }

            public function withTimeout(float $timeout): HttpClientInterface
            {
                return $this;
            }
        };

        $inner = new CohereRerankerPostProcessor(
            key: 'fake-key-for-test',
            httpClient: $mockHttpClient,
        );

        $fallback = new FallbackRerankerProcessor($inner);

        $documents = [
            (new Document('Laporan neraca Q1 2025'))->addMetadata('company', 'PT ABC'),
            (new Document('Laporan laba rugi Q1 2025'))->addMetadata('company', 'PT ABC'),
        ];

        $start = microtime(true);
        $result = $fallback->process($this->fakeMessage(), $documents);
        $elapsed = microtime(true) - $start;

        $this->assertCount(2, $result);
        $this->assertSame('Laporan neraca Q1 2025', $result[0]->getContent());
        $this->assertSame('Laporan laba rugi Q1 2025', $result[1]->getContent());
        $this->assertLessThan(5.0, $elapsed, 'Fallback harus selesai dalam < 5 detik.');
    }

    #[Test]
    public function jumlah_dokumen_kurang_dari_topn_tidak_dipaksa_padding(): void
    {
        $innerYangSukses = new class implements PostProcessorInterface {
            public function process(Message $question, array $documents): array
            {
                foreach ($documents as $doc) {
                    $doc->setScore(0.9);
                }
                return $documents;
            }
        };

        $fallback = new FallbackRerankerProcessor($innerYangSukses);

        $documents = [
            new Document('Dokumen A'),
            new Document('Dokumen B'),
        ];

        $result = $fallback->process($this->fakeMessage(), $documents);

        $this->assertCount(2, $result, 'Tidak boleh ada padding dokumen palsu meski TopN=5.');
    }

    #[Test]
    public function exception_dari_inner_tidak_menghentikan_pipeline(): void
    {
        $innerYangGagal = new class implements PostProcessorInterface {
            public function process(Message $question, array $documents): array
            {
                throw new \RuntimeException('Simulasi kegagalan API reranker.');
            }
        };

        $fallback = new FallbackRerankerProcessor($innerYangGagal);

        $documents = [new Document('Dokumen asli tidak boleh hilang')];

        $result = $fallback->process($this->fakeMessage(), $documents);

        $this->assertCount(1, $result);
        $this->assertSame('Dokumen asli tidak boleh hilang', $result[0]->getContent());
    }

    private function fakeMessage(): Message
    {
        return new Message(
            role: MessageRole::USER,
            content: 'Analisis laporan keuangan Q1 2025'
        );
    }
}