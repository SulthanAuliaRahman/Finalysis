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
import { TrendArusKasCard } from "@/Components/Analisis/TrendArusKasCard";
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
    trendArusKas,
    neraca,
    labaRugi,
    referensiDokumen,
}) {
    const [isCalculating, setIsCalculating] = useState(false);

    const refRasio      = useRef(null);
    const refDupont     = useRef(null);
    const refCommonsize = useRef(null);
    const refAkunUtama  = useRef(null);
    const refArusKas    = useRef(null);

    const safeNama    = perusahaan.nama.replace(/[^a-zA-Z0-9]/g, '_');
    const safePeriode = analisis.periode_label.replace(/[^a-zA-Z0-9]/g, '_');
    const fileName    = `Analisis_${safeNama}_${safePeriode}.pdf`;

    // FIX: lowercase 'u' — sesuai nama export di usePdfGenerator.jsx
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
            trendArusKas,
            fileName,
        },
        chartRefs: {
            rasio:      refRasio,
            dupont:     refDupont,
            commonsize: refCommonsize,
            akunUtama:  refAkunUtama,
            arusKas:    refArusKas,
        },
    });

    function handleDownloadPdf() {
        if (!analisis.ai_summary_insight) {
            alert('Generate Executive Summary terlebih dahulu sebelum mengunduh PDF.');
            return;
        }
        generatePdf();
    }

    function handleHitungRasio() {
        setIsCalculating(true);
        router.post(
            `/perusahaan/${perusahaan.id}/analisis/${analisis.id}/hitung-rasio`,
            {},
            {
                preserveScroll: true,
                onFinish: () => setIsCalculating(false),
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
                        disabled={isGenerating}
                        className="flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium flex-shrink-0 disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        {isGenerating
                            ? <Loader2 className="w-4 h-4 animate-spin" />
                            : <FileDown className="w-4 h-4" />}
                        {isGenerating ? 'Membuat PDF...' : 'Download PDF'}
                    </button>
                </div>
            </div>

            <CompanyHeader perusahaan={perusahaan} dokumenPeriode={dokumenPeriode} />

            <div className="mb-8">
                <h3 className="font-semibold text-slate-900 mb-4">Rasio Keuangan</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <AnalisisLikuiditasCard data={likuiditas} neraca={neraca} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} referenceDocuments={referensiDokumen} />
                    <AnalisisProfitabilitasCard data={profitabilitas} neraca={neraca} labaRugi={labaRugi} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} referenceDocuments={referensiDokumen} />
                    <AnalisisSolvabilitasCard data={solvabilitas} neraca={neraca} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} referenceDocuments={referensiDokumen} />
                    <AnalisisAktivitasCard data={aktivitas} neraca={neraca} labaRugi={labaRugi} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} referenceDocuments={referensiDokumen} />
                </div>
            </div>

            <div className="mb-8">
                <h3 className="font-semibold text-slate-900 mb-4">Analisis Struktural</h3>
                <div className="grid grid-cols-1 gap-6">
                    <AnalisisDupontCard data={dupont} neraca={neraca} labaRugi={labaRugi} perusahaanId={perusahaan.id} analisisId={analisis.id} referenceDocuments={referensiDokumen} />
                    <AnalisisCommonsizeCard data={commonsize} perusahaanId={perusahaan.id} analisisId={analisis.id} referenceDocuments={referensiDokumen} />
                </div>
            </div>

            <div className="mb-8">
                <h3 className="font-semibold text-slate-900 mb-4">Analisis Tren</h3>
                <div className="grid grid-cols-1 gap-6">
                    <TrendAkunUtamaCard data={trendAkunUtama} perusahaanId={perusahaan.id} analisisId={analisis.id} referenceDocuments={referensiDokumen} />
                    <TrendRasioCard data={trendRasio} perusahaanId={perusahaan.id} analisisId={analisis.id} referenceDocuments={referensiDokumen} />
                    <TrendDupontCard data={trendDupont} perusahaanId={perusahaan.id} analisisId={analisis.id} referenceDocuments={referensiDokumen} />
                    <TrendCommonsizeCard data={trendCommonsize} perusahaanId={perusahaan.id} analisisId={analisis.id} referenceDocuments={referensiDokumen} />
                    <TrendArusKasCard data={trendArusKas} perusahaanId={perusahaan.id} analisisId={analisis.id} referenceDocuments={referensiDokumen} />
                </div>
            </div>

            <div className="flex justify-center">
                <div className="w-full max-w-4xl">
                    <AIInsightCard narasi={analisis.ai_summary_insight} perusahaanId={perusahaan.id} analisisId={analisis.id} referenceDocuments={referensiDokumen} />
                </div>
            </div>
        </div>
    );
}

Detail.layout = page => <AppLayout title="Detail Analisis" children={page} />;
