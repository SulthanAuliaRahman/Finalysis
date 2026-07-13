import { GitMerge } from 'lucide-react';
import { TrendCardBase } from './TrendCardBase';
import { TabelPeriode, LineChartBlock } from './trendHelpers';

// Tabel row definitions
const DUPONT_ROWS = [
    { label: 'Net Profit Margin',    get: (a) => a?.dupont?.net_profit_margin,    suffix: '%' },
    { label: 'Total Asset Turnover', get: (a) => a?.dupont?.total_asset_turnover, suffix: 'x' },
    { label: 'Leverage Multiplier',  get: (a) => a?.dupont?.leverage_multiplier,  suffix: 'x' },
    { label: 'ROE',                  get: (a) => a?.dupont?.roe,                  suffix: '%' },
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

// NPM & ROE -> sumbu kiri (%)
// TATO & Leverage -> sumbu kanan (x)
const DUPONT_LINES = [
    { key: 'npm',      label: 'NPM',      color: '#16a34a', axis: 'left',  get: (a) => a?.dupont?.net_profit_margin },
    { key: 'roe',      label: 'ROE',      color: '#dc2626', axis: 'left',  get: (a) => a?.dupont?.roe },
    { key: 'tato',     label: 'TATO',     color: '#2563eb', axis: 'right', get: (a) => a?.dupont?.total_asset_turnover },
    { key: 'leverage', label: 'Leverage', color: '#ea580c', axis: 'right', get: (a) => a?.dupont?.leverage_multiplier },
];

export function TrendDupontCard({ data, perusahaanId, analisisId, referenceDocuments }) {
    const periodeData = data?.periode_data ?? [];
    const dataKurang  = periodeData.length < 2;
    const hasGap      = data?.has_gap ?? false;

    return (
        <TrendCardBase
            title="Tren DuPont"
            icon={<GitMerge className="w-5 h-5" />}
            iconBgColor="bg-orange-100"
            iconColor="text-orange-600"
            section="trend_dupont"
            narasi={data?.narasi_trend_dupont_AI}
            narasiLabel="DuPont"
            hasGap={hasGap}
            dataKurang={dataKurang}
            perusahaanId={perusahaanId}
            analisisId={analisisId}
            referenceDocuments={referenceDocuments}
        >
            <TabelPeriode
                title="Komponen DuPont"
                rows={DUPONT_ROWS}
                periodeData={periodeData}
            />

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
