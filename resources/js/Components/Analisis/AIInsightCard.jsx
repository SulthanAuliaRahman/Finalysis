import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Sparkles, RefreshCw, Loader2 } from 'lucide-react';

export function AIInsightCard({ narasi, perusahaanId, analisisId }) {
    const [isLoading, setIsLoading] = useState(false);
    const belumDianalisis = !narasi;

    function handleTrigger() {
        setIsLoading(true);
        router.post(
            `/perusahaan/${perusahaanId}/analisis/${analisisId}/regenerasi`,
            { section: 'summary' },
            {
                preserveScroll: true,
                onFinish: () => setIsLoading(false),
            }
        );
    }

    return (
        <div className="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-xl p-6">
            <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2.5">
                    <div className="p-2 bg-white rounded-lg shadow-xs">
                        <Sparkles className="w-5 h-5 text-blue-600" />
                    </div>
                    <h3 className="font-semibold text-slate-900">Summary & Insight</h3>
                </div>
                <button
                    onClick={handleTrigger}
                    disabled={isLoading}
                    className="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-blue-200 rounded-lg text-blue-700 hover:bg-blue-50 transition-colors text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {isLoading ? (
                        <Loader2 className="w-3.5 h-3.5 animate-spin" />
                    ) : (
                        <RefreshCw className="w-3.5 h-3.5" />
                    )}
                    {belumDianalisis ? 'Mulai Analisis' : 'Regenerasi'}
                </button>
            </div>

            {belumDianalisis ? (
                <div className="bg-white/60 border border-dashed border-blue-200 rounded-lg p-5 text-center">
                    <p className="text-sm text-slate-400 italic">
                        Ringkasan akan tersedia setelah terdapat analisi.
                    </p>
                </div>
            ) : (
                <div className="bg-white/70 border border-blue-100 rounded-lg p-5">
                    <p className="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{narasi}</p>
                </div>
            )}
        </div>
    );
}
