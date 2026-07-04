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
import { FileDown, Calculator, Loader2 } from "lucide-react";
import { router } from '@inertiajs/react';
import { useState } from 'react';

export default function Detail({ perusahaan, analisis, dokumenPeriode, likuiditas, profitabilitas, solvabilitas, aktivitas, dupont, commonsize, trend, neraca, labaRugi }) {
    const [isCalculating, setIsCalculating] = useState(false);

    function handleDownloadPdf() {
        console.log(`Download PDF analisis periode ${analisis.periode_label} untuk perusahaan ${perusahaan.id}`);
    }

    function handleHitungRasio() {
        setIsCalculating(true);
        router.post(
            `/perusahaan/${perusahaan.id}/analisis/${analisis.id}/hitung-rasio`,
            {},
            {
                preserveScroll: true,
                onFinish: () => setIsCalculating(false)
            }
        );
    }

    return (
        <div className="p-8">
            <div className="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 className="text-3xl font-semibold text-slate-900">
                        Detail Analisis Pada Periode {analisis.periode_label}
                    </h2>
                    <div className="flex items-center gap-3 mt-1">
                        <p className="text-slate-500">Ringkasan dan insight keuangan perusahaan</p>
                        <span className="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full font-medium border border-blue-200">
                            Status: {analisis.status}
                        </span>
                    </div>
                </div>
                <div className="flex gap-2">
                    <button
                        onClick={handleHitungRasio}
                        disabled={isCalculating}
                        className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex-shrink-0 disabled:opacity-50"
                    >
                        {isCalculating ? <Loader2 className="w-4 h-4 animate-spin" /> : <Calculator className="w-4 h-4" />}
                        Hitung Data Finansial
                    </button>
                    <button
                        onClick={handleDownloadPdf}
                        className="flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium flex-shrink-0"
                    >
                        <FileDown className="w-4 h-4" />
                        Download PDF
                    </button>
                </div>
            </div>

            <CompanyHeader perusahaan={perusahaan} dokumenPeriode={dokumenPeriode} />

            <div className="mb-8">
                <h3 className="font-semibold text-slate-900 mb-4">Rasio Keuangan</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {/* Tambahkan prop sektor ke masing-masing Card */}
                    <AnalisisLikuiditasCard data={likuiditas} neraca={neraca} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} />
                    <AnalisisProfitabilitasCard data={profitabilitas} neraca={neraca} labaRugi={labaRugi} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} />
                    <AnalisisSolvabilitasCard data={solvabilitas} neraca={neraca} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} />
                    <AnalisisAktivitasCard data={aktivitas} neraca={neraca} labaRugi={labaRugi} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} />

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
