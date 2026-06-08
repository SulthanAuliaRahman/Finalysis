<?php
// app/Http/Controllers/DocumentController.php
namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\PythonDocumentService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        private PythonDocumentService $pythonService
    ) {}

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file'    => 'required|file|mimes:pdf|max:51200',
            'company' => 'required|string|max:255',
            'period'  => 'required|string|max:50',
        ]);

        // Kirim ke Python service
        $result = $this->pythonService->ingest(
            file:    $request->file('file'),
            company: $request->company,
            period:  $request->period,
        );

        // Simpan ke database
        $document = Document::create([
            'company'         => $result['company'],
            'period'          => $result['period'],
            'source_filename' => $result['source'],
            'total_chunks'    => $result['total_chunks'],
            'chunks'          => $result['chunks'],
            'statements'      => $result['statements'],
        ]);

        return redirect()->route('documents.show', $document)
                         ->with('success', "Berhasil memproses {$result['total_chunks']} chunks.");
    }

    public function show(Document $document)
    {
        return view('documents.show', compact('document'));
    }
}
