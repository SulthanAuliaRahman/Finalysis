import { useRef } from 'react';
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
import { useGenerateProgress } from "@/Hooks/useGenerateProgress";
import { FileDown, Calculator, Loader2, AlertTriangle, CheckCircle2 } from "lucide-react";

export default function Detail({
    perusahaan, analisis, dokumenPeriode,
    likuiditas, profitabilitas, solvabilitas, aktivitas, dupont, commonsize,
    trendRasio, trendDupont, trendCommonsize, trendAkunUtama,
    neraca, labaRugi,
}) {
    const refLikuiditas = useRef(null);
    const refProfitabilitas = useRef(null);
    const refSolvabilitas = useRef(null);
    const refAktivitas = useRef(null);
    const refRasio = useRef(null);
    const refDupont = useRef(null);
    const refCommonsize = useRef(null);
    const refTrendRasio = useRef(null);
    const refTrendDupont = useRef(null);
    const refTrendCommonsize = useRef(null);

    const safeNama = perusahaan.nama.replace(/[^a-zA-Z0-9]/g, '_');
    const safePeriode = analisis.periode_label.replace(/[^a-zA-Z0-9]/g, '_');
    const fileName = `Analisis_${safeNama}_${safePeriode}.pdf`;

    const [progress, startPolling] = useGenerateProgress(perusahaan.id, analisis.id, {
        status_generate: analisis.status_generate,
        progress_current: analisis.progress_current,
        progress_total: analisis.progress_total,
        error_message: analisis.error_message,
        section_status: analisis.section_status,
        semua_selesai: analisis.semua_selesai,
        ai_summary_insight: analisis.ai_summary_insight,
    });

    const { isGenerating, generatePdf } = usePdfGenerator({
        pdfProps: { perusahaan, analisis, neraca, labaRugi, likuiditas, profitabilitas, solvabilitas, aktivitas, dupont, commonsize, trendAkunUtama, trendRasio, trendDupont, trendCommonsize, fileName },
        chartRefs: {
            likuiditas: refLikuiditas, profitabilitas: refProfitabilitas, solvabilitas: refSolvabilitas,
            aktivitas: refAktivitas, rasio: refRasio, dupont: refDupont, commonsize: refCommonsize,
            trendRasio: refTrendRasio, trendDupont: refTrendDupont, trendCommonsize: refTrendCommonsize,
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
        router.post(`/perusahaan/${perusahaan.id}/analisis/${analisis.id}/generate`, {}, {
            preserveScroll: true,
            onSuccess: startPolling,
        });
    }

    const isProcessing = progress.status_generate === 'processing';
    const isGagal = progress.status_generate === 'gagal';
    const isSelesai = progress.semua_selesai;

    // props seragam yang diteruskan ke SEMUA card section
    const cardProps = (section) => ({
        sectionStatus: progress.section_status?.[section],
        canRegenerasi: isSelesai,
        onRegenerasiStart: startPolling,
    });

    return (
        <div className="p-8">
            <div className="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 className="text-3xl font-semibold text-slate-900">
                        Detail Analisis Pada Periode {analisis.periode_label}
                    </h2>
                    <p className="text-slate-500 mt-1">Ringkasan dan insight keuangan perusahaan</p>
                </div>
                <div className="flex gap-2">
                    {isSelesai ? (
                        <span className="flex items-center gap-1.5 px-4 py-2 text-emerald-600 text-sm font-medium">
                            <CheckCircle2 className="w-4 h-4" /> Sudah di-generate
                        </span>
                    ) : isProcessing ? (
                        <button disabled className="flex items-center gap-2 px-4 py-2 bg-blue-400 text-white rounded-lg text-sm font-medium cursor-not-allowed">
                            <Loader2 className="w-4 h-4 animate-spin" /> Memproses {progress.progress_current}/{progress.progress_total}...
                        </button>
                    ) : (
                        <button onClick={handleGenerateAnalisis} className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                            <Calculator className="w-4 h-4" /> {isGagal ? 'Coba lagi (section yang belum)' : 'Generate analisis'}
                        </button>
                    )}
                    {analisis.ai_summary_insight && (
                        <button onClick={handleDownloadPdf} disabled={isGenerating} className="flex items-center gap-2 px-4 py-2 border border-slate-200 rounded-lg hover:bg-slate-50 text-sm font-medium text-slate-700 disabled:opacity-50">
                            {isGenerating ? <Loader2 className="w-4 h-4 animate-spin" /> : <FileDown className="w-4 h-4" />}
                            Unduh PDF
                        </button>
                    )}
                </div>
            </div>

            {isGagal && (
                <div className="mb-6 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-start gap-2 text-amber-700">
                    <AlertTriangle className="w-4 h-4 shrink-0 mt-0.5" />
                    <p className="text-sm">
                        Sebagian section belum berhasil di-generate. Klik <strong>"Coba lagi"</strong> untuk memproses ulang yang masih kosong.
                    </p>
                </div>
            )}

            <CompanyHeader perusahaan={perusahaan} dokumenPeriode={dokumenPeriode} />

            <div className="mb-8">
                <h3 className="font-semibold text-slate-900 mb-4">Rasio Keuangan</h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <AnalisisLikuiditasCard ref={refLikuiditas} data={likuiditas} neraca={neraca} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} {...cardProps('likuiditas')} />
                    <AnalisisProfitabilitasCard ref={refProfitabilitas} data={profitabilitas} neraca={neraca} labaRugi={labaRugi} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} {...cardProps('profitabilitas')} />
                    <AnalisisSolvabilitasCard ref={refSolvabilitas} data={solvabilitas} neraca={neraca} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} {...cardProps('solvabilitas')} />
                    <AnalisisAktivitasCard ref={refAktivitas} data={aktivitas} neraca={neraca} labaRugi={labaRugi} perusahaanId={perusahaan.id} analisisId={analisis.id} sektor={perusahaan.sektor} {...cardProps('aktivitas')} />
                </div>
            </div>

            <div className="mb-8">
                <h3 className="font-semibold text-slate-900 mb-4">Analisis Struktural</h3>
                <div className="grid grid-cols-1 gap-6">
                    <AnalisisDupontCard ref={refDupont} data={dupont} profitabilitas={profitabilitas} aktivitas={aktivitas} solvabilitas={solvabilitas} neraca={neraca} labaRugi={labaRugi} perusahaanId={perusahaan.id} analisisId={analisis.id} {...cardProps('dupont')} />
                    <AnalisisCommonsizeCard ref={refCommonsize} data={commonsize} perusahaanId={perusahaan.id} analisisId={analisis.id} {...cardProps('commonsize')} />
                </div>
            </div>

            <div className="mb-8">
                <h3 className="font-semibold text-slate-900 mb-4">Analisis Tren</h3>
                <div className="grid grid-cols-1 gap-6">
                    <TrendAkunUtamaCard data={trendAkunUtama} perusahaanId={perusahaan.id} analisisId={analisis.id} {...cardProps('trend_akun_utama')} />
                    <TrendRasioCard ref={refTrendRasio} data={trendRasio} perusahaanId={perusahaan.id} analisisId={analisis.id} {...cardProps('trend_rasio')} />
                    <TrendDupontCard ref={refTrendDupont} data={trendDupont} perusahaanId={perusahaan.id} analisisId={analisis.id} {...cardProps('trend_dupont')} />
                    <TrendCommonsizeCard ref={refTrendCommonsize} data={trendCommonsize} perusahaanId={perusahaan.id} analisisId={analisis.id} {...cardProps('trend_commonsize')} />
                </div>
            </div>

            <div className="flex justify-center">
                <div className="w-full">
                    <AIInsightCard narasi={analisis.ai_summary_insight} perusahaanId={perusahaan.id} analisisId={analisis.id} {...cardProps('summary')} />
                </div>
            </div>
        </div>
    );
}

Detail.layout = page => <AppLayout title="Detail Analisis" children={page} />;
