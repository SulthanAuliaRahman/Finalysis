import { Layers, RefreshCw, Loader2, Sparkles } from 'lucide-react';
import { ReferenceButton } from './ReferenceButton';
import { useState, forwardRef } from 'react';
import { router } from '@inertiajs/react';
import { BarChart, Bar, XAxis, ResponsiveContainer, LabelList, Cell } from 'recharts';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);

const getRawDecimal = (val) => {
    if (val == null) return null;
    return Number(val / 100).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
};

const NPM_SKALA_MAX = 30;
const TATO_SKALA_MAX = 2;
const LEVERAGE_SKALA_MAX = 3;

function normalisasi(value, max) {
    if (value === null || value === undefined) return 0;
    return Math.min((value / max) * 100, 100);
}

export const AnalisisDupontCard = forwardRef(function AnalisisDupontCard({ data, neraca, labaRugi, perusahaanId, analisisId, referenceDocuments }, ref) {
    const [isLoading, setIsLoading] = useState(false);
    const belumDianalisis = !data?.narasi_dupont_AI;

    function handleTrigger() {
        setIsLoading(true);
        router.post(
            `/perusahaan/${perusahaanId}/analisis/${analisisId}/regenerasi`,
            { section: 'dupont' },
            { preserveScroll: true, onFinish: () => setIsLoading(false) }
        );
    }

    const chartData = [
        {
            key: 'npm',
            label: 'Net Profit Margin',
            value: data?.net_profit_margin ?? null,
            unit: '%',
            skala: normalisasi(data?.net_profit_margin, NPM_SKALA_MAX),
            color: '#0d9488',
        },
        {
            key: 'tato',
            label: 'Asset Turnover',
            value: data?.total_asset_turnover ?? null,
            unit: 'x',
            skala: normalisasi(data?.total_asset_turnover, TATO_SKALA_MAX),
            color: '#2563eb',
        },
        {
            key: 'leverage',
            label: 'Leverage Factor',
            value: data?.leverage_multiplier ?? null,
            unit: 'x',
            skala: normalisasi(data?.leverage_multiplier, LEVERAGE_SKALA_MAX),
            color: '#ea580c',
        },
    ];

    const adaData = chartData.some((d) => d.value !== null);

    const ratios = [
        {
            label: 'Net Profit Margin (NPM)',
            value: data?.net_profit_margin ?? null, suffix: '%',
            formula: 'Laba Bersih / Pendapatan',
            breakdown: labaRugi ? `${formatNum(labaRugi.laba_bersih)} / ${formatNum(labaRugi.pendapatan)}` : null,
            rawResult: data?.net_profit_margin != null ? getRawDecimal(data.net_profit_margin) : null,
            rawNote: '(sebelum dikali 100%)' // Tambahan
        },
        {
            label: 'Total Asset Turnover (TATO)',
            value: data?.total_asset_turnover ?? null, suffix: 'x',
            formula: 'Pendapatan / Total Aset',
            breakdown: (labaRugi && neraca) ? `${formatNum(labaRugi.pendapatan)} / ${formatNum(neraca.total_assets)}` : null,
            rawResult: data?.total_asset_turnover != null ? data.total_asset_turnover : null, // Ubah dari null
            rawNote: null // Kosongkan karena bukan persentase
        },
        {
            label: 'Leverage Multiplier',
            value: data?.leverage_multiplier ?? null, suffix: 'x',
            formula: 'Total Aset / Total Ekuitas',
            breakdown: neraca ? `${formatNum(neraca.total_assets)} / ${formatNum(neraca.total_equity)}` : null,
            rawResult: data?.leverage_multiplier != null ? data.leverage_multiplier : null, // Ubah dari null
            rawNote: null
        },
        {
            label: 'Return on Equity (ROE)',
            value: data?.roe ?? null, suffix: '%',
            formula: 'NPM x TATO x Leverage',
            breakdown: data ? `${data.net_profit_margin}% x ${data.total_asset_turnover}x x ${data.leverage_multiplier}x` : null,
            rawResult: data?.roe != null ? getRawDecimal(data.roe) : null,
            rawNote: '(sebelum dikali 100%)'
        },
    ];

    return (
        <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
            {/* Header */}
            <div className="flex items-center justify-between mb-4">
                <div className="flex items-center gap-2.5">
                    <div className="p-2 rounded-lg bg-indigo-100">
                        <Layers className="w-5 h-5 text-indigo-600" />
                    </div>
                    <h3 className="font-semibold text-slate-900">DuPont Analysis</h3>
                </div>
                <div className="flex items-center gap-2">
                    <ReferenceButton documents={referenceDocuments} section="dupont" />
                    <button
                        onClick={handleTrigger}
                        disabled={isLoading}
                        className="flex items-center gap-1.5 px-2.5 py-1 border border-slate-200 rounded-lg text-slate-500 hover:bg-slate-50 transition-colors text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {isLoading ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : <RefreshCw className="w-3.5 h-3.5" />}
                        {belumDianalisis ? 'Mulai Analisis' : 'Regenerasi'}
                    </button>
                </div>
            </div>

            {/* Detail Rumus & Angka Mentah */}
            <div className="space-y-2.5 mb-4">
                {ratios.map((ratio, idx) => (
                    <div key={idx} className="flex flex-col mb-4 last:mb-0 border-b border-slate-100 last:border-0 pb-3 last:pb-0">
                        <div className="flex items-center justify-between mb-1.5">
                            <span className="text-sm text-slate-600 font-medium">{ratio.label}</span>
                            <span className={`text-sm font-bold ${ratio.value !== null ? 'text-slate-900' : 'text-slate-300'}`}>
                                {ratio.value !== null ? `${ratio.value}${ratio.suffix}` : '—'}
                            </span>
                        </div>

                        {ratio.breakdown && ratio.value !== null && (
                            <div className="p-2.5 bg-slate-50 border border-slate-100 rounded-lg text-[11px] font-mono text-slate-500 space-y-1.5">
                                <div className="flex gap-2">
                                    <span className="text-slate-400 font-sans w-14 shrink-0">Rumus:</span>
                                    <span className="text-blue-600">{ratio.formula}</span>
                                </div>
                                <div className="flex gap-2">
                                    <span className="text-slate-400 font-sans w-14 shrink-0">Angka:</span>
                                    <span className="text-slate-700">{ratio.breakdown}</span>
                                </div>

                                {/* Row Hasil Raw yang sudah mendukung TATO & Leverage */}
                                {ratio.rawResult !== null && ratio.rawResult !== undefined && (
                                    <div className="flex gap-2 mt-1 pt-1.5 border-t border-slate-200 border-dashed items-center">
                                        <span className="text-slate-500 font-sans font-medium w-14 shrink-0">Hasil:</span>
                                        <div className="flex items-center gap-1.5">
                                            <span className="text-slate-800 font-bold bg-white px-1.5 py-0.5 rounded border border-slate-200 leading-none">
                                                {ratio.rawResult}
                                            </span>
                                            {/* Note hanya dirender jika ada nilainya */}
                                            {ratio.rawNote && (
                                                <span className="text-slate-400 font-sans italic text-[10px]">
                                                    {ratio.rawNote}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                ))}
            </div>

            {/* Chart: Dekomposisi DuPont */}
            {adaData ? (
                <div className="mb-4">
                    <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2">
                        Dekomposisi Visual
                    </p>
                    <div ref={ref} style={{ width: '100%', height: 180 }}>
                        <ResponsiveContainer>
                            <BarChart data={chartData} margin={{ top: 20, right: 10, left: 10, bottom: 0 }}>
                                <XAxis dataKey="label" tick={{ fontSize: 11 }} axisLine={false} tickLine={false} />
                                <Bar dataKey="skala" radius={[6, 6, 0, 0]} maxBarSize={60}>
                                    {chartData.map((entry) => (
                                        <Cell key={entry.key} fill={entry.color} />
                                    ))}
                                    <LabelList
                                        dataKey="value"
                                        position="top"
                                        formatter={(val, entry) => (val !== null ? `${val}${entry?.unit ?? ''}` : '—')}
                                        style={{ fontSize: 12, fontWeight: 700, fill: '#1e293b' }}
                                    />
                                </Bar>
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                    <p className="text-[10px] text-slate-400 italic text-center mt-1">
                        Batang divisualisasikan pada skala relatif agar mudah dibandingkan; nilai asli tiap komponen ditampilkan di atas batang.
                    </p>

                    {data?.roe !== null && data?.roe !== undefined && (
                        <div className="mt-3 p-2.5 bg-indigo-50 border border-indigo-100 rounded-lg text-center">
                            <span className="text-xs font-semibold text-indigo-700">
                                NPM ({data.net_profit_margin}%) × TATO ({data.total_asset_turnover}x) × Leverage ({data.leverage_multiplier}x) = ROE {data.roe}%
                            </span>
                        </div>
                    )}
                </div>
            ) : (
                <div className="h-32 rounded-lg bg-slate-50 border border-dashed border-slate-200 flex items-center justify-center mb-4">
                    <span className="text-xs text-slate-400">Grafik akan tampil setelah analisis dijalankan</span>
                </div>
            )}

            {/* Narasi AI */}
            {belumDianalisis ? (
                <div className="bg-slate-50/70 border border-dashed border-slate-200 rounded-lg p-4 text-center">
                    <p className="text-xs text-slate-400">Analisis DuPont belum pernah dijalankan pada periode ini.</p>
                </div>
            ) : (
                <div className="bg-blue-50/50 border border-blue-100 rounded-lg p-3">
                    <div className="flex items-center gap-1.5 mb-1.5">
                        <Sparkles className="w-3.5 h-3.5 text-blue-500" />
                        <span className="text-xs font-medium text-blue-700">Narasi AI</span>
                    </div>
                    <p className="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{data.narasi_dupont_AI}</p>
                </div>
            )}
        </div>
    );
});
