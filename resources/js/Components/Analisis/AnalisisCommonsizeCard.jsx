import { useState } from 'react';
import { router } from '@inertiajs/react';
import { PieChart, RefreshCw, Loader2, Sparkles } from 'lucide-react';

function PercentBar({ label, value, color = 'bg-teal-500' }) {
    const hasValue = value !== null && value !== undefined;
    const width = hasValue ? Math.min(Math.abs(value), 100) : 0;
    return (
        <div className="mb-2.5 last:mb-0">
            <div className="flex items-center justify-between mb-1">
                <span className="text-xs text-slate-600">{label}</span>
                <span className="text-xs font-semibold text-slate-900">
                    {hasValue ? `${value}%` : '—'}
                </span>
            </div>
            <div className="h-2 bg-slate-100 rounded-full overflow-hidden">
                <div className={`h-full ${color} rounded-full transition-all`} style={{ width: `${width}%` }} />
            </div>
        </div>
    );
}

export function AnalisisCommonsizeCard({ data, perusahaanId, analisisId }) {
    const [isLoading, setIsLoading] = useState(false);
    const belumDianalisis = !data?.narasi_commonsize_AI;

    function handleTrigger() {
        setIsLoading(true);
        router.post(
            `/perusahaan/${perusahaanId}/analisis/${analisisId}/regenerasi`,
            { section: 'commonsize' },
            { preserveScroll: true, onFinish: () => setIsLoading(false) }
        );
    }

    return (
        <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
            <div className="flex items-center justify-between mb-4">
                <div className="flex items-center gap-2.5">
                    <div className="p-2 rounded-lg bg-teal-100">
                        <PieChart className="w-5 h-5 text-teal-600" />
                    </div>
                    <h3 className="font-semibold text-slate-900">Common-Size Analysis</h3>
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

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-4">
                <div>
                    <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2.5">
                        Laba Rugi (basis Pendapatan)
                    </p>
                    <PercentBar label="HPP" value={data?.hpp_persen} color="bg-orange-400" />
                    <PercentBar label="Laba Kotor" value={data?.laba_kotor_persen} color="bg-teal-500" />
                    <PercentBar label="Beban Lain & Pajak" value={data?.beban_lain_pajak_persen} color="bg-red-400" />
                    <PercentBar label="Laba Bersih" value={data?.laba_bersih_persen} color="bg-green-500" />
                </div>
                <div>
                    <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2.5">
                        Neraca (basis Total Aset)
                    </p>
                    <PercentBar label="Aset Lancar" value={data?.aset_lancar_persen} color="bg-blue-400" />
                    <PercentBar label="Aset Tetap" value={data?.aset_tetap_persen} color="bg-indigo-400" />
                    <PercentBar label="Liabilitas Lancar" value={data?.liabilitas_lancar_persen} color="bg-amber-400" />
                    <PercentBar label="Liabilitas Jk. Panjang" value={data?.liabilitas_panjang_persen} color="bg-orange-500" />
                    <PercentBar label="Ekuitas" value={data?.ekuitas_persen} color="bg-purple-400" />
                </div>
            </div>

            {belumDianalisis ? (
                <div className="bg-slate-50/70 border border-dashed border-slate-200 rounded-lg p-4 text-center">
                    <p className="text-xs text-slate-400">
                        Analisis common-size belum pernah dijalankan pada periode ini.
                    </p>
                </div>
            ) : (
                <div className="bg-blue-50/50 border border-blue-100 rounded-lg p-3">
                    <div className="flex items-center gap-1.5 mb-1.5">
                        <Sparkles className="w-3.5 h-3.5 text-blue-500" />
                        <span className="text-xs font-medium text-blue-700">Insight AI</span>
                    </div>
                    <p className="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{data.narasi_commonsize_AI}</p>
                </div>
            )}
        </div>
    );
}