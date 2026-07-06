import { useState } from 'react';
import { router } from '@inertiajs/react';
import { RefreshCw, Sparkles, Loader2 } from 'lucide-react';

/**
 * RatioCardBase
 *
 * Struktur dasar yang dipakai oleh AnalisisLikuiditasCard, AnalisisProfitabilitasCard,
 * AnalisisSolvabilitasCard, dan AnalisisAktivitasCard.
 *
 * - Jika `narasi` null/kosong -> tampilkan empty state + tombol "Mulai Analisis"
 * - Jika `narasi` sudah ada -> tampilkan rasio + narasi + tombol "Regenerasi"
 * - Tombol memanggil POST ke endpoint regenerasi per section (via Inertia router.post)
 *
 * ratios: array of { label: string, value: number|string|null, suffix?: string }
 */
export function RatioCardBase({
    title,
    icon,
    iconBgColor,
    iconColor,
    ratios,
    narasi,
    section,
    perusahaanId,
    analisisId,
}) {
    const [isLoading, setIsLoading] = useState(false);

    const belumDianalisis = !narasi;

    function handleTrigger() {
        setIsLoading(true);
        router.post(
            `/perusahaan/${perusahaanId}/analisis/${analisisId}/regenerasi`,
            { section },
            {
                preserveScroll: true,
                onFinish: () => setIsLoading(false),
            }
        );
    }

    return (
        <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
            <div className="flex items-center justify-between mb-4">
                <div className="flex items-center gap-2.5">
                    <div className={`p-2 rounded-lg ${iconBgColor}`}>
                        <span className={iconColor}>{icon}</span>
                    </div>
                    <h3 className="font-semibold text-slate-900">{title}</h3>
                </div>
                <button
                    onClick={handleTrigger}
                    disabled={isLoading}
                    className="flex items-center gap-1.5 px-2.5 py-1 border border-slate-200 rounded-lg text-slate-500 hover:bg-slate-50 transition-colors text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {isLoading ? (
                        <Loader2 className="w-3.5 h-3.5 animate-spin" />
                    ) : (
                        <RefreshCw className="w-3.5 h-3.5" />
                    )}
                    {belumDianalisis ? 'Mulai Analisis' : 'Regenerasi'}
                </button>
            </div>

            <div className="space-y-2.5 mb-4">
                <div className="flex-1">
                    {ratios.map((ratio, idx) => (
                        <div key={idx} className="flex flex-col mb-4 last:mb-0 border-b border-slate-100 last:border-0 pb-3 last:pb-0">
                            <div className="flex items-center justify-between mb-1.5">
                                <span className="text-sm text-slate-600 font-medium">{ratio.label}</span>
                                <span className={`text-sm font-bold ${ratio.value !== null && ratio.value !== undefined ? 'text-slate-900' : 'text-slate-300'}`}>
                                    {ratio.value !== null && ratio.value !== undefined ? `${ratio.value}${ratio.suffix || ''}` : '—'}
                                </span>
                            </div>

                            {/* Menampilkan Breakdown Rumus dan Angka Mentah */}
                            {ratio.breakdown && ratio.value !== null && ratio.value !== undefined && (
                                <div className="p-2.5 bg-slate-50 border border-slate-100 rounded-lg text-[11px] font-mono text-slate-500 space-y-1">
                                    <div className="flex gap-2">
                                        <span className="text-slate-400 font-sans w-12 shrink-0">Rumus:</span>
                                        <span className="text-blue-600">{ratio.formula}</span>
                                    </div>
                                    <div className="flex gap-2">
                                        <span className="text-slate-400 font-sans w-12 shrink-0">Data:</span>
                                        <span className="text-slate-700">{ratio.breakdown}</span>
                                    </div>
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            </div>

            <div className="h-32 rounded-lg bg-slate-50 border border-dashed border-slate-200 flex items-center justify-center mb-4">
                <span className="text-xs text-slate-400">Grafik tren akan tampil di sini</span>
            </div>

            {belumDianalisis ? (
                <div className="bg-slate-50/70 border border-dashed border-slate-200 rounded-lg p-4 text-center">
                    <p className="text-xs text-slate-400">
                        Analisis untuk {title.toLowerCase()} belum pernah dijalankan pada periode ini.
                    </p>
                </div>
            ) : (
                <div className="bg-blue-50/50 border border-blue-100 rounded-lg p-3">
                    <div className="flex items-center gap-1.5 mb-1.5">
                        <Sparkles className="w-3.5 h-3.5 text-blue-500" />
                        <span className="text-xs font-medium text-blue-700">Insight AI</span>
                    </div>
                    <p className="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{narasi}</p>
                </div>
            )}
        </div>
    );
}
