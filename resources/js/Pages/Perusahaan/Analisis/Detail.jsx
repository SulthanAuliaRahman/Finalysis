import AppLayout from "@/Layouts/AppLayout";
import { CompanyHeader } from "@/Components/Analisis/CompanyHeader";
import { AnalisisLikuiditasCard } from "@/Components/Analisis/AnalisisLikuiditasCard";
import { AnalisisProfitabilitasCard } from "@/Components/Analisis/AnalisisProfitabilitasCard";
import { AnalisisSolvabilitasCard } from "@/Components/Analisis/AnalisisSolvabilitasCard";
import { AnalisisAktivitasCard } from "@/Components/Analisis/AnalisisAktivitasCard";
import { AIInsightCard } from "@/Components/Analisis/AIInsightCard";
import { Button } from "@/Components/ui/button"; // Pastikan path ini sesuai
import { Download } from "lucide-react";

export default function Detail({ perusahaan, periodeLabel }) {

    // TODO: hubungkan ke endpoint regenerasi per-kategori rasio saat backend siap.
    function handleRegenerateRatio(kategori) {
        console.log(`Regenerasi rasio ${kategori} untuk perusahaan ${perusahaan.id}`);
    }

    function handleRegenerateInsight() {
        console.log(`Regenerasi AI insight untuk perusahaan ${perusahaan.id}`);
    }

    // Placeholder untuk fitur Download PDF
    function handleDownloadPdf() {
        console.log("Tombol Download PDF diklik (UI Only)");
    }

    return (
        <div className="p-8">
            {/* Wrapper Flexbox: Judul di kiri, Tombol di kanan */}
            <div className="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <h2 className="text-3xl font-semibold text-slate-900">
                        Detail Analisis Pada Periode {periodeLabel}
                    </h2>
                    <p className="text-slate-500 mt-1">Ringkasan dan insight keuangan perusahaan</p>
                </div>

                {/* Button Download PDF */}
                <Button
                    onClick={handleDownloadPdf}
                    variant="outline"
                    className="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 border-slate-200 shadow-xs shrink-0"
                >
                    <Download className="w-4 h-4" />
                    Download PDF
                </Button>
            </div>

            <CompanyHeader />

            <div className="mb-8">
                <h3 className="font-semibold text-slate-900 mb-4">Rasio Keuangan</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <AnalisisLikuiditasCard onRegenerate={() => handleRegenerateRatio('likuiditas')} />
                    <AnalisisProfitabilitasCard onRegenerate={() => handleRegenerateRatio('profitabilitas')} />
                    <AnalisisSolvabilitasCard onRegenerate={() => handleRegenerateRatio('solvabilitas')} />
                    <AnalisisAktivitasCard onRegenerate={() => handleRegenerateRatio('aktivitas')} />
                </div>
            </div>

            <div className="flex justify-center">
                <div className="w-full max-w-4xl">
                    <AIInsightCard onRegenerate={handleRegenerateInsight} />
                </div>
            </div>
        </div>
    );
}

Detail.layout = page => <AppLayout title="Detail Analisis" children={page} />;
