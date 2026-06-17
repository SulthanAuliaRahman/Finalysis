<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class PythonDocumentService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.python_service.url', 'http://localhost:8000');
        $this->timeout = (int) config('services.python_service.timeout', 120);
    }

    public function health(): array
    {
        $response = Http::timeout(5)->get("{$this->baseUrl}/api/v1/health");

        if (! $response->successful()) {
            throw new \RuntimeException("Service unreachable [{$response->status()}]");
        }

        return $response->json();
    }

    /**
     * Full pipeline: chunking + extraction.
     */
    public function ingest(UploadedFile $file, string $company, string $period,array $statementTypes = ['neraca','laba_rugi','arus_kas'] ): array
    {
        $response = Http::timeout($this->timeout)
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post("{$this->baseUrl}/api/v1/ingest", [
                'company'        => $company,
                'period'         => $period,
                'statement_types' => json_encode($statementTypes),
                'run_extraction' => 'true',
            ]);

        $this->assertSuccess($response);
        return $response->json();
    }

    /**
     * Extract only — hanya data finansial, tanpa chunks.
     * Memanggil endpoint /api/v1/extract di Python service.
     */
    public function extract(UploadedFile $file, string $company, string $period): array
    {
        $response = Http::timeout($this->timeout)
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post("{$this->baseUrl}/api/v1/extract", [
                'company' => $company,
                'period'  => $period,
            ]);

        $this->assertSuccess($response);
        return $response->json();
    }

    private function assertSuccess(Response $response): void
    {
        if (! $response->successful()) {
            $detail = $response->json('detail') ?? $response->body();
            throw new \RuntimeException(
                "Python service error [{$response->status()}]: {$detail}"
            );
        }
    }
}
