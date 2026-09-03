import { useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from "@/Layouts/AppLayout";
import { CompanyHeader } from "@/Components/Analisis/CompanyHeader";
import { AnalisisLikuiditasCard } from "@/Components/Analisis/AnalisisLikuiditasCard";
import { AnalisisProfitabilitasCard } from "@/Components/Analisis/AnalisisProfitabilitasCard";
import { AnalisisSolvabilitasCard } from "@/Components/Analisis/AnalisisSolvabilitasCard";
import { AnalisisAktivitasCard } from "@/Components/Analisis/AnalisisAktivitasCard";
import { AnalisisDupontCard } from "@/Components/Analisis/AnalisisDupontCard";
import { AnalisisCommonsizeCard } from "@/Components/Analisis/AnalisisCommonsizeCard";
import { TrendAkunUtamaCard } from "@/Components/Analisis/TrendAkunUtamaCard";
import { TrendRasioCard } from "@/Components/Analisis/TrendRasioCard";
import { TrendDupontCard } from "@/Components/Analisis/TrendDupontCard";
import { TrendCommonsizeCard } from "@/Components/Analisis/TrendCommonsizeCard";
import { AIInsightCard } from "@/Components/Analisis/AIInsightCard";
import { usePdfGenerator } from "@/Components/Analisis/PDF/usePdfGenerator";
import { FileDown, Calculator, Loader2 } from "lucide-react";

export default function Detail({
    perusahaan,
    analisis,
    dokumenPeriode,
    likuiditas,
    profitabilitas,
    solvabilitas,
    aktivitas,
    dupont,
    commonsize,
    trendRasio,
    trendDupont,
    trendCommonsize,
    trendAkunUtama,
    neraca,
    labaRugi,
}) {
    const [isCalculating, setIsCalculating] = useState(false);

    const refLikuiditas    = useRef(null);
    const refProfitabilitas = useRef(null);
    const refSolvabilitas  = useRef(null);
    const refAktivitas     = useRef(null);
    const refRasio         = useRef(null);
    const refDupont        = useRef(null);
    const refCommonsize    = useRef(null);

    const refTrendRasio  = useRef(null);
    const refTrendDupont  = useRef(null);
    const refTrendCommonsize  = useRef(null);

    const safeNama    = perusahaan.nama.replace(/[^a-zA-Z0-9]/g, '_');
    const safePeriode = analisis.periode_label.replace(/[^a-zA-Z0-9]/g, '_');
    const fileName    = `Analisis_${safeNama}_${safePeriode}.pdf`;

    const { isGenerating, generatePdf } = usePdfGenerator({
        pdfProps: {
            perusahaan,
            analisis,
            neraca,
            labaRugi,
            likuiditas,
            profitabilitas,
            solvabilitas,
            aktivitas,
            dupont,
            commonsize,
            trendAkunUtama,
            trendRasio,
            trendDupont,
            trendCommonsize,

            fileName,
        },
        chartRefs: {
            likuiditas:     refLikuiditas,
            profitabilitas: refProfitabilitas,
            solvabilitas:   refSolvabilitas,
            aktivitas:      refAktivitas,
            rasio:          refRasio,
            dupont:         refDupont,
            commonsize:     refCommonsize,
            trendRasio:         refTrendRasio,
            trendDupont:        refTrendDupont,
            trendCommonsize:    refTrendCommonsize,
        },
    });

    function handleDownloadPdf() {
        if (!analisis.ai_summary_insight) {
            alert('Generate Executive Summary terlebih dahulu sebelum mengunduh PDF.');
            return;
        }
        generatePdf();
    }

    function handleGenerateAnalisis() {
        // TODO: belum diimplementasi — nanti panggil endpoint generate
        // Route::post('/perusahaan/{perusahaan}/analisis/{analisis}/generate')
        alert('Fitur Generate Analisis masih dalam pengembangan.');
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
                    </div>
                </div>
                <div className="flex gap-2">
                    {analisis.ai_summary_insight ? (
                        <button
                            onClick={handleHitungRasio}
                            disabled={isCalculating}
                            className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex-shrink-0 disabled:opacity-50"
                        >
                            {isCalculating ? <Loader2 className="w-4 h-4 animate-spin" /> : <Calculator className="w-4 h-4" />}
                            Generate analisis
                        </button>
                    ) : (
                        <button
                            onClick={handleGenerateAnalisis}
                            disabled={isCalculating}
                            className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex-shrink-0 disabled:opacity-50"
                        >
                            {isCalculating ? <Loader2 className="w-4 h-4 animate-spin" /> : <Calculator className="w-4 h-4" />}
                            Generate analisis
                        </button>
                    )}
                </div>
            </div>

            <CompanyHeader perusahaan={perusahaan} dokumenPeriode={dokumenPeriode} />

            <div className="mb-8">
                <h3 className="font-semibold text-slate-900 mb-4">Rasio Keuangan</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <AnalisisLikuiditasCard ref={refLikuiditas} data={likuiditas} neraca={neraca} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} />
                    <AnalisisProfitabilitasCard ref={refProfitabilitas} data={profitabilitas} neraca={neraca} labaRugi={labaRugi} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} />
                    <AnalisisSolvabilitasCard ref={refSolvabilitas} data={solvabilitas} neraca={neraca} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} />
                    <AnalisisAktivitasCard ref={refAktivitas} data={aktivitas} neraca={neraca} labaRugi={labaRugi} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} />
                </div>
            </div>

            <div className="mb-8">
                <h3 className="font-semibold text-slate-900 mb-4">Analisis Struktural</h3>
                <div className="grid grid-cols-1 gap-6">
                    <AnalisisDupontCard
                        ref={refDupont}data={dupont}profitabilitas={profitabilitas} aktivitas={aktivitas} solvabilitas={solvabilitas}
                        neraca={neraca}labaRugi={labaRugi}perusahaanId={perusahaan.id} analisisId={analisis.id}
                    />
                    <AnalisisCommonsizeCard ref={refCommonsize} data={commonsize} perusahaanId={perusahaan.id} analisisId={analisis.id} />
                </div>
            </div>

            <div className="mb-8">
                <h3 className="font-semibold text-slate-900 mb-4">Analisis Tren</h3>
                <div className="grid grid-cols-1 gap-6">
                    <TrendAkunUtamaCard data={trendAkunUtama} perusahaanId={perusahaan.id} analisisId={analisis.id} />
                    <TrendRasioCard ref={refTrendRasio} data={trendRasio} perusahaanId={perusahaan.id} analisisId={analisis.id}/>
                    <TrendDupontCard ref={refTrendDupont} data={trendDupont} perusahaanId={perusahaan.id} analisisId={analisis.id} />
                    <TrendCommonsizeCard ref={refTrendCommonsize} data={trendCommonsize} perusahaanId={perusahaan.id} analisisId={analisis.id} />
                </div>
            </div>

            <div className="flex justify-center">
                <div className="w-full">
                    <AIInsightCard narasi={analisis.ai_summary_insight} perusahaanId={perusahaan.id} analisisId={analisis.id} />
                </div>
            </div>
        </div>
    );
}

Detail.layout = page => <AppLayout title="Detail Analisis" children={page} />;
