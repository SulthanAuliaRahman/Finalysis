import { PieChart, RefreshCw, Loader2, Sparkles } from 'lucide-react';
import { useState } from 'react';
import { router } from '@inertiajs/react';
import { PieChart as RePieChart, Pie, Cell, ResponsiveContainer, Legend, Tooltip } from 'recharts';

// Format persen: DB kirim string decimal(12,6) mentah (mis. "64.290000"),
// jadi selalu dibulatkan ke 2 desimal sebelum ditampilkan.
const formatPersen = (val) => {
    if (val === null || val === undefined) return null;
    return Number(val).toFixed(2);
};

// Konversi eksplisit ke number — kolom decimal(12,6) Laravel dikembalikan sebagai STRING,
// dan Recharts butuh number asli untuk menghitung sudut tiap slice pie.
const toNum = (val) => (val === null || val === undefined ? null : Number(val));

function PercentBar({ label, value, color = 'bg-teal-500' }) {
    const hasValue = value !== null && value !== undefined;
    const displayValue = formatPersen(value);
    const width = hasValue ? Math.min(Math.max(Number(value), 0), 100) : 0;

    return (
        <div className="mb-4 last:mb-0">
            <div className="flex items-center justify-between mb-1.5">
                <span className="text-sm text-slate-600">{label}</span>
                <span className={`text-sm font-semibold ${hasValue ? 'text-slate-900' : 'text-slate-300'}`}>
                    {hasValue ? `${displayValue}%` : '—'}
                </span>
            </div>
            <div className="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                <div
                    className={`h-full rounded-full ${color}`}
                    style={{ width: `${width}%` }}
                />
            </div>
        </div>
    );
}

function DonutChart({ title, data, height = 200 }) {
    const adaData = data.some((d) => d.value !== null && d.value !== undefined);

    if (!adaData) {
        return (
            <div className="flex-1 min-w-0">
                <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2 text-center">{title}</p>
                <div className="h-40 rounded-lg bg-slate-50 border border-dashed border-slate-200 flex items-center justify-center">
                    <span className="text-xs text-slate-400">Belum ada data</span>
                </div>
            </div>
        );
    }

    return (
        <div className="flex-1 min-w-0">
            <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2 text-center">{title}</p>
            <div style={{ width: '100%', height }}>
                <ResponsiveContainer>
                    <RePieChart>
                        <Pie
                            data={data}
                            dataKey="value"
                            nameKey="label"
                            cx="50%"
                            cy="50%"
                            innerRadius={height * 0.28}
                            outerRadius={height * 0.4}
                            paddingAngle={2}
                        >
                            {data.map((entry) => (
                                <Cell key={entry.label} fill={entry.color} />
                            ))}
                        </Pie>
                        <Tooltip formatter={(val) => `${formatPersen(val)}%`} />
                        <Legend
                            layout="vertical"
                            align="center"
                            verticalAlign="bottom"
                            wrapperStyle={{ fontSize: '11px' }}
                            formatter={(value, entry) => `${value} (${formatPersen(entry.payload.value)}%)`}
                        />
                    </RePieChart>
                </ResponsiveContainer>
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

    const incomeStatementData = [
        { label: 'HPP', value: toNum(data?.hpp_persen), color: '#f97316' },
        { label: 'Beban Lain & Pajak', value: toNum(data?.beban_lain_pajak_persen), color: '#ef4444' },
        { label: 'Laba Bersih', value: toNum(data?.laba_bersih_persen), color: '#16a34a' },
    ];

    const asetData = [
        { label: 'Aset Lancar', value: toNum(data?.aset_lancar_persen), color: '#3b82f6' },
        { label: 'Aset Tetap', value: toNum(data?.aset_tetap_persen), color: '#1e3a8a' },
    ];

    const liabilitasEkuitasData = [
        { label: 'Liabilitas Lancar', value: toNum(data?.liabilitas_lancar_persen), color: '#eab308' },
        { label: 'Liabilitas Jk. Panjang', value: toNum(data?.liabilitas_panjang_persen), color: '#f97316' },
        { label: 'Ekuitas', value: toNum(data?.ekuitas_persen), color: '#a855f7' },
    ];

    return (
        <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
            {/* Header */}
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
                    {isLoading ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : <RefreshCw className="w-3.5 h-3.5" />}
                    {belumDianalisis ? 'Mulai Analisis' : 'Regenerasi'}
                </button>
            </div>

            {/* Data mentah: 2 kolom percent bar, persis seperti gambar referensi */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-1 mb-6">
                <div>
                    <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-3">
                        Laba Rugi (basis Pendapatan)
                    </p>
                    <PercentBar label="HPP" value={data?.hpp_persen ?? null} color="bg-orange-500" />
                    <PercentBar label="Beban Lain & Pajak" value={data?.beban_lain_pajak_persen ?? null} color="bg-red-500" />
                    <PercentBar label="Laba Bersih" value={data?.laba_bersih_persen ?? null} color="bg-green-600" />
                </div>
                <div>
                    <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-3">
                        Neraca (basis Total Aset)
                    </p>
                    <PercentBar label="Aset Lancar" value={data?.aset_lancar_persen ?? null} color="bg-blue-500" />
                    <PercentBar label="Aset Tetap" value={data?.aset_tetap_persen ?? null} color="bg-indigo-500" />
                    <PercentBar label="Liabilitas Lancar" value={data?.liabilitas_lancar_persen ?? null} color="bg-yellow-500" />
                    <PercentBar label="Liabilitas Jk. Panjang" value={data?.liabilitas_panjang_persen ?? null} color="bg-orange-600" />
                    <PercentBar label="Ekuitas" value={data?.ekuitas_persen ?? null} color="bg-purple-500" />
                </div>
            </div>

            {/* Catatan transparansi untuk pos gabungan */}
            {data?.beban_lain_pajak_persen !== null && data?.beban_lain_pajak_persen !== undefined && (
                <p className="text-[10px] text-slate-400 italic mb-6 -mt-3">
                    * "Beban Lain & Pajak" adalah gabungan OpEx, Bunga, dan Pajak — tidak tersedia terpisah dari dokumen sumber.
                </p>
            )}

            {/* Donut chart, sebagai pelengkap visual */}
            <div className="mb-5">
                <DonutChart title="Income Statement (basis Pendapatan)" data={incomeStatementData} />
            </div>
            <div className="mb-5">
                <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2 text-center">
                    Balance Sheet — Keseimbangan Aset = Liabilitas + Ekuitas
                </p>
                <div className="flex gap-3">
                    <DonutChart title="Sisi Aset" data={asetData} height={180} />
                    <DonutChart title="Sisi Liabilitas + Ekuitas" data={liabilitasEkuitasData} height={180} />
                </div>
            </div>

            {/* Narasi AI */}
            {belumDianalisis ? (
                <div className="bg-slate-50/70 border border-dashed border-slate-200 rounded-lg p-4 text-center">
                    <p className="text-xs text-slate-400">Analisis Common-size belum pernah dijalankan pada periode ini.</p>
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