import { useState } from "react";
import { useForm, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import {
    ArrowLeft, Loader2, Database, ChevronLeft, ChevronRight,
    FileText, CheckCircle2, Layers
} from "lucide-react";

export default function Embed({ perusahaan, dokumen, chunks }) {
    // Setup Filter dan Navigasi Chunk
    const [filterType, setFilterType] = useState("");
    const [currentIndex, setCurrentIndex] = useState(0);

    // Ambil daftar tipe unik untuk dropdown filter
    const uniqueTypes = [...new Set(chunks.map(c => c.metadata.statement_type))];

    // Saring chunk berdasarkan pilihan dropdown
    const filteredChunks = filterType
        ? chunks.filter(c => c.metadata.statement_type === filterType)
        : chunks;

    const currentChunk = filteredChunks[currentIndex];

    // Setup Form Inertia untuk Action Start Embedding
    const { post, processing } = useForm({});

    function handleEmbedSubmit() {
        // Asumsi rute POST yang Anda buat untuk eksekusi embed
        post(`/perusahaan/${perusahaan.id}/dokumen/${dokumen.id}/embed`);
    }

    function handleNext() {
        if (currentIndex < filteredChunks.length - 1) setCurrentIndex(currentIndex + 1);
    }

    function handlePrev() {
        if (currentIndex > 0) setCurrentIndex(currentIndex - 1);
    }

    return (
        <div className="max-w-4xl mx-auto space-y-4">
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

            {/* Chunk Viewer Component */}
            <div className="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">

                {/* Toolbar Viewer */}
                <div className="flex flex-wrap items-center gap-3 p-3 border-b border-slate-100 bg-slate-50/50">
                    <div className="flex items-center gap-1.5">
                        <Button
                            variant="outline"
                            size="sm"
                            className="h-8 px-2 text-slate-600"
                            onClick={handlePrev}
                            disabled={currentIndex === 0}
                        >
                            <ChevronLeft className="w-4 h-4" /> Prev
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            className="h-8 px-2 text-slate-600"
                            onClick={handleNext}
                            disabled={filteredChunks.length === 0 || currentIndex === filteredChunks.length - 1}
                        >
                            Next <ChevronRight className="w-4 h-4" />
                        </Button>
                    </div>

                    <span className="text-xs font-medium text-slate-500 min-w-[50px] text-center">
                        {filteredChunks.length > 0 ? `${currentIndex + 1} / ${filteredChunks.length}` : "0 / 0"}
                    </span>

                    <div className="ml-auto flex items-center gap-2">
                        <span className="text-xs font-semibold text-slate-500">Filter Tipe:</span>
                        <select
                            className="text-xs border border-slate-200 rounded bg-white px-2 py-1.5 focus:outline-none focus:border-blue-500"
                            value={filterType}
                            onChange={(e) => {
                                setFilterType(e.target.value);
                                setCurrentIndex(0); // Reset index tiap ganti filter
                            }}
                        >
                            <option value="">Semua Tipe</option>
                            {uniqueTypes.map(type => (
                                <option key={type} value={type}>{type.replace('_', ' ').toUpperCase()}</option>
                            ))}
                        </select>
                    </div>
                </div>

                {/* Body Viewer (Teks Chunk) */}
                <div className="p-5 bg-slate-900 text-slate-300 font-mono text-sm leading-relaxed whitespace-pre-wrap max-h-96 overflow-y-auto">
                    {currentChunk ? currentChunk.text : <span className="text-slate-600 italic">Tidak ada chunk yang sesuai filter.</span>}
                </div>

                {/* Footer Metadata */}
                {currentChunk && (
                    <div className="flex flex-wrap gap-x-6 gap-y-2 p-4 border-t border-slate-100 bg-slate-50 text-xs">
                        <div className="flex gap-1.5">
                            <span className="font-semibold text-slate-400">Tipe Tabel:</span>
                            <Badge variant="outline" className="text-[10px] bg-white text-blue-700 border-blue-200 capitalize">
                                {currentChunk.metadata.statement_type.replace('_', ' ')}
                            </Badge>
                        </div>
                        <div className="flex gap-1.5">
                            <span className="font-semibold text-slate-400">Chunk ID:</span>
                            <span className="text-slate-700 font-mono">#{currentChunk.metadata.chunk_index}</span>
                        </div>
                        <div className="flex gap-1.5">
                            <span className="font-semibold text-slate-400">Halaman PDF:</span>
                            <span className="text-slate-700 font-mono">
                                {currentChunk.metadata.page_start} {currentChunk.metadata.page_start !== currentChunk.metadata.page_end && `- ${currentChunk.metadata.page_end}`}
                            </span>
                        </div>
                        <div className="flex gap-1.5">
                            <span className="font-semibold text-slate-400">Periode:</span>
                            <span className="text-slate-700 font-mono">{currentChunk.metadata.period}</span>
                        </div>
                    </div>
                )}
            </div>

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
