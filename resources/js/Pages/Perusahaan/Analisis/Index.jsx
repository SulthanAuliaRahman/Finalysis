import { Link, router } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import {
    Trash2, CheckCircle2, AlertTriangle,
    Circle, Building2, Eye, FileText, CalendarRange
} from "lucide-react";

function StatusBadge({ status }) {
    const badges = {
        belum_dimulai: {
            bg: "bg-slate-100 border-slate-200 text-slate-600",
            icon: <Circle className="w-3 h-3" />,
            label: "Belum Dimulai"
        },
        sudah_dianalisis: {
            bg: "bg-green-50 border-green-200 text-green-700",
            icon: <CheckCircle2 className="w-3 h-3" />,
            label: "Sudah Dianalisis"
        },
        perubahan_data: {
            bg: "bg-orange-50 border-orange-200 text-orange-700",
            icon: <AlertTriangle className="w-3 h-3" />,
            label: "Terjadi Perubahan Data!"
        }
    };
    const current = badges[status] || badges['belum_dimulai'];
    return (
        <span className={`inline-flex items-center gap-1 px-2 py-0.5 rounded-md border text-xs font-medium ${current.bg}`}>
            {current.icon} {current.label}
        </span>
    );
}

function TipePeriodeBadge({ tipe }) {
    const labels = {
        quarterly: "Kuartalan",
        monthly: "Bulanan",
        annual: "Tahunan"
    };
    return (
        <Badge variant="outline" className="font-normal text-slate-500">
            {labels[tipe] || tipe}
        </Badge>
    );
}

export default function Index({ perusahaan, analisisList }) {

    function handleDelete(analisisId) {
        if (confirm("Apakah Anda yakin ingin menghapus analisis ini? Hasil analisis yang sudah dibuat akan ikut terhapus.")) {
            router.delete(`/perusahaan/${perusahaan.id}/analisis/${analisisId}`);
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
            </div>

            <div className="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                {analisisList.length === 0 ? (
                    <div className="text-center py-16 text-slate-400 text-sm flex flex-col items-center gap-2">
                        <CalendarRange className="w-8 h-8 text-slate-300" />
                        Belum ada analisis tersedia. Unggah dokumen laporan keuangan terlebih dahulu.
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm text-left">
                            <thead>
                                <tr className="border-b border-slate-200 bg-slate-50/50 text-slate-500 text-xs uppercase font-semibold">
                                    <th className="px-5 py-3.5">Periode Analisis</th>
                                    <th className="px-5 py-3.5">Tipe</th>
                                    <th className="px-5 py-3.5">Jumlah Dokumen</th>
                                    <th className="px-5 py-3.5">Status</th>
                                    <th className="px-5 py-3.5"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100 text-slate-700">
                                {analisisList.map((analisis) => (
                                    <tr key={analisis.id} className="hover:bg-slate-50/40 transition-colors">
                                        <td className="px-5 py-4 flex items-center gap-3">
                                            <div className="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center flex-shrink-0">
                                                <CalendarRange className="w-4 h-4 text-blue-600" />
                                            </div>
                                            <span className="font-medium text-slate-900">{analisis.periode_label}</span>
                                        </td>
                                        <td className="px-5 py-4">
                                            <TipePeriodeBadge tipe={analisis.tipe_periode} />
                                        </td>
                                        <td className="px-5 py-4">
                                            <span className="inline-flex items-center gap-1.5 text-slate-600">
                                                <FileText className="w-3.5 h-3.5 text-slate-400" />
                                                {analisis.jumlah_dokumen} dokumen
                                            </span>
                                        </td>
                                        <td className="px-5 py-4">
                                            <StatusBadge status={analisis.status} />
                                        </td>
                                        <td className="px-5 py-4">
                                            <div className="flex items-center justify-end gap-1.5">
                                                <Link href={`/perusahaan/${perusahaan.id}/analisis/${analisis.id}`}>
                                                    <Button size="sm" variant="outline" className="h-7 text-xs gap-1">
                                                        <Eye className="w-3 h-3" /> Lihat Detail
                                                    </Button>
                                                </Link>
                                                <Button variant="ghost" size="icon" onClick={() => handleDelete(analisis.id)}>
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

Index.layout = page => <AppLayout title="Analisis Laporan Keuangan" children={page} />;
