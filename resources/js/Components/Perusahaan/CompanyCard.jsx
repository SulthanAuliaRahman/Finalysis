import { Building2, FileText, BarChart3, Edit2, Trash2, Database, ChartLine } from "lucide-react";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { cn } from "@/lib/utils";

const SEKTOR_COLOR = {
    Manufaktur: "bg-amber-50 text-amber-700 border-amber-200",
    Teknologi: "bg-blue-50 text-blue-700 border-blue-200",
    Energi: "bg-violet-50 text-violet-700 border-violet-200",
    Keuangan: "bg-green-50 text-green-700 border-green-200",
    Properti: "bg-orange-50 text-orange-700 border-orange-200",
    Retail: "bg-pink-50 text-pink-700 border-pink-200",
    Pertanian: "bg-lime-50 text-lime-700 border-lime-200",
    Kesehatan: "bg-cyan-50 text-cyan-700 border-cyan-200",
    Lainnya: "bg-slate-50 text-slate-700 border-slate-200",
};

export default function CompanyCard({ perusahaan }) {
    return (
        <div className="bg-white border border-slate-200 rounded-xl p-5 flex flex-col gap-4 hover:border-blue-300 hover:shadow-sm transition-all duration-200">
            {/* Header Card (Icon, Nama, Sektor) */}
            <div className="flex items-start justify-between gap-2">
                <div className="flex items-center gap-3 min-w-0">
                    <div className="flex-shrink-0 w-10 h-10 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center">
                        <Building2 className="w-5 h-5 text-slate-500" />
                    </div>
                    <div className="min-w-0">
                        <h3 className="text-slate-900 truncate font-semibold text-sm">
                            {perusahaan.nama}
                        </h3>
                        <p className="text-xs text-slate-500">{perusahaan.tanggalDibuat}</p>
                    </div>
                </div>
                <Badge
                    variant="outline"
                    className={cn("flex-shrink-0 text-[10px] font-semibold tracking-wide border", SEKTOR_COLOR[perusahaan.sektor] || SEKTOR_COLOR["Lainnya"])}
                >
                    {perusahaan.sektor}
                </Badge>
            </div>

            {/* Deskripsi */}
            {perusahaan.deskripsi && (
                <p className="text-xs text-slate-600 leading-relaxed line-clamp-2">
                    {perusahaan.deskripsi}
                </p>
            )}

            {/* Statistik (Dokumen & Analisis) */}
            <div className="flex items-center gap-4 pt-3 mt-auto border-t border-slate-100">
                <div className="flex items-center gap-1.5 text-xs text-slate-500">
                    <FileText className="w-3.5 h-3.5" />
                    <span>{perusahaan.dokCount || 0} dokumen</span>
                </div>
                <div className="flex items-center gap-1.5 text-xs text-slate-500">
                    <BarChart3 className="w-3.5 h-3.5" />
                    <span>{perusahaan.analisisCount || 0} analisis</span>
                </div>

                {/* Skor Kesehatan Perusahaan*/}
                {/* No Need? */}
                {/* {perusahaan.skorKesehatan && (
                    <div className="ml-auto flex items-center gap-1 px-1.5 py-0.5 rounded bg-blue-50 border border-blue-100">
                        <span className="text-[11px] font-bold text-blue-700 font-mono">
                            {perusahaan.skorKesehatan}
                        </span>
                    </div>
                )} */}
            </div>

            {/* Aksi*/}
            <div className="flex gap-2 pt-1">
                <Button variant="outline" size="sm" className="flex-1 gap-1.5" onClick={() => console.log('Edit clicked', perusahaan.id)}>
                    <ChartLine className="w-3 h-3" /> Analisis
                </Button>
                <Button variant="outline" size="sm" className="flex-1 gap-1.5" onClick={() => console.log('Edit clicked', perusahaan.id)}>
                    <Database className="w-3 h-3" /> Dokumen
                </Button>
                <Button variant="outline" size="sm" className="flex-1 gap-1.5" onClick={() => console.log('Edit clicked', perusahaan.id)}>
                    <Edit2 className="w-3 h-3" /> Edit
                </Button>
                <Button variant="outline" size="sm" className="flex-1 gap-1.5 text-red-600 hover:text-red-700 hover:bg-red-50 border-slate-200" onClick={() => console.log('Hapus clicked', perusahaan.id)}>
                    <Trash2 className="w-3 h-3" /> Hapus
                </Button>
            </div>
        </div>
    );
}
