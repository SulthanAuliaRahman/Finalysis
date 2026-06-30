<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\Dokumen;
use App\Services\PythonDocumentService;
use App\Neuron\DataLoader\DataLoader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

use Illuminate\Filesystem\FilesystemAdapter;

class DokumenController extends Controller
{
    protected PythonDocumentService $pythonService;

    public function __construct(PythonDocumentService $pythonService)
    {
        $this->pythonService = $pythonService;
    }

    public function index(Perusahaan $perusahaan)
    {
        $dokumen = $perusahaan->dokumen()
            ->select('id', 'nama_file', 'periode', 'ukuran_file', 'status', 'created_at')
            ->latest()
            ->get();

        return Inertia::render('Perusahaan/Dokumen/Index', [
            'perusahaan' => $perusahaan,
            'dokumenList' => $dokumen
        ]);
    }

    public function create(Perusahaan $perusahaan)
    {
        return Inertia::render('Perusahaan/Dokumen/Create', [
            'perusahaan' => $perusahaan
        ]);
    }

    public function store(Request $request, Perusahaan $perusahaan)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:20480', // Maksimal 20MB
            'periode' => 'required|string|max:10',
            'statement_types' => 'required|array'
        ]);

        $file = $request->file('file');
        $namaFileOriginal = $file->getClientOriginalName();
        $ukuranFile = $file->getSize();
        $storedPath = $file->storeAs('documents', time() . '_' . $namaFileOriginal, 'local');

        $dokumen = DB::transaction(function () use ($perusahaan, $namaFileOriginal, $storedPath, $request, $ukuranFile) {
            return Dokumen::create([
                'perusahaan_id' => $perusahaan->id,
                'nama_file' => $namaFileOriginal,
                'storage_path' => $storedPath,
                'periode' => $request->periode,
                'statement_types' => $request->statement_types,
                'ukuran_file' => $ukuranFile,
                'status' => 'menunggu'
            ]);
        });

        try {
            $result = $this->pythonService->extract($file, $perusahaan->nama, $request->periode, $request->statement_types);

            // dd($result); //  Debugging: Tampilkan hasil ekstraksi dari Python Service

            DB::transaction(function () use ($dokumen, $result) {
                $extracted = $result['extracted'] ?? [];
                $foundAtData = $result['found_at'] ?? [];

                // Filter data found_at per kelompok komponen untuk memetakan traceability koordinat
                $filterFoundAt = function($fields) use ($foundAtData) {
                    return array_intersect_key($foundAtData, array_flip($fields));
                };

                // Ambil nilai Neraca
                if (isset($extracted['balance_sheet'])) {
                    $bs = $extracted['balance_sheet'];
                    DB::table('neraca')->insert([
                        'dokumen_id' => $dokumen->id,
                        'total_equity' => $bs['total_equity'] ?? null,
                        'total_liabilities' => $bs['total_liabilities'] ?? null,
                        'current_liabilities' => $bs['current_liabilities'] ?? null,
                        'total_assets' => $bs['total_assets'] ?? null,
                        'current_assets' => $bs['current_assets'] ?? null,
                        'found_at' => json_encode($filterFoundAt(['total_equity', 'total_liabilities', 'current_liabilities', 'total_assets', 'current_assets'])),
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                }

                // Ambil nilai Laba Rugi
                if (isset($extracted['income_statement'])) {
                    $is = $extracted['income_statement'];
                    DB::table('laba_rugi')->insert([
                        'dokumen_id' => $dokumen->id,
                        'pendapatan'  => $is['revenue'] ?? null,
                        'laba_kotor'  => $is['gross_profit'] ?? null,
                        'laba_bersih' => $is['net_profit'] ?? null,
                        'found_at' => json_encode($filterFoundAt(['revenue', 'gross_profit', 'net_profit'])),
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                }

                // kas_masuk  = komponen yang bernilai positif
                // kas_keluar =  komponen yang bernilai negatif
                if (isset($extracted['cash_flow'])) {
                    $cf = $extracted['cash_flow'];

                    $cfo = $cf['cash_flow_from_operations'] ?? 0;
                    $cfi = $cf['cash_flow_from_investing']  ?? 0;
                    $cff = $cf['cash_flow_from_financing']  ?? 0;

                    $kasMasuk  = max(0, $cfo) + max(0, $cfi) + max(0, $cff);
                    $kasKeluar = abs(min(0, $cfo)) + abs(min(0, $cfi)) + abs(min(0, $cff));

                    DB::table('arus_kas')->insert([
                        'dokumen_id' => $dokumen->id,
                        'kas_masuk'  => $kasMasuk,
                        'kas_keluar' => $kasKeluar,
                        'found_at' => json_encode($filterFoundAt([
                            'cash_flow_from_operations',
                            'cash_flow_from_investing',
                            'cash_flow_from_financing',
                        ])),
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                }

                $dokumen->update(['status' => 'diekstrak']);
            });

            return redirect()->route('perusahaan.dokumen.review', [$perusahaan->id, $dokumen->id]);

        } catch (\Exception $e) {
            return redirect()->route('perusahaan.dokumen.index', $perusahaan->id)
                ->with('error', 'Gagal ekstraksi AI: ' . $e->getMessage());
        }
    }

    public function review(Perusahaan $perusahaan, Dokumen $dokumen)
    {
        $neraca = DB::table('neraca')->where('dokumen_id', $dokumen->id)->first();
        $labaRugi = DB::table('laba_rugi')->where('dokumen_id', $dokumen->id)->first();
        $arusKas = DB::table('arus_kas')->where('dokumen_id', $dokumen->id)->first();

        Log::info('neraca found_at raw:',   ['value' => $neraca?->found_at]);
        Log::info('labarugi found_at raw:', ['value' => $labaRugi?->found_at]);
        Log::info('aruskAs found_at raw:',  ['value' => $arusKas?->found_at]);

        // Gabungkan seluruh payload found_at gabungan untuk dikirim ke frontend review
        $foundAtMerged = array_merge(
            json_decode($neraca->found_at ?? '{}', true),
            json_decode($labaRugi->found_at ?? '{}', true),
            json_decode($arusKas->found_at ?? '{}', true)
        );

        // dd($foundAtMerged); //Debug: Request ke review

        return Inertia::render('Perusahaan/Dokumen/Review', [
            'perusahaan' => $perusahaan,
            'dokumen' => $dokumen,
            'extractedData' => [
                'neraca' => $neraca,
                'laba_rugi' => $labaRugi,
                'arus_kas' => $arusKas
            ],
            'foundAt' => $foundAtMerged
        ]);
    }

    public function chunk(Request $request, Perusahaan $perusahaan, Dokumen $dokumen)
    {
        Log::info('found_at raw:', ['value' => $request->input('found_at'), 'type' => gettype($request->input('found_at'))]);

        // untuk Dokumentasi nyalain
        // dd($request->all());

        $foundAt = $request->input('found_at', '{}');
        // Pastikan format menjadi array
        $foundAtArray = is_string($foundAt) ? json_decode($foundAt, true) : $foundAt;
        if (is_string($foundAtArray)) {
            $foundAtArray = json_decode($foundAtArray, true) ?? [];
        }

        // Penambahan Helper untuk membagi payload found_at berdasarkan tabel database masing-masing
        $filterFoundAt = function($fields) use ($foundAtArray) {
            return array_intersect_key($foundAtArray, array_flip($fields));
        };

        DB::transaction(function () use ($dokumen, $request, $filterFoundAt) {
            if ($request->has('neraca')) {
                DB::table('neraca')->where('dokumen_id', $dokumen->id)->update([
                    'current_assets' => $request->input('neraca.current_assets'),
                    'total_assets' => $request->input('neraca.total_assets'),
                    'current_liabilities' => $request->input('neraca.current_liabilities'),
                    'total_liabilities' => $request->input('neraca.total_liabilities'),
                    'total_equity' => $request->input('neraca.total_equity'),
                    // Ikut simpan koordinat/metadata baru hasil manipulasi manual
                    'found_at' => json_encode($filterFoundAt(['total_equity', 'total_liabilities', 'current_liabilities', 'total_assets', 'current_assets'])),
                    'updated_at' => now()
                ]);
            }
            if ($request->has('laba_rugi')) {
                DB::table('laba_rugi')->where('dokumen_id', $dokumen->id)->update([
                    'pendapatan' => $request->input('laba_rugi.pendapatan'),
                    'laba_kotor' => $request->input('laba_rugi.laba_kotor'),
                    'laba_bersih' => $request->input('laba_rugi.laba_bersih'),
                    // Ikut simpan koordinat/metadata baru hasil manipulasi manual
                    'found_at' => json_encode($filterFoundAt(['revenue', 'gross_profit', 'net_profit'])),
                    'updated_at' => now()
                ]);
            }
            if ($request->has('arus_kas')) {
                DB::table('arus_kas')->where('dokumen_id', $dokumen->id)->update([
                    'kas_masuk' => $request->input('arus_kas.kas_masuk'),
                    'kas_keluar' => $request->input('arus_kas.kas_keluar'),
                    // Ikut simpan koordinat/metadata baru termasuk key kas_masuk & kas_keluar manual
                    'found_at' => json_encode($filterFoundAt([
                        'cash_flow_from_operations',
                        'cash_flow_from_investing',
                        'cash_flow_from_financing',
                        'kas_masuk',
                        'kas_keluar'
                    ])),
                    'updated_at' => now()
                ]);
            }
        });

        $absolutePath = Storage::disk('local')->path($dokumen->storage_path);

        // Teruskan data payload gabungan foundAtArray ke python chunking engine secara aman
        $chunkResult = $this->pythonService->chunk(
            $absolutePath,
            $dokumen->nama_file,
            $perusahaan->nama,
            $dokumen->periode,
            $dokumen->statement_types ?? ['neraca', 'laba_rugi'],
            $foundAtArray ?? []
        );

        // Bulk Insert array chunks ke tabel database (Sama seperti code lama anda)
        DB::transaction(function () use ($dokumen, $chunkResult) {
            DB::table('chunks')->where('dokumen_id', $dokumen->id)->delete();

            $insertPayload = [];
            foreach ($chunkResult['chunks'] as $c) {
                $insertPayload[] = [
                    'dokumen_id'   => $dokumen->id,
                    'chunk_index'  => $c['metadata']['chunk_index'] ?? 0,
                    'text'         => $c['text'],
                    'metadata'     => json_encode($c['metadata']),
                    'has_table'    => $c['metadata']['has_table'] ?? false,
                    'created_at'   => now()
                ];
            }

            DB::table('chunks')->insert($insertPayload);
            $dokumen->update(['status' => 'dichunk']);
        });

        return redirect()->route('perusahaan.dokumen.embed', [$perusahaan->id, $dokumen->id]);
    }

    public function embedPage(Perusahaan $perusahaan, Dokumen $dokumen)
    {
        $chunks = DB::table('chunks')
            ->where('dokumen_id', $dokumen->id)
            ->orderBy('chunk_index', 'asc')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'text' => $c->text,
                    'metadata' => json_decode($c->metadata, true)
                ];
            });

        return Inertia::render('Perusahaan/Dokumen/Embed', [
            'perusahaan' => $perusahaan,
            'dokumen' => $dokumen,
            'chunks' => $chunks
        ]);
    }

    public function startEmbedding(Request $request, Perusahaan $perusahaan, Dokumen $dokumen)
    {
        $chunksFromDb = DB::table('chunks')
            ->where('dokumen_id', $dokumen->id)
            ->orderBy('chunk_index', 'asc')
            ->get()
            ->map(function ($c) {
                return [
                    'text' => $c->text,
                    'metadata' => json_decode($c->metadata, true)
                ];
            })->toArray();

        //  NeuronAI DataLoader ke Vector DB
        $embeddedCount = DataLoader::embedChunks($chunksFromDb);

        if ($embeddedCount > 0) {
            $dokumen->update(['status' => 'selesai']);
        }

        return redirect()->route('perusahaan.dokumen.index', $perusahaan->id);
    }

    public function showChunks(Perusahaan $perusahaan, Dokumen $dokumen)
    {
        $chunks = DB::table('chunks')
            ->where('dokumen_id', $dokumen->id)
            ->orderBy('chunk_index', 'asc')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'text' => $c->text,
                    'metadata' => json_decode($c->metadata, true)
                ];
            });

        return Inertia::render('Perusahaan/Dokumen/ShowChunks', [
            'perusahaan' => $perusahaan,
            'dokumen' => $dokumen,
            'chunks' => $chunks
        ]);
    }

    public function checkPythonHealth()
    {
        try {
            $status = $this->pythonService->health();
            return response()->json([
                'ok' => true,
                'status' => $status['status'] ?? 'ok',
                'version' => $status['version'] ?? '1.0.0'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Perusahaan $perusahaan, Dokumen $dokumen)
    {
        try {
            if (Storage::disk('local')->exists($dokumen->storage_path)) {
                Storage::disk('local')->delete($dokumen->storage_path);
            }
            $dokumen->delete();

            return redirect()->route('perusahaan.dokumen.index', $perusahaan->id)
                ->with('success', 'Dokumen beserta data ekstraksi berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Gagal menghapus dokumen: ' . $e->getMessage());

            return redirect()->route('perusahaan.dokumen.index', $perusahaan->id)
                ->with('error', 'Gagal menghapus dokumen: ' . $e->getMessage());
        }
    }

    public function viewPdf(Perusahaan $perusahaan, Dokumen $dokumen)
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        if (! $disk->exists($dokumen->storage_path)) {
            abort(404, 'File dokumen tidak ditemukan di server.');
        }

        return $disk->response($dokumen->storage_path);
    }
}
