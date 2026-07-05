import { useState } from 'react';
import { router } from '@inertiajs/react';
import { LineChart, RefreshCw, Loader2, Sparkles, TrendingUp, TrendingDown, Minus, AlertTriangle } from 'lucide-react';
import {
    LineChart as ReLineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer,
} from 'recharts';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
// Kolom decimal(12,6) Laravel dikembalikan sebagai string mentah — selalu konversi ke Number
// sebelum dipakai Recharts, kalau tidak sudut/skala pie & garis bisa gagal dihitung.
const toNum = (val) => (val === null || val === undefined ? null : Number(val));

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

function TabelPeriode({ title, rows, periodeData }) {
    return (
        <>
            <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2">{title}</p>
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
                                    <td key={p.urutan} className="text-right py-1.5 px-1">{row.render(p)}</td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </>
    );
}

// Chart garis generik. `lines`: [{ key, label, color, axis: 'left'|'right' }]
// `dualAxis`: true kalau ada campuran satuan (mis. % vs x) sehingga butuh 2 sumbu Y.
function LineChartBlock({ title, periodeData, lines, dualAxis = false, leftUnit = '%', rightUnit = 'x' }) {
    const chartData = periodeData.map((p) => {
        const row = { periode: labelPeriode(p.analisis) };
        lines.forEach((line) => {
            row[line.key] = toNum(line.get(p.analisis));
        });
        return row;
    });

    const adaData = lines.some((line) => chartData.some((row) => row[line.key] !== null));

    return (
        <div className="mb-5">
            <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2">{title}</p>
            {adaData ? (
                <div style={{ width: '100%', height: 220 }}>
                    <ResponsiveContainer>
                        <ReLineChart data={chartData} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
                            <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
                            <XAxis dataKey="periode" tick={{ fontSize: 11 }} />
                            <YAxis
                                yAxisId="left"
                                tick={{ fontSize: 11 }}
                                unit={leftUnit}
                                width={45}
                            />
                            {dualAxis && (
                                <YAxis
                                    yAxisId="right"
                                    orientation="right"
                                    tick={{ fontSize: 11 }}
                                    unit={rightUnit}
                                    width={45}
                                />
                            )}
                            <Tooltip formatter={(val, name) => [val, name]} />
                            <Legend wrapperStyle={{ fontSize: '11px' }} />
                            {lines.map((line) => (
                                <Line
                                    key={line.key}
                                    yAxisId={line.axis || 'left'}
                                    type="monotone"
                                    dataKey={line.key}
                                    name={line.label}
                                    stroke={line.color}
                                    strokeWidth={2}
                                    dot={{ r: 3 }}
                                    connectNulls
                                />
                            ))}
                        </ReLineChart>
                    </ResponsiveContainer>
                </div>
            ) : (
                <div className="h-32 rounded-lg bg-slate-50 border border-dashed border-slate-200 flex items-center justify-center">
                    <span className="text-xs text-slate-400">Belum ada data untuk chart ini</span>
                </div>
            )}
        </div>
    );
}

// 7 akun utama
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

// 9 rasio (tabel tetap gabung, chart dipecah per kategori)
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
        return <span className="text-slate-800 font-medium">{val !== null && val !== undefined ? `${val}${rasio.suffix}` : '—'}</span>;
    },
}));

// 4 metrik DuPont
const DUPONT_ROWS = [
    { label: 'Net Profit Margin', get: (a) => a?.dupont?.net_profit_margin, suffix: '%' },
    { label: 'Total Asset Turnover', get: (a) => a?.dupont?.total_asset_turnover, suffix: 'x' },
    { label: 'Leverage Multiplier', get: (a) => a?.dupont?.leverage_multiplier, suffix: 'x' },
    { label: 'ROE', get: (a) => a?.dupont?.roe, suffix: '%' },
].map((r) => ({
    label: r.label,
    render: (p) => {
        const val = r.get(p.analisis);
        return <span className="text-slate-800 font-medium">{val !== null && val !== undefined ? `${val}${r.suffix}` : '—'}</span>;
    },
}));

// 9 pos common-size
const COMMONSIZE_ROWS = [
    { label: 'HPP', get: (a) => a?.commonsize?.hpp_persen },
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
        return <span className="text-slate-800 font-medium">{val !== null && val !== undefined ? `${val}%` : '—'}</span>;
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
                    {/* 1. Perbandingan Akun Utama — tabel + narasi saja, tanpa chart */}
                    <TabelPeriode title="Perbandingan Akun Utama" rows={AKUN_UTAMA_ROWS} periodeData={periodeData} />
                    <NarasiBlock title="Akun Utama" text={data?.narasi_trend_AI} />

                    {/* 2. Tren Rasio — tabel gabung, lalu 4 chart per kategori */}
                    <TabelPeriode title="Tren Rasio" rows={RASIO_ROWS} periodeData={periodeData} />
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
                        <LineChartBlock
                            title="Tren Likuiditas"
                            periodeData={periodeData}
                            lines={[
                                { key: 'cr', label: 'Current Ratio', color: '#0ea5e9', get: (a) => a?.likuiditas?.current_ratio },
                                { key: 'qr', label: 'Quick Ratio', color: '#6366f1', get: (a) => a?.likuiditas?.quick_ratio },
                                { key: 'csr', label: 'Cash Ratio', color: '#14b8a6', get: (a) => a?.likuiditas?.cash_ratio },
                            ]}
                        />
                        <LineChartBlock
                            title="Tren Profitabilitas"
                            periodeData={periodeData}
                            lines={[
                                { key: 'npm', label: 'NPM', color: '#16a34a', get: (a) => a?.profitabilitas?.net_profit_margin },
                                { key: 'roa', label: 'ROA', color: '#f59e0b', get: (a) => a?.profitabilitas?.ROA },
                                { key: 'roe', label: 'ROE', color: '#dc2626', get: (a) => a?.profitabilitas?.ROE },
                            ]}
                        />
                        <LineChartBlock
                            title="Tren Solvabilitas"
                            periodeData={periodeData}
                            lines={[
                                { key: 'der', label: 'DER', color: '#ef4444', get: (a) => a?.solvabilitas?.debt_to_equity },
                                { key: 'dar', label: 'DAR', color: '#7f1d1d', get: (a) => a?.solvabilitas?.debt_to_asset },
                            ]}
                        />
                        <LineChartBlock
                            title="Tren Aktivitas"
                            periodeData={periodeData}
                            leftUnit="x"
                            lines={[
                                { key: 'tato', label: 'TATO', color: '#2563eb', get: (a) => a?.aktivitas?.total_asset_turnover },
                            ]}
                        />
                    </div>
                    <NarasiBlock title="Rasio" text={data?.narasi_rasio_AI} />

                    

                    {/* 3. Tren DuPont — tabel, narasi, 1 chart dual-axis */}
                    <TabelPeriode title="DuPont" rows={DUPONT_ROWS} periodeData={periodeData} />
                    <div className="flex justify-center">
                        <div className="w-full max-w-md">
                            <LineChartBlock
                                title="Tren DuPont"
                                periodeData={periodeData}
                                dualAxis
                                leftUnit="%"
                                rightUnit="x"
                                lines={[
                                    { key: 'npm', label: 'NPM', color: '#16a34a', axis: 'left', get: (a) => a?.dupont?.net_profit_margin },
                                    { key: 'roe', label: 'ROE', color: '#dc2626', axis: 'left', get: (a) => a?.dupont?.roe },
                                    { key: 'tato', label: 'TATO', color: '#2563eb', axis: 'right', get: (a) => a?.dupont?.total_asset_turnover },
                                    { key: 'leverage', label: 'Leverage', color: '#ea580c', axis: 'right', get: (a) => a?.dupont?.leverage_multiplier },
                                ]}
                            />
                        </div>
                    </div>
                
                    <NarasiBlock title="DuPont" text={data?.narasi_dupont_AI} />
                    

                    {/* 4. Tren Common-size — tabel gabung, narasi, 2 chart terpisah basis */}
                    <TabelPeriode title="Common-size" rows={COMMONSIZE_ROWS} periodeData={periodeData} />
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
                        <LineChartBlock
                            title="Tren Common-size — Laba Rugi (basis Pendapatan)"
                            periodeData={periodeData}
                            lines={[
                                { key: 'hpp', label: 'HPP', color: '#f97316', get: (a) => a?.commonsize?.hpp_persen },
                                { key: 'beban_lain', label: 'Beban Lain & Pajak', color: '#ef4444', get: (a) => a?.commonsize?.beban_lain_pajak_persen },
                                { key: 'laba_bersih', label: 'Laba Bersih', color: '#16a34a', get: (a) => a?.commonsize?.laba_bersih_persen },
                            ]}
                        />
                        <LineChartBlock
                            title="Tren Common-size — Neraca (basis Total Aset)"
                            periodeData={periodeData}
                            lines={[
                                { key: 'aset_lancar', label: 'Aset Lancar', color: '#3b82f6', get: (a) => a?.commonsize?.aset_lancar_persen },
                                { key: 'aset_tetap', label: 'Aset Tetap', color: '#1e3a8a', get: (a) => a?.commonsize?.aset_tetap_persen },
                                { key: 'liab_lancar', label: 'Liabilitas Lancar', color: '#eab308', get: (a) => a?.commonsize?.liabilitas_lancar_persen },
                                { key: 'liab_panjang', label: 'Liabilitas Jk. Panjang', color: '#f97316', get: (a) => a?.commonsize?.liabilitas_panjang_persen },
                                { key: 'ekuitas', label: 'Ekuitas', color: '#a855f7', get: (a) => a?.commonsize?.ekuitas_persen },
                            ]}
                        />
                    </div>
                    <NarasiBlock title="Common-size" text={data?.narasi_commonsize_AI} />
                    
                </>
            )}
        </div>
    );
}