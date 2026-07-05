import AppLayout from "@/Layouts/AppLayout";
import { CompanyHeader } from "@/Components/Analisis/CompanyHeader";
import { AnalisisLikuiditasCard } from "@/Components/Analisis/AnalisisLikuiditasCard";
import { AnalisisProfitabilitasCard } from "@/Components/Analisis/AnalisisProfitabilitasCard";
import { AnalisisSolvabilitasCard } from "@/Components/Analisis/AnalisisSolvabilitasCard";
import { AnalisisAktivitasCard } from "@/Components/Analisis/AnalisisAktivitasCard";
import { AnalisisDupontCard } from "@/Components/Analisis/AnalisisDupontCard";
import { AnalisisCommonsizeCard } from "@/Components/Analisis/AnalisisCommonsizeCard";
import { AnalisisTrendCard } from "@/Components/Analisis/AnalisisTrendCard";

import { AIInsightCard } from "@/Components/Analisis/AIInsightCard";
import { FileDown } from "lucide-react";

export default function Detail({ perusahaan, analisis, dokumenPeriode, likuiditas, profitabilitas, solvabilitas, aktivitas, dupont, commonsize, trend, neraca, labaRugi }) {
    // TODO: hubungkan ke endpoint generate/download PDF laporan analisis saat backend siap.
    function handleDownloadPdf() {
        console.log(`Download PDF analisis periode ${analisis.periode_label} untuk perusahaan ${perusahaan.id}`);
    }

    return (
        <div className="p-8">
            <div className="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 className="text-3xl font-semibold text-slate-900">
                        Detail Analisis Pada Periode {analisis.periode_label}
                    </h2>
                    <p className="text-slate-500 mt-1">Ringkasan dan insight keuangan perusahaan</p>
                </div>
                <button
                    onClick={handleDownloadPdf}
                    className="flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium flex-shrink-0"
                >
                    <FileDown className="w-4 h-4" />
                    Download PDF
                </button>
            </div>

            <CompanyHeader perusahaan={perusahaan} dokumenPeriode={dokumenPeriode} />

            <div className="mb-8">
                <h3 className="font-semibold text-slate-900 mb-4">Rasio Keuangan</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <AnalisisLikuiditasCard data={likuiditas} neraca={neraca} perusahaanId={perusahaan.id} analisisId={analisis.id} />
                    <AnalisisProfitabilitasCard data={profitabilitas} neraca={neraca} labaRugi={labaRugi} perusahaanId={perusahaan.id} analisisId={analisis.id} />
                    <AnalisisSolvabilitasCard data={solvabilitas} neraca={neraca} perusahaanId={perusahaan.id} analisisId={analisis.id} />
                    <AnalisisAktivitasCard data={aktivitas} neraca={neraca} labaRugi={labaRugi} perusahaanId={perusahaan.id} analisisId={analisis.id} />
                    
                </div>
            </div>

            <div className="mb-8">
                <h3 className="font-semibold text-slate-900 mb-4">Analisis Struktural & Tren</h3>
                <div className="grid grid-cols-1 gap-6">
                    <AnalisisDupontCard data={dupont} neraca={neraca} labaRugi={labaRugi} perusahaanId={perusahaan.id} analisisId={analisis.id} />
                    <AnalisisCommonsizeCard data={commonsize} perusahaanId={perusahaan.id} analisisId={analisis.id} />
                    <AnalisisTrendCard data={trend} perusahaanId={perusahaan.id} analisisId={analisis.id} />
                </div>
            </div>


            <div className="flex justify-center">
                <div className="w-full max-w-4xl">
                    <AIInsightCard narasi={analisis.ai_summary_insight} perusahaanId={perusahaan.id} analisisId={analisis.id} />
                </div>
            </div>
        </div>
    );
}

Detail.layout = page => <AppLayout title="Detail Analisis" children={page} />;
