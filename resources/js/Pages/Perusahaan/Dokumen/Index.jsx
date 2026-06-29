import { Link, router } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import {
    Upload, FileText, Trash2, CheckCircle2,
    Clock, Loader2, Building2, ArrowRight, Eye
} from "lucide-react";

function StatusBadge({ status }) {
    const badges = {
        menunggu: { bg: "bg-slate-100 border-slate-200 text-slate-600", icon: <Clock className="w-3 h-3" />, label: "Menunggu" },
        diekstrak: { bg: "bg-blue-50 border-blue-200 text-blue-700", icon: <Loader2 className="w-3 h-3 text-blue-500 animate-spin" />, label: "Diekstrak" },
        dichunk: { bg: "bg-amber-50 border-amber-200 text-amber-700", icon: <FileText className="w-3 h-3 text-amber-500" />, label: "Dichunk" },
        diembed: { bg: "bg-indigo-50 border-indigo-200 text-indigo-700", icon: <Loader2 className="w-3 h-3 text-indigo-500 animate-pulse" />, label: "Diembed" },
        selesai: { bg: "bg-green-50 border-green-200 text-green-700", icon: <CheckCircle2 className="w-3 h-3" />, label: "Selesai" }
    };
    const current = badges[status] || badges['menunggu'];
    return (
        <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-md border text-xs font-medium ${current.bg}`}>
            {current.icon} {current.label}
        </span>
    );
}

export default function Index({ perusahaan, dokumenList }) {

    function handleDelete(documentId) {
        if (confirm("Apakah Anda yakin ingin menghapus berkas laporan keuangan ini beserta seluruh data olahan AI di dalamnya?")) {
            router.delete(`/perusahaan/${perusahaan.id}/dokumen/${documentId}`);
            // router.delete(`/dokumen/${id}`);


        }
    }

    // Fungsi Render Tombol Aksi Dinamis Kontekstual Berdasarkan Status Dokumen
    function renderActionButtons(doc) {
        switch (doc.status) {
            case "menunggu":
                return null; // Hanya menampilkan tombol hapus bawaan di ujung kanan

            case "diekstrak":
                return (
                    <Link href={`/perusahaan/${perusahaan.id}/dokumen/${doc.id}/review`}>
                        <Button size="sm" variant="outline" className="h-7 text-xs text-blue-600 border-blue-200 bg-blue-50/50 hover:bg-blue-50 gap-1">
                            Lanjut Review <ArrowRight className="w-3 h-3" />
                        </Button>
                    </Link>
                );

            case "dichunk":
                return (
                    <Link href={`/perusahaan/${perusahaan.id}/dokumen/${doc.id}/embed`}>
                        <Button size="sm" variant="outline" className="h-7 text-xs text-amber-700 border-amber-200 bg-amber-50/50 hover:bg-amber-50 gap-1">
                            Lanjut Embed <ArrowRight className="w-3 h-3" />
                        </Button>
                    </Link>
                );

            case "diembed":
            case "selesai":
                return (
                    <Link href={`/perusahaan/${perusahaan.id}/dokumen/${doc.id}/chunks`}>
                        <Button size="sm" variant="outline" className="h-7 text-xs text-slate-700 gap-1">
                            <Eye className="w-3 h-3" /> Lihat Chunks
                        </Button>
                    </Link>
                );

            default:
                return null;
        }
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row gap-4 sm:items-center justify-between">
                <div className="flex items-center gap-2.5 bg-blue-50 border border-blue-100 px-4 py-2 rounded-lg text-blue-800">
                    <Building2 className="w-4 h-4 text-blue-600" />
                    <span className="text-sm font-semibold tracking-wide">
                        <span className="text-blue-500 font-normal">Perusahaan:</span> {perusahaan.nama}
                    </span>
                </div>

                <Link href={`/perusahaan/${perusahaan.id}/dokumen/create`}>
                    <Button className="gap-2 shadow-xs">
                        <Upload className="w-4 h-4" /> Unggah Dokumen Baru
                    </Button>
                </Link>
            </div>

            <div className="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                {dokumenList.length === 0 ? (
                    <div className="text-center py-16 text-slate-400 text-sm">
                        Belum ada berkas dokumen laporan keuangan di perusahaan ini.
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm text-left">
                            <thead>
                                <tr className="border-b border-slate-200 bg-slate-50/50 text-slate-500 text-xs uppercase font-semibold">
                                    <th className="px-5 py-3.5">Nama File</th>
                                    <th className="px-5 py-3.5">Periode</th>
                                    <th className="px-5 py-3.5">Ukuran</th>
                                    <th className="px-5 py-3.5">Status</th>
                                    <th className="px-5 py-3.5">Progres AI</th>
                                    <th className="px-5 py-3.5"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 text-slate-700">
                                {dokumenList.map((dokumen) => (
                                    <tr key={dokumen.id} className="hover:bg-slate-50/40 transition-colors">
                                        <td className="px-5 py-4 flex items-center gap-3">
                                            <div className="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center flex-shrink-0">
                                                <FileText className="w-4 h-4 text-blue-600" />
                                            </div>
                                            <span className="font-mono text-xs text-slate-900 font-medium truncate max-w-xs">{dokumen.nama_file}</span>
                                        </td>
                                        <td className="px-5 py-4"><Badge variant="outline">{dokumen.periode}</Badge></td>
                                        <td className="px-5 py-4 font-mono text-xs text-slate-400">
                                            {dokumen.ukuran_file ? `${(dokumen.ukuran_file / 1024 / 1024).toFixed(2)} MB` : "—"}
                                        </td>
                                        <td className="px-5 py-4"><StatusBadge status={dokumen.status} /></td>

                                        {/* Kolom Tombol Alur Kontekstual Dinamis */}
                                        <td className="px-5 py-4 whitespace-nowrap">{renderActionButtons(dokumen)}</td>
                                        <td className="px-5 py-4 text-right">
                                            <Button variant="ghost" size="icon" onClick={() => handleDelete(dokumen.id)}>
                                                <Trash2 className="w-3.5 h-3.5" />
                                            </Button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </div>
    );
}

Index.layout = page => <AppLayout title="Berkas Laporan Keuangan" children={page} />;
