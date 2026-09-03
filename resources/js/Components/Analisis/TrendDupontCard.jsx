import { GitMerge } from 'lucide-react';
import { forwardRef } from 'react';
import { TrendCardBase } from './TrendCardBase';
import { TabelPeriode, LineChartBlock } from './trendHelpers';

const DUPONT_ROWS = [
    { label: 'Net Profit Margin',    get: (a) => a?.profitabilitas?.net_profit_margin,    suffix: '%' },
    { label: 'Total Asset Turnover', get: (a) => a?.aktivitas?.total_asset_turnover, suffix: 'x' },
    { label: 'Leverage Multiplier',  get: (a) => a?.solvabilitas?.leverage_multiplier,  suffix: 'x' },
    { label: 'ROE Dupont',                  get: (a) => a?.dupont?.roe_dupont,                  suffix: '%' },
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

const DUPONT_LINES = [
    { key: 'npm',      label: 'NPM',      color: '#16a34a', axis: 'left',  get: (a) => a?.profitabilitas?.net_profit_margin},
    { key: 'roe_dupont',      label: 'ROE Dupont',      color: '#dc2626', axis: 'left',  get: (a) => a?.dupont?.roe_dupont },
    { key: 'tato',     label: 'TATO',     color: '#2563eb', axis: 'right', get: (a) => a?.aktivitas?.total_asset_turnover },
    { key: 'leverage', label: 'Leverage', color: '#ea580c', axis: 'right', get: (a) => a?.solvabilitas?.leverage_multiplier},
];

export const TrendDupontCard = forwardRef(function TrendDupontCard({ data, perusahaanId, analisisId }, ref) {
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
        >
            <TabelPeriode
                title="Komponen DuPont"
                rows={DUPONT_ROWS}
                periodeData={periodeData}
            />

            <div ref={ref} className="w-full bg-white mt-4 pb-2">
                <LineChartBlock
                    title="Tren DuPont — NPM & ROE (kiri %) vs TATO & Leverage (kanan x)"
                    periodeData={periodeData}
                    lines={DUPONT_LINES}
                    dualAxis
                    leftUnit="%"
                    rightUnit="x"
                />
            </div>
        </TrendCardBase>
    );
});
