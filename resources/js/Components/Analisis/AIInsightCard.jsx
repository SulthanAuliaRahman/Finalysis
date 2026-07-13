import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Sparkles, RefreshCw, Loader2, X } from 'lucide-react';
import { ReferenceButton } from './ReferenceButton';

export function AIInsightCard({ narasi, perusahaanId, analisisId, referenceDocuments }) {
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
            { section: 'summary', user_prompt: customPrompt },
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
        <div className="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-xl p-6 relative overflow-hidden">
            <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2.5">
                    <div className="p-2 bg-white rounded-lg shadow-xs">
                        <Sparkles className="w-5 h-5 text-blue-600" />
                    </div>
                    <h3 className="font-semibold text-slate-900">Summary & Insight</h3>
                </div>
                <div className="flex items-center gap-2">
                    <ReferenceButton documents={referenceDocuments} section="summary" />
                    <button
                        onClick={handleTrigger}
                        disabled={isLoading}
                        className="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-blue-200 rounded-lg text-blue-700 hover:bg-blue-50 transition-colors text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed z-0"
                    >
                        {isLoading ? (
                            <Loader2 className="w-3.5 h-3.5 animate-spin" />
                        ) : (
                            <RefreshCw className="w-3.5 h-3.5" />
                        )}
                        {belumDianalisis ? 'Mulai Analisis' : 'Regenerasi'}
                    </button>
                </div>
            </div>

            {belumDianalisis ? (
                <div className="bg-white/60 border border-dashed border-blue-200 rounded-lg p-5 text-center">
                    <p className="text-sm text-slate-400 italic">
                        Ringkasan akan tersedia setelah terdapat analisis.
                    </p>
                </div>
            ) : (
                <div className="bg-white/70 border border-blue-100 rounded-lg p-5">
                    <p className="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{narasi}</p>
                </div>
            )}

            {/* Modal Prompt Regenerasi */}
            {isPromptModalOpen && (
                <div className="absolute inset-0 z-10 flex items-center justify-center bg-white/60 backdrop-blur-sm p-4">
                    <div className="bg-white border border-slate-200 shadow-xl rounded-xl p-5 w-full max-w-lg mx-auto">
                        <div className="flex justify-between items-center mb-3">
                            <h4 className="text-sm font-semibold text-slate-900">Regenerasi Summary & Insight</h4>
                            <button onClick={() => setIsPromptModalOpen(false)} className="text-slate-400 hover:text-slate-700 transition-colors">
                                <X className="w-4 h-4" />
                            </button>
                        </div>
                        <p className="text-xs text-slate-500 mb-3">
                            Berikan instruksi tambahan ke AI (misal: "Fokuskan pada tren profitabilitas yang menurun dan berikan rekomendasi aksi").
                        </p>
                        <textarea
                            className="w-full border border-slate-200 rounded-lg p-3 text-sm focus:ring-blue-500 focus:border-blue-500 mb-4 resize-none h-24"
                            placeholder="Instruksi Opsional..."
                            value={userPrompt}
                            onChange={(e) => setUserPrompt(e.target.value)}
                            disabled={isLoading}
                        ></textarea>
                        <div className="flex justify-end gap-2">
                            <button
                                onClick={() => setIsPromptModalOpen(false)}
                                className="px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                                disabled={isLoading}
                            >
                                Batal
                            </button>
                            <button
                                onClick={() => submitAnalisis(userPrompt)}
                                className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg disabled:opacity-50 transition-colors"
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
