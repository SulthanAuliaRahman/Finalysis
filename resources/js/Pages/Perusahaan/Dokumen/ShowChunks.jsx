import { Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { ArrowLeft, CheckCircle2, Download, ShieldCheck } from "lucide-react";
import ChunkViewer from "@/Components/Dokumen/ChunkViewer";

export default function ShowChunks({ perusahaan, dokumen, chunks }) {

    // Fungsi simulasi untuk mendownload full JSON dari data chunks yang ada
    function downloadJSON() {
        const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(chunks, null, 2));
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

            {/* Menggunakan Komponen Reusable */}
            <ChunkViewer chunks={chunks} />

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
