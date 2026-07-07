import { useState } from 'react';
import { router } from '@inertiajs/react';
import { RefreshCw, Loader2, Sparkles, X, AlertTriangle } from 'lucide-react';


export function NarasiBlock({ title, text }) {
    if (!text) {
        return (
            <div className="bg-slate-50/70 border border-dashed border-slate-200 rounded-lg p-3 text-center mb-4">
                <p className="text-xs text-slate-400">Belum ada narasi {title.toLowerCase()} untuk periode ini.</p>
            </div>
        );
    }
    return (
        <div className="bg-blue-50/50 border border-blue-100 rounded-lg p-3 mb-4">
            <div className="flex items-center gap-1.5 mb-1.5">
                <Sparkles className="w-3.5 h-3.5 text-blue-500" />
                <span className="text-xs font-medium text-blue-700">Insight AI — {title}</span>
            </div>
            <p className="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{text}</p>
        </div>
    );
}

// TrendCardBase — shell untuk semua TrendCard
export function TrendCardBase({
    title,
    icon,
    iconBgColor,
    iconColor,
    section,
    narasi,
    narasiLabel,
    hasGap = false,
    dataKurang = false,
    perusahaanId,
    analisisId,
    children,
}) {
    const [isLoading, setIsLoading] = useState(false);
    const [isPromptModalOpen, setIsPromptModalOpen] = useState(false);
    const [userPrompt, setUserPrompt] = useState('');

    const belumDianalisis = !narasi;

    function handleTrigger() {
        if (belumDianalisis) {
            submitAnalisis();
        } else {
            setIsPromptModalOpen(true);
        }
    }

    function submitAnalisis(customPrompt = '') {
        setIsLoading(true);
        router.post(
            `/perusahaan/${perusahaanId}/analisis/${analisisId}/regenerasi`,
            { section, user_prompt: customPrompt },
            {
                preserveScroll: true,
                onFinish: () => {
                    setIsLoading(false);
                    setIsPromptModalOpen(false);
                    setUserPrompt('');
                },
            }
        );
    }

    return (
        <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-xs relative">
            {/* Header */}
            <div className="flex items-center justify-between mb-4">
                <div className="flex items-center gap-2.5">
                    <div className={`p-2 rounded-lg ${iconBgColor}`}>
                        <span className={iconColor}>{icon}</span>
                    </div>
                    <h3 className="font-semibold text-slate-900">{title}</h3>
                </div>
                <button
                    onClick={handleTrigger}
                    disabled={isLoading || dataKurang}
                    className="flex items-center gap-1.5 px-2.5 py-1 border border-slate-200 rounded-lg text-slate-500 hover:bg-slate-50 transition-colors text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {isLoading
                        ? <Loader2 className="w-3.5 h-3.5 animate-spin" />
                        : <RefreshCw className="w-3.5 h-3.5" />
                    }
                    {belumDianalisis ? 'Mulai Analisis' : 'Regenerasi'}
                </button>
            </div>

            {/* Warning: ada gap data di tengah periode */}
            {hasGap && !dataKurang && (
                <div className="flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-lg p-2.5 mb-4">
                    <AlertTriangle className="w-3.5 h-3.5 text-amber-500 mt-0.5 flex-shrink-0" />
                    <p className="text-[11px] text-amber-700 leading-relaxed">
                        Terdapat periode tidak memiliki data. Hasil analisis mungkin tidak mencerminkan tren yang sesungguhnya.
                    </p>
                </div>
            )}

            {/* Empty state: data kurang */}
            {dataKurang ? (
                <div className="bg-slate-50/70 border border-dashed border-slate-200 rounded-lg p-4 text-center">
                    <p className="text-xs text-slate-400">
                        Dibutuhkan minimal 2 periode data untuk menampilkan analisis tren.
                    </p>
                </div>
            ) : (
                <>
                    {/* Content dari child card */}
                    {children}

                    {/* Narasi AI */}
                    <NarasiBlock title={narasiLabel} text={narasi} />
                </>
            )}

            {/* Modal Regenerasi dengan Prompt */}
            {isPromptModalOpen && (
                <div className="absolute inset-0 z-10 flex items-center justify-center bg-white/60 backdrop-blur-sm rounded-xl p-4">
                    <div className="bg-white border border-slate-200 shadow-xl rounded-xl p-5 w-full">
                        <div className="flex justify-between items-center mb-3">
                            <h4 className="text-sm font-semibold text-slate-900">Regenerasi {title}</h4>
                            <button
                                onClick={() => setIsPromptModalOpen(false)}
                                className="text-slate-400 hover:text-slate-700"
                            >
                                <X className="w-4 h-4" />
                            </button>
                        </div>
                        <p className="text-xs text-slate-500 mb-3">
                            Berikan instruksi tambahan ke AI (opsional)
                        </p>
                        <textarea
                            className="w-full border border-slate-200 rounded-lg p-3 text-sm focus:ring-blue-500 focus:border-blue-500 mb-4 resize-none h-24"
                            placeholder="Contoh: Fokuskan pada penurunan rasio di tahun terakhir..."
                            value={userPrompt}
                            onChange={(e) => setUserPrompt(e.target.value)}
                            disabled={isLoading}
                        />
                        <div className="flex justify-end gap-2">
                            <button
                                onClick={() => setIsPromptModalOpen(false)}
                                className="px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg"
                                disabled={isLoading}
                            >
                                Batal
                            </button>
                            <button
                                onClick={() => submitAnalisis(userPrompt)}
                                className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg disabled:opacity-50"
                                disabled={isLoading}
                            >
                                {isLoading && <Loader2 className="w-3 h-3 animate-spin" />}
                                Generate AI
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
