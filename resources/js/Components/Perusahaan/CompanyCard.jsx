import { Link, router } from "@inertiajs/react";
import { Building2, FileText, BarChart3, Edit2, Trash2 } from "lucide-react";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { cn } from "@/lib/utils";

const SEKTOR_COLOR = {
    Manufaktur: "bg-amber-50 text-amber-700 border-amber-200",
    Jasa: "bg-blue-50 text-blue-700 border-blue-200",
    Perdagangan: "bg-pink-50 text-pink-700 border-pink-200",
    Lainnya: "bg-slate-50 text-slate-700 border-slate-200",
};

export default function CompanyCard({ perusahaan }) {
    const formattedDate = perusahaan.created_at ? perusahaan.created_at.slice(0, 10) : "—";

    function handleDelete() {
        if (confirm(`Apakah Anda yakin ingin menghapus ${perusahaan.nama}? Seluruh dokumen RAG terkait akan ikut terhapus.`)) {
            router.delete(`/perusahaan/${perusahaan.id}`);
        }
    }

    return (
        <div className="bg-white border border-slate-200 rounded-xl p-5 flex flex-col gap-4 hover:border-blue-300 hover:shadow-xs transition-all duration-200">
            {/* Header: Ikon, Nama, dan Badge Sektor */}
            <div className="flex items-start justify-between gap-2">
                <div className="flex items-center gap-3 min-w-0">
                    <div className="flex-shrink-0 w-10 h-10 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center">
                        <Building2 className="w-5 h-5 text-slate-500" />
                    </div>
                    <div className="min-w-0">
                        <h3 className="text-slate-900 font-semibold text-sm truncate">
                            {perusahaan.nama}
                        </h3>
                        <p className="text-xs text-slate-400">Dibuat: {formattedDate}</p>
                    </div>
                </div>
                <Badge variant="outline" className={cn("flex-shrink-0 text-[10px] font-semibold border", SEKTOR_COLOR[perusahaan.sektor] || SEKTOR_COLOR["Lainnya"])}>
                    {perusahaan.sektor}
                </Badge>
            </div>

            {/* Deskripsi */}
            {perusahaan.deskripsi && (
                <p className="text-xs text-slate-600 leading-relaxed line-clamp-2">
                    {perusahaan.deskripsi}
                </p>
            )}

            {/* Counter Sederhana (Informasional) */}
            <div className="flex items-center gap-1.5 text-xs text-slate-500 pt-3 mt-auto border-t border-slate-100">
                <FileText className="w-3.5 h-3.5" />
                <span className="font-semibold text-slate-700">{perusahaan.dokumen_count ?? 0}</span> berkas dokumen
            </div>

            {/* Kumpulan Tombol Aksi (2x2 Grid) */}
            <div className="grid grid-cols-2 gap-2 pt-2">
                <Link href={`/perusahaan/${perusahaan.id}/dokumen`} className="w-full">
                    <Button variant="outline" size="sm" className="w-full gap-1.5 h-8 text-xs text-blue-600 hover:text-blue-700 hover:bg-blue-50 border-blue-200">
                        <FileText className="w-3 h-3" /> Dokumen
                    </Button>
                </Link>

                <Link href={`/perusahaan/${perusahaan.id}/analisis`} className="w-full">
                    <Button variant="outline" size="sm" className="w-full gap-1.5 h-8 text-xs text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 border-indigo-200">
                        <BarChart3 className="w-3 h-3" /> Analisis
                    </Button>
                </Link>

                <Link href={`/perusahaan/${perusahaan.id}/edit`} className="w-full">
                    <Button variant="outline" size="sm" className="w-full gap-1.5 h-8 text-xs">
                        <Edit2 className="w-3 h-3" /> Edit
                    </Button>
                </Link>

                <Button
                    variant="outline"
                    size="sm"
                    className="w-full gap-1.5 h-8 text-xs text-red-600 hover:text-red-700 hover:bg-red-50 border-slate-200"
                    onClick={handleDelete}
                >
                    <Trash2 className="w-3 h-3" /> Hapus
                </Button>
            </div>
        </div>
    );
}
