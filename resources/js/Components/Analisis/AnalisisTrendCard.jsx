import { useState } from 'react';
import { router } from '@inertiajs/react';
import { LineChart, RefreshCw, Loader2, Sparkles, TrendingUp, TrendingDown, Minus, AlertTriangle } from 'lucide-react';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);

function GrowthBadge({ value }) {
    const hasValue = value !== null && value !== undefined;
    const isUp = hasValue && value > 0;
    const isDown = hasValue && value < 0;
    const Icon = isUp ? TrendingUp : isDown ? TrendingDown : Minus;
    const color = isUp ? 'text-green-600' : isDown ? 'text-red-500' : 'text-slate-400';

    if (!hasValue) return <span className="text-slate-300 text-[11px]">—</span>;

    return (
        <span className={`inline-flex items-center gap-0.5 text-[11px] font-medium ${color}`}>
            <Icon className="w-3 h-3" />
            {value > 0 ? '+' : ''}{value}%
        </span>
    );
}

// Narasi AI, dipakai berulang di 4 section
function NarasiBlock({ title, text }) {
    const belum = !text;
    return belum ? (
        <div className="bg-slate-50/70 border border-dashed border-slate-200 rounded-lg p-3 text-center mb-5">
            <p className="text-xs text-slate-400">Belum ada narasi {title.toLowerCase()} untuk periode ini.</p>
        </div>
    ) : (
        <div className="bg-blue-50/50 border border-blue-100 rounded-lg p-3 mb-5">
            <div className="flex items-center gap-1.5 mb-1.5">
                <Sparkles className="w-3.5 h-3.5 text-blue-500" />
                <span className="text-xs font-medium text-blue-700">Insight AI — {title}</span>
            </div>
            <p className="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{text}</p>
        </div>
    );
}

// Tabel generik: baris = daftar rasio/akun, kolom = periode
function TabelPeriode({ title, rows, periodeData }) {
    return (
        <>
            <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2">
                {title}
            </p>
            <div className="overflow-x-auto mb-3 -mx-1">
                <table className="w-full text-xs min-w-[500px]">
                    <thead>
                        <tr className="text-slate-400 border-b border-slate-100">
                            <th className="text-left py-1.5 px-1 font-medium">Item</th>
                            {periodeData.map((p) => (
                                <th key={p.urutan} className="text-right py-1.5 px-1 font-medium whitespace-nowrap">
                                    {labelPeriode(p.analisis)}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {rows.map((row) => (
                            <tr key={row.label} className="border-b border-slate-50 last:border-0">
                                <td className="py-1.5 px-1 text-slate-600">{row.label}</td>
                                {periodeData.map((p) => (
                                    <td key={p.urutan} className="text-right py-1.5 px-1">
                                        {row.render(p)}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </>
    );
}

// 7 akun utama, dipetakan ke field flat di tiap titik data
const AKUN_UTAMA = [
    { key: 'pendapatan', growthKey: 'growth_pendapatan', label: 'Pendapatan' },
    { key: 'laba_kotor', growthKey: 'growth_laba_kotor', label: 'Laba Kotor' },
    { key: 'laba_bersih', growthKey: 'growth_laba_bersih', label: 'Laba Bersih' },
    { key: 'total_assets', growthKey: 'growth_total_assets', label: 'Total Aset' },
    { key: 'kas_setara_kas', growthKey: 'growth_kas_setara_kas', label: 'Kas & Setara Kas' },
    { key: 'total_equity', growthKey: 'growth_total_equity', label: 'Total Ekuitas' },
    { key: 'net_cash_flow', growthKey: 'growth_net_cash_flow', label: 'Net Cash Flow' },
];

const AKUN_UTAMA_ROWS = AKUN_UTAMA.map((akun) => ({
    label: akun.label,
    render: (p) => (
        <>
            <div className="text-slate-800 font-medium">{formatNum(p[akun.key])}</div>
            <GrowthBadge value={p[akun.growthKey]} />
        </>
    ),
}));

// 9 rasio, diambil dari relasi analisis->likuiditas/profitabilitas/solvabilitas/aktivitas per titik data
const RASIO_ROWS = [
    { label: 'Current Ratio', get: (a) => a?.likuiditas?.current_ratio, suffix: '%' },
    { label: 'Quick Ratio', get: (a) => a?.likuiditas?.quick_ratio, suffix: '%' },
    { label: 'Cash Ratio', get: (a) => a?.likuiditas?.cash_ratio, suffix: '%' },
    { label: 'NPM', get: (a) => a?.profitabilitas?.net_profit_margin, suffix: '%' },
    { label: 'ROA', get: (a) => a?.profitabilitas?.ROA, suffix: '%' },
    { label: 'ROE', get: (a) => a?.profitabilitas?.ROE, suffix: '%' },
    { label: 'DER', get: (a) => a?.solvabilitas?.debt_to_equity, suffix: '%' },
    { label: 'DAR', get: (a) => a?.solvabilitas?.debt_to_asset, suffix: '%' },
    { label: 'TATO', get: (a) => a?.aktivitas?.total_asset_turnover, suffix: 'x' },
].map((rasio) => ({
    label: rasio.label,
    render: (p) => {
        const val = rasio.get(p.analisis);
        return (
            <span className="text-slate-800 font-medium">
                {val !== null && val !== undefined ? `${val}${rasio.suffix}` : '—'}
            </span>
        );
    },
}));

// 4 metrik DuPont, dari relasi analisis->dupont per titik data
const DUPONT_ROWS = [
    { label: 'Net Profit Margin', get: (a) => a?.dupont?.net_profit_margin, suffix: '%' },
    { label: 'Total Asset Turnover', get: (a) => a?.dupont?.total_asset_turnover, suffix: 'x' },
    { label: 'Leverage Multiplier', get: (a) => a?.dupont?.leverage_multiplier, suffix: 'x' },
    { label: 'ROE', get: (a) => a?.dupont?.roe, suffix: '%' },
].map((r) => ({
    label: r.label,
    render: (p) => {
        const val = r.get(p.analisis);
        return (
            <span className="text-slate-800 font-medium">
                {val !== null && val !== undefined ? `${val}${r.suffix}` : '—'}
            </span>
        );
    },
}));

// 9 pos common-size, dari relasi analisis->commonsize per titik data
const COMMONSIZE_ROWS = [
    { label: 'HPP', get: (a) => a?.commonsize?.hpp_persen },
    { label: 'Laba Kotor', get: (a) => a?.commonsize?.laba_kotor_persen },
    { label: 'Beban Lain & Pajak', get: (a) => a?.commonsize?.beban_lain_pajak_persen },
    { label: 'Laba Bersih', get: (a) => a?.commonsize?.laba_bersih_persen },
    { label: 'Aset Lancar', get: (a) => a?.commonsize?.aset_lancar_persen },
    { label: 'Aset Tetap', get: (a) => a?.commonsize?.aset_tetap_persen },
    { label: 'Liabilitas Lancar', get: (a) => a?.commonsize?.liabilitas_lancar_persen },
    { label: 'Liabilitas Jk. Panjang', get: (a) => a?.commonsize?.liabilitas_panjang_persen },
    { label: 'Ekuitas', get: (a) => a?.commonsize?.ekuitas_persen },
].map((r) => ({
    label: r.label,
    render: (p) => {
        const val = r.get(p.analisis);
        return (
            <span className="text-slate-800 font-medium">
                {val !== null && val !== undefined ? `${val}%` : '—'}
            </span>
        );
    },
}));

function labelPeriode(analisis) {
    if (!analisis) return '—';
    if (analisis.periode_type === 'annual') return `${analisis.tahun}`;
    if (analisis.periode_type === 'quarterly') return `Q${analisis.quarter} ${analisis.tahun}`;
    return `Bln ${analisis.bulan} ${analisis.tahun}`;
}

export function AnalisisTrendCard({ data, perusahaanId, analisisId }) {
    const [isLoading, setIsLoading] = useState(false);
    const belumDianalisis = !data?.narasi_trend_AI;
    const periodeData = data?.periode_data ?? [];

    function handleTrigger() {
        setIsLoading(true);
        router.post(
            `/perusahaan/${perusahaanId}/analisis/${analisisId}/regenerasi`,
            { section: 'trend' },
            { preserveScroll: true, onFinish: () => setIsLoading(false) }
        );
    }

    return (
        <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
            <div className="flex items-center justify-between mb-4">
                <div className="flex items-center gap-2.5">
                    <div className="p-2 rounded-lg bg-pink-100">
                        <LineChart className="w-5 h-5 text-pink-600" />
                    </div>
                    <h3 className="font-semibold text-slate-900">Trend Analysis</h3>
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

            {data?.is_data_ilustratif && (
                <div className="flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-lg p-2.5 mb-4">
                    <AlertTriangle className="w-3.5 h-3.5 text-amber-500 mt-0.5 flex-shrink-0" />
                    <p className="text-[11px] text-amber-700 leading-relaxed">
                        Belum ada cukup periode pembanding untuk tren yang bermakna. Data di bawah bersifat{' '}
                        <span className="font-semibold">ilustratif/baseline</span>.
                    </p>
                </div>
            )}

            {periodeData.length === 0 || belumDianalisis ? (
                <div className="bg-slate-50/70 border border-dashed border-slate-200 rounded-lg p-4 text-center">
                    <p className="text-xs text-slate-400">Analisis tren belum pernah dijalankan pada periode ini.</p>
                </div>
            ) : (
                <>
                    {/* 1. Perbandingan Akun Utama */}
                    <TabelPeriode title="Perbandingan Akun Utama" rows={AKUN_UTAMA_ROWS} periodeData={periodeData} />
                    <NarasiBlock title="Akun Utama" text={data?.narasi_trend_AI} />

                    {/* 2. Tren Rasio */}
                    <TabelPeriode title="Tren Rasio" rows={RASIO_ROWS} periodeData={periodeData} />
                    <NarasiBlock title="Rasio" text={data?.narasi_rasio_AI} />

                    {/* 3. DuPont */}
                    <TabelPeriode title="DuPont" rows={DUPONT_ROWS} periodeData={periodeData} />
                    <NarasiBlock title="DuPont" text={data?.narasi_dupont_AI} />

                    {/* 4. Common-size */}
                    <TabelPeriode title="Common-size" rows={COMMONSIZE_ROWS} periodeData={periodeData} />
                    <NarasiBlock title="Common-size" text={data?.narasi_commonsize_AI} />
                </>
            )}
        </div>
    );
}