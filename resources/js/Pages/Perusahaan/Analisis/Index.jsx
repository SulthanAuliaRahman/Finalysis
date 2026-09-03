import { Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Building2, FileText, CalendarRange, Sparkles } from "lucide-react";

export default function Index({ perusahaan, analisisList }) {
    const grouped = Object.groupBy(analisisList, (a) => a.tahun);
    const tahunList = Object.keys(grouped).sort((a, b) => b - a);

    return (
        <div className="space-y-8">
            <div className="flex items-center gap-2.5 bg-blue-50 border border-blue-100 px-4 py-2 rounded-lg text-blue-800 w-fit">
                <Building2 className="w-4 h-4 text-blue-600" />
                <span className="text-sm font-semibold tracking-wide">
                    <span className="text-blue-500 font-normal">Perusahaan:</span> {perusahaan.nama}
                </span>
            </div>

            {analisisList.length === 0 ? (
                <div className="text-center py-16 text-slate-400 text-sm flex flex-col items-center gap-2 border border-slate-200 rounded-xl bg-white">
                    <CalendarRange className="w-8 h-8 text-slate-300" />
                    Belum ada analisis tersedia. Unggah dokumen laporan keuangan terlebih dahulu.
                </div>
            ) : (
                tahunList.map((tahun) => (
                    <div key={tahun}>
                        <h3 className="text-sm font-semibold text-slate-500 mb-3">{tahun}</h3>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            {grouped[tahun].map((analisis) => (
                                <Link
                                    key={analisis.id}
                                    href={`/perusahaan/${perusahaan.id}/analisis/${analisis.id}`}
                                    className="border border-slate-200 rounded-xl p-4 bg-white hover:border-blue-300 hover:shadow-sm transition-all"
                                >
                                    <div className="flex items-center justify-between mb-2">
                                        <span className="font-medium text-slate-900">{analisis.periode_label}</span>
                                        {analisis.sudah_diringkas && (
                                            <Sparkles className="w-3.5 h-3.5 text-blue-500" />
                                        )}
                                    </div>
                                    <p className="text-xs text-slate-500 truncate flex items-center gap-1.5">
                                        <FileText className="w-3 h-3 text-slate-400" />
                                        {analisis.nama_file}
                                    </p>
                                </Link>
                            ))}
                        </div>
                    </div>
                ))
            )}
        </div>
    );
}

Index.layout = page => <AppLayout title="Analisis Laporan Keuangan" children={page} />;
