import { useState } from "react";
import { Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import {
    ArrowLeft, ChevronLeft, ChevronRight, CheckCircle2,
    Download, ShieldCheck, FileJson
} from "lucide-react";

export default function ShowChunks({ perusahaan, dokumen, chunks }) {
    const [filterType, setFilterType] = useState("");
    const [currentIndex, setCurrentIndex] = useState(0);

    const uniqueTypes = [...new Set(chunks.map(c => c.metadata.statement_type))];
    const filteredChunks = filterType
        ? chunks.filter(c => c.metadata.statement_type === filterType)
        : chunks;

    const currentChunk = filteredChunks[currentIndex];

    // Fungsi simulasi untuk mendownload full JSON dari data chunks yang ada
    function downloadJSON() {
        const dataStr = "data:text/json;charset=utf-8hd," + encodeURIComponent(JSON.stringify(chunks, null, 2));
        const downloadAnchor = document.createElement('a');
        downloadAnchor.setAttribute("href", dataStr);
        downloadAnchor.setAttribute("download", `chunks_${dokumen.nama_file}.json`);
        document.body.appendChild(downloadAnchor);
        downloadAnchor.click();
        downloadAnchor.remove();
    }

    return (
        <div className="max-w mx-auto space-y-4">
            {/* Tombol Kembali */}
            <Link href={`/perusahaan/${perusahaan.id}/dokumen`} className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
                <ArrowLeft className="w-3.5 h-3.5" /> Kembali ke Daftar Dokumen
            </Link>

            {/* Header Read-Only Card */}
            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div className="space-y-1">
                    <div className="flex items-center gap-2">
                        <h2 className="text-lg font-bold text-slate-900">Arsip Potongan Teks (Chunks)</h2>
                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-green-50 border border-green-200 text-[11px] font-semibold text-green-700">
                            <ShieldCheck className="w-3 h-3" /> Terindeks di RAG
                        </span>
                    </div>
                    <p className="text-xs text-slate-500 font-mono truncate max-w-xl">
                        Berkas: {dokumen.nama_file}
                    </p>
                </div>

                {/* Tombol Download Backup JSON */}
                <Button onClick={downloadJSON} variant="outline" size="sm" className="gap-1.5 h-9 border-slate-200 text-slate-700 shadow-2xs flex-shrink-0">
                    <Download className="w-3.5 h-3.5" /> Export JSON
                </Button>
            </div>

            {/* Chunk Viewer Panel */}
            <div className="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">

                {/* Toolbar Navigasi & Filter */}
                <div className="flex flex-wrap items-center gap-3 p-3 border-b border-slate-100 bg-slate-50/50">
                    <div className="flex items-center gap-1.5">
                        <Button
                            variant="outline"
                            size="sm"
                            className="h-8 px-2"
                            onClick={() => currentIndex > 0 && setCurrentIndex(currentIndex - 1)}
                            disabled={currentIndex === 0}
                        >
                            <ChevronLeft className="w-4 h-4" /> Prev
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            className="h-8 px-2"
                            onClick={() => currentIndex < filteredChunks.length - 1 && setCurrentIndex(currentIndex + 1)}
                            disabled={filteredChunks.length === 0 || currentIndex === filteredChunks.length - 1}
                        >
                            Next <ChevronRight className="w-4 h-4" />
                        </Button>
                    </div>

                    <span className="text-xs font-medium text-slate-500 min-w-[50px] text-center font-mono">
                        {filteredChunks.length > 0 ? `${currentIndex + 1} / ${filteredChunks.length}` : "0 / 0"}
                    </span>

                    <div className="ml-auto flex items-center gap-2">
                        <span className="text-xs font-semibold text-slate-500">Filter Komponen:</span>
                        <select
                            className="text-xs border border-slate-200 rounded bg-white px-2 py-1.5 focus:outline-none"
                            value={filterType}
                            onChange={(e) => {
                                setFilterType(e.target.value);
                                setCurrentIndex(0);
                            }}
                        >
                            <option value="">Semua Tipe</option>
                            {uniqueTypes.map(type => (
                                <option key={type} value={type}>{type.replace('_', ' ').toUpperCase()}</option>
                            ))}
                        </select>
                    </div>
                </div>

                {/* Teks Box Tampilan Chunk */}
                <div className="p-5 bg-slate-900 text-slate-300 font-mono text-sm leading-relaxed whitespace-pre-wrap max-h-[800px] overflow-y-auto">
                    {currentChunk ? currentChunk.text : <span className="text-slate-600 italic">Tidak ada chunk tersedia.</span>}
                </div>

                {/* Metadata di Bagian Bawah */}
                {currentChunk && (
                    <div className="flex flex-wrap gap-x-6 gap-y-2 p-4 border-t border-slate-100 bg-slate-50 text-xs text-slate-600">
                        <div>
                            <span className="font-semibold text-slate-400 mr-1.5">Tipe:</span>
                            <Badge variant="outline" className="text-[10px] bg-white text-slate-700 capitalize">
                                {currentChunk.metadata.statement_type.replace('_', ' ')}
                            </Badge>
                        </div>
                        <div>
                            <span className="font-semibold text-slate-400 mr-1.5">Index:</span>
                            <span className="font-mono text-slate-800 font-medium">#{currentChunk.metadata.chunk_index}</span>
                        </div>
                        <div>
                            <span className="font-semibold text-slate-400 mr-1.5">Halaman PDF:</span>
                            <span className="font-mono text-slate-800 font-medium">{currentChunk.metadata.page_start}</span>
                        </div>
                    </div>
                )}
            </div>

            {/* Notifikasi Status RAG Active */}
            <div className="bg-green-50/40 border border-green-100 rounded-xl p-4 flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <CheckCircle2 className="w-4 h-4 text-green-600" />
                </div>
                <p className="text-xs text-green-800 leading-relaxed">
                    Seluruh potongan teks di atas telah ter-indeks ke dalam *Vector Store* menggunakan model embedding **NeuronAI**. Dokumen ini siap digunakan sebagai basis pengetahuan kontekstual untuk modul asisten AI keuangan Anda.
                </p>
            </div>
        </div>
    );
}

ShowChunks.layout = page => <AppLayout title="Arsip Pengetahuan RAG" children={page} />;
