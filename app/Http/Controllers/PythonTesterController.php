<?php

namespace App\Http\Controllers;

use App\Neuron\DataLoader\DataLoader;
use App\Services\PythonDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use NeuronAI\Chat\Messages\UserMessage;

use App\Neuron\RAG\RagAgent;

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
            'statement_types' => 'required|json',
        ]);

        try {
            $statementTypes = json_decode($request->input('statement_types'), true);

            $result = $this->pythonService->ingest(
                file:    $request->file('file'),
                company: $request->input('company'),
                period:  $request->input('period'),
                statementTypes: $statementTypes,
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

    public function embed(Request $request): JsonResponse
    {
        $request->validate([
            'chunks'             => 'required|array|min:1',
            'chunks.*.text'      => 'required|string',
            'chunks.*.metadata'  => 'sometimes|array',
        ]);

        try {
            $embedded = DataLoader::embedChunks($request->input('chunks'));

            return response()->json([
                'ok'       => true,
                'embedded' => $embedded,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function chatPage()
    {
        return view('python-tester.chat');
    }

    public function chatAsk(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        try {
            $response = RagAgent::make()->chat(
                new UserMessage($request->input('message'))
            );

            return response()->json([
                'ok'     => true,
                'answer' => $response->getMessage()->getContent(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
