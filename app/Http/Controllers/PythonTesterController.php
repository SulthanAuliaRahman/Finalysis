<?php

namespace App\Http\Controllers;

use App\Services\PythonDocumentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PythonTesterController extends Controller
{
    public function __construct(
        private PythonDocumentService $pythonService
    ) {}

    // ── Pages ────────────────────────────────────────────────────────────────

    public function index()
    {
        return view('python-tester.index');
    }

    public function index2()
    {
        return view('python-tester.index2');
    }

    // ── API ──────────────────────────────────────────────────────────────────

    public function health(): JsonResponse
    {
        try {
            $result = $this->pythonService->health();
            return response()->json(['ok' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 503);
        }
    }

    /**
     * Ingest — chunking + extraction (full pipeline).
     */
    public function ingest(Request $request): JsonResponse
    {
        $request->validate([
            'file'    => 'required|file|mimes:pdf|max:51200',
            'company' => 'required|string|max:255',
            'period'  => 'required|string|max:50',
        ]);

        try {
            $result = $this->pythonService->ingest(
                file:    $request->file('file'),
                company: $request->input('company'),
                period:  $request->input('period'),
            );
            return response()->json(['ok' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Extract — hanya ekstraksi data finansial, tanpa chunking.
     */
    public function extract(Request $request): JsonResponse
    {
        $request->validate([
            'file'    => 'required|file|mimes:pdf|max:51200',
            'company' => 'required|string|max:255',
            'period'  => 'required|string|max:50',
        ]);

        try {
            $result = $this->pythonService->extract(
                file:    $request->file('file'),
                company: $request->input('company'),
                period:  $request->input('period'),
            );
            return response()->json(['ok' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
