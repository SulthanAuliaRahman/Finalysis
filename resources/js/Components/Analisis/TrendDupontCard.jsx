import { GitMerge } from 'lucide-react';
import { TrendCardBase } from './TrendCardBase';
import { TabelPeriode, LineChartBlock } from './trendHelpers';

// Tabel row definitions
const DUPONT_ROWS = [
    { label: 'Net Profit Margin',   get: (a) => a?.dupont?.net_profit_margin,   suffix: '%' },
    { label: 'Total Asset Turnover',get: (a) => a?.dupont?.total_asset_turnover,suffix: 'x' },
    { label: 'Leverage Multiplier', get: (a) => a?.dupont?.leverage_multiplier, suffix: 'x' },
    { label: 'ROE',                 get: (a) => a?.dupont?.roe,                  suffix: '%' },
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

// Chart line definitions
// NPM & ROE -> sumbu kiri (%)
// TATO & Leverage -> sumbu kanan (x)
const DUPONT_LINES = [
    { key: 'npm',      label: 'NPM',      color: '#16a34a', axis: 'left',  get: (a) => a?.dupont?.net_profit_margin },
    { key: 'roe',      label: 'ROE',      color: '#dc2626', axis: 'left',  get: (a) => a?.dupont?.roe },
    { key: 'tato',     label: 'TATO',     color: '#2563eb', axis: 'right', get: (a) => a?.dupont?.total_asset_turnover },
    { key: 'leverage', label: 'Leverage', color: '#ea580c', axis: 'right', get: (a) => a?.dupont?.leverage_multiplier },
];

// Component
export function TrendDupontCard({ data, perusahaanId, analisisId }) {
    const periodeData = data?.periode_data ?? [];
    const dataKurang = periodeData.length < 2;

    return (
        <TrendCardBase
            title="Tren DuPont"
            icon={<GitMerge className="w-5 h-5" />}
            iconBgColor="bg-orange-100"
            iconColor="text-orange-600"
            section="trend_dupont"
            narasi={data?.narasi_dupont_AI}
            narasiLabel="DuPont"
            isDataIlustratif={data?.is_data_ilustratif ?? false}
            dataKurang={dataKurang}
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        >
            {/* Tabel 4 komponen DuPont */}
            <TabelPeriode
                title="Komponen DuPont"
                rows={DUPONT_ROWS}
                periodeData={periodeData}
            />

            {/* Dual-axis line chart */}
            <LineChartBlock
                title="Tren DuPont — NPM & ROE (kiri %) vs TATO & Leverage (kanan x)"
                periodeData={periodeData}
                lines={DUPONT_LINES}
                dualAxis
                leftUnit="%"
                rightUnit="x"
            />
        </TrendCardBase>
    );
}
