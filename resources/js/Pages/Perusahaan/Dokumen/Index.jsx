import { Link, router } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { Upload, FileText, Trash2, Building2, Eye } from "lucide-react";

const BULAN_LABELS = {
    1: "Januari", 2: "Februari", 3: "Maret", 4: "April", 5: "Mei", 6: "Juni",
    7: "Juli", 8: "Agustus", 9: "September", 10: "Oktober", 11: "November", 12: "Desember",
};

// Format kolom Periode dari periode_type + tahun + quarter/bulan
function formatPeriode(dokumen) {
    if (dokumen.periode_type === "quarterly") {
        return `Q${dokumen.quarter} ${dokumen.tahun}`;
    }
    if (dokumen.periode_type === "monthly") {
        return `${BULAN_LABELS[dokumen.bulan] ?? dokumen.bulan} ${dokumen.tahun}`;
    }
    return `Tahunan ${dokumen.tahun}`;
}

function formatTanggal(value) {
    if (!value) return "-";
    return new Date(value).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

export default function Index({ perusahaan, dokumenList }) {
    function handleDelete(documentId) {
        if (confirm("Apakah Anda yakin ingin menghapus berkas laporan keuangan ini beserta seluruh data hasil ekstraksinya?")) {
            router.delete(`/perusahaan/${perusahaan.id}/dokumen/${documentId}`);
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
                                    <th className="px-5 py-3.5">Diunggah</th>
                                    <th className="px-5 py-3.5"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 text-slate-700">
                                {dokumenList.map((dokumen) => (
                                    <tr key={dokumen.id} className="hover:bg-slate-50/40 transition-colors">
                                        <td className="px-5 py-4">
                                            <Link
                                                href={`/perusahaan/${perusahaan.id}/dokumen/${dokumen.id}/detail`}
                                                className="flex items-center gap-3 group w-fit"
                                            >
                                                <div className="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center flex-shrink-0">
                                                    <FileText className="w-4 h-4 text-blue-600" />
                                                </div>
                                                <span className="font-mono text-xs text-slate-900 font-medium truncate max-w-xs group-hover:underline">
                                                    {dokumen.nama_file}
                                                </span>
                                            </Link>
                                        </td>
                                        <td className="px-5 py-4">
                                            <Badge variant="outline">{formatPeriode(dokumen)}</Badge>
                                        </td>
                                        <td className="px-5 py-4 text-xs text-slate-500">
                                            {formatTanggal(dokumen.created_at)}
                                        </td>
                                        <td className="px-5 py-4 text-right whitespace-nowrap">
                                            <div className="flex items-center justify-end gap-1.5">
                                                <Link href={`/perusahaan/${perusahaan.id}/dokumen/${dokumen.id}/detail`}>
                                                    <Button size="sm" variant="ghost" className="h-7 text-xs text-slate-500 hover:text-slate-700 gap-1">
                                                        <Eye className="w-3 h-3" /> Detail
                                                    </Button>
                                                </Link>
                                                <Button variant="ghost" size="icon" onClick={() => handleDelete(dokumen.id)}>
                                                    <Trash2 className="w-3.5 h-3.5" />
                                                </Button>
                                            </div>
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
