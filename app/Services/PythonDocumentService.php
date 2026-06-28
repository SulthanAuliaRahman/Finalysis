<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

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

    public function extract(UploadedFile $file, string $company, string $period, array $statementTypes): array
    {
        $response = Http::timeout($this->timeout)
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post("{$this->baseUrl}/api/v1/extract", [
                'company'         => $company,
                'period'          => $period,
                'statement_types' => json_encode($statementTypes),
            ]);

        $this->assertSuccess($response);
        return $response->json();
    }

    public function chunk(string $absoluteFilePath, string $originalName, string $company, string $period, array $statementTypes, array $foundAt): array
    {
        if (!file_exists($absoluteFilePath)) {
            throw new \InvalidArgumentException("Berkas fisik PDF tidak ditemukan di storage lokal: {$absoluteFilePath}");
        }

        Log::info('Sending to Python /chunk:', [
            'found_at_encoded' => json_encode($foundAt),
            'found_at_type'    => gettype($foundAt),
        ]);

        $response = Http::timeout($this->timeout)
            ->attach('file', file_get_contents($absoluteFilePath), $originalName)
            ->post("{$this->baseUrl}/api/v1/chunk", [
                'company'         => $company,
                'period'          => $period,
                'statement_types' => json_encode($statementTypes),
                'found_at'        => json_encode($foundAt), // Mengirim koordinat terverifikasi dari frontend
            ]);

        $this->assertSuccess($response);
        return $response->json();
    }

    private function assertSuccess(Response $response): void
    {
        if (!$response->successful()) {
            $detail = $response->json('detail') ?? $response->body();
            throw new \RuntimeException(
                "Python service error [{$response->status()}]: " . (is_array($detail) ? json_encode($detail) : $detail)
            );
        }
    }
}
