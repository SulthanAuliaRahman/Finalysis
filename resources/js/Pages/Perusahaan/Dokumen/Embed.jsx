import { useForm, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { ArrowLeft, Loader2, Database, Layers } from "lucide-react";
import ChunkViewer from "@/Components/Dokumen/ChunkViewer";

export default function Embed({ perusahaan, dokumen, chunks }) {
    // Ambil daftar tipe unik untuk header statistik
    const uniqueTypes = [...new Set(chunks.map(c => c.metadata.statement_type))];

    // Setup Form Inertia untuk Action Start Embedding
    const { post, processing } = useForm({});

    function handleEmbedSubmit() {
        post(`/perusahaan/${perusahaan.id}/dokumen/${dokumen.id}/embed`);
    }

    return (
        <div className="max-w mx-auto space-y-4">
            <Link href={`/perusahaan/${perusahaan.id}/dokumen`} className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
                <ArrowLeft className="w-3.5 h-3.5" /> Kembali ke Daftar Dokumen
            </Link>

            {/* Header Informasi */}
            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 className="text-lg font-bold text-slate-900 flex items-center gap-2">
                        Langkah 3: Pratinjau Teks (Chunking) & Embedding
                    </h2>
                    <p className="text-xs text-slate-500 mt-1">
                        Verifikasi potongan teks (*chunks*) yang telah digenerasi dari data numerik sebelum dimasukkan ke dalam Vector Database RAG.
                    </p>
                </div>
                <div className="flex gap-4 items-center bg-slate-50 px-4 py-2 border border-slate-100 rounded-lg">
                    <div className="flex flex-col">
                        <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Chunks</span>
                        <span className="text-lg font-bold text-slate-800">{chunks.length}</span>
                    </div>
                    <div className="w-px h-8 bg-slate-200"></div>
                    <div className="flex flex-col">
                        <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tabel Terdeteksi</span>
                        <span className="text-lg font-bold text-slate-800">{uniqueTypes.length}</span>
                    </div>
                </div>
            </div>

            <ChunkViewer chunks={chunks} />

            {/* Tombol Eksekusi Akhir (Embed) */}
            <div className="bg-blue-50/50 border border-blue-100 rounded-xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <Database className="w-5 h-5 text-blue-600" />
                    </div>
                    <div>
                        <h4 className="text-sm font-bold text-slate-800">Mulai Proses Embedding</h4>
                        <p className="text-xs text-slate-500 mt-0.5">Kirim {chunks.length} chunks ke NeuronAI Data Loader untuk diindeks.</p>
                    </div>
                </div>

                <Button
                    onClick={handleEmbedSubmit}
                    disabled={processing || chunks.length === 0}
                    className="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white min-w-[200px]"
                >
                    {processing ? (
                        <><Loader2 className="w-4 h-4 animate-spin mr-2" /> Sedang Mengindeks...</>
                    ) : (
                        <><Layers className="w-4 h-4 mr-2" /> Mulai Data Loader</>
                    )}
                </Button>
            </div>
        </div>
    );
}

Embed.layout = page => <AppLayout title="Pratinjau Chunks & Embedding" children={page} />;
