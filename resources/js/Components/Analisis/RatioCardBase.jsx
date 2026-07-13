import { useState, forwardRef } from 'react';
import { router } from '@inertiajs/react';
import { RefreshCw, Sparkles, Loader2, X, AlertCircle } from 'lucide-react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

export const RatioCardBase = forwardRef(function RatioCardBase({
    title,
    icon,
    iconBgColor,
    iconColor,
    ratios,
    chartData,
    chartColor,
    narasi,
    section,
    perusahaanId,
    analisisId,
}, chartRef) {
    const [isLoading, setIsLoading] = useState(false);
    const [isPromptModalOpen, setIsPromptModalOpen] = useState(false);
    const [userPrompt, setUserPrompt] = useState('');

    const belumDianalisis = !narasi;
    const rasioBelumDihitung = ratios.every(r => r.value === null || r.value === undefined);

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
        <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-xs relative flex flex-col h-full">
            <div className="flex items-center justify-between mb-4">
                <div className="flex items-center gap-2.5">
                    <div className={`p-2 rounded-lg ${iconBgColor}`}>
                        <span className={iconColor}>{icon}</span>
                    </div>
                    <h3 className="font-semibold text-slate-900">{title}</h3>
                </div>
                <button
                    onClick={handleTrigger}
                    disabled={isLoading || rasioBelumDihitung}
                    className="flex items-center gap-1.5 px-2.5 py-1 border border-slate-200 rounded-lg text-slate-500 hover:bg-slate-50 transition-colors text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {isLoading ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : <RefreshCw className="w-3.5 h-3.5" />}
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

                            {/* --- AREA BREAKDOWN YANG DIPERBARUI --- */}
                            {ratio.breakdown && ratio.value !== null && ratio.value !== undefined && (
                                <div className="p-2.5 bg-slate-50 border border-slate-100 rounded-lg text-[11px] font-mono text-slate-500 space-y-1.5">
                                    <div className="flex gap-2">
                                        <span className="text-slate-400 font-sans w-14 shrink-0">Rumus:</span>
                                        <span className="text-blue-600">{ratio.formula}</span>
                                    </div>
                                    <div className="flex gap-2">
                                        <span className="text-slate-400 font-sans w-14 shrink-0">Angka:</span>
                                        <span className="text-slate-700">{ratio.breakdown}</span>
                                    </div>

                                    {/* Menambahkan Row Hasil Raw jika ada */}
                                    {ratio.rawResult && (
                                        <div className="flex gap-2 mt-1 pt-1.5 border-t border-slate-200 border-dashed">
                                            <span className="text-slate-500 font-sans font-medium w-14 shrink-0">Hasil:</span>
                                            <span className="text-slate-800 font-bold bg-white px-1.5 rounded border border-slate-200">
                                                {ratio.rawResult}
                                            </span>
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            </div>

            {/* Diagram Render Area */}
            {!rasioBelumDihitung && chartData && chartData.length > 0 && (
                <div ref={chartRef} className="h-56 mb-4 w-full">
                    <ResponsiveContainer width="100%" height="100%">
                        <BarChart data={chartData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                            <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f5f9" />
                            <XAxis dataKey="name" axisLine={false} tickLine={false} tick={{ fontSize: 11, fill: '#64748b' }} />
                            <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 11, fill: '#64748b' }} />
                            <Tooltip
                                cursor={{ fill: '#f8fafc' }}
                                contentStyle={{ borderRadius: '8px', border: 'none', boxShadow: '0 4px 6px -1px rgb(0 0 0 / 0.1)', fontSize: '12px' }}
                            />
                            <Legend wrapperStyle={{ fontSize: '11px', paddingTop: '10px' }} />
                            <Bar dataKey="value" name="Nilai Aktual" fill={chartColor || '#3b82f6'} radius={[4, 4, 0, 0]} />
                            <Bar dataKey="benchmark" name="Benchmark" fill="#cbd5e1" radius={[4, 4, 0, 0]} />
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            )}

            <div className="mt">
                {rasioBelumDihitung ? (
                    <div className="bg-amber-50/70 border border-amber-200 rounded-lg p-3 flex gap-2 items-start text-amber-700">
                        <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
                        <p className="text-xs">
                            Silakan klik <strong>"Hitung Data Finansial"</strong> di bagian atas untuk mengkalkulasi rasio sebelum melakukan analisis AI.
                        </p>
                    </div>
                ) : belumDianalisis ? (
                    <div className="bg-slate-50/70 border border-dashed border-slate-200 rounded-lg p-4 text-center">
                        <p className="text-xs text-slate-400">
                            Analisis untuk {title.toLowerCase()} belum pernah dijalankan pada periode ini.
                        </p>
                    </div>
                ) : (
                    <div className="bg-blue-50/50 border border-blue-100 rounded-lg p-3">
                        <div className="flex items-center gap-1.5 mb-1.5">
                            <Sparkles className="w-3.5 h-3.5 text-blue-500" />
                            <span className="text-xs font-medium text-blue-700">Narasi AI</span>
                        </div>
                        <p className="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{narasi}</p>
                    </div>
                )}
            </div>

            {isPromptModalOpen && (
                <div className="absolute inset-0 z-10 flex items-center justify-center bg-white/60 backdrop-blur-sm rounded-xl p-4">
                    <div className="bg-white border border-slate-200 shadow-xl rounded-xl p-5 w-full">
                        <div className="flex justify-between items-center mb-3">
                            <h4 className="text-sm font-semibold text-slate-900">Regenerasi {title}</h4>
                            <button onClick={() => setIsPromptModalOpen(false)} className="text-slate-400 hover:text-slate-700">
                                <X className="w-4 h-4" />
                            </button>
                        </div>
                        <p className="text-xs text-slate-500 mb-3">Berikan instruksi tambahan ke AI (misal: "Fokuskan pada penurunan rasio bulan ini")</p>
                        <textarea
                            className="w-full border border-slate-200 rounded-lg p-3 text-sm focus:ring-blue-500 focus:border-blue-500 mb-4 resize-none h-24"
                            placeholder="Instruksi Opsional..."
                            value={userPrompt}
                            onChange={(e) => setUserPrompt(e.target.value)}
                            disabled={isLoading}
                        ></textarea>
                        <div className="flex justify-end gap-2">
                            <button onClick={() => setIsPromptModalOpen(false)} className="px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg" disabled={isLoading}>Batal</button>
                            <button onClick={() => submitAnalisis(userPrompt)} className="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-blue-600 text-white hover:bg-blue-700 rounded-lg disabled:opacity-50" disabled={isLoading}>
                                {isLoading && <Loader2 className="w-3 h-3 animate-spin" />} Generate AI
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
});
