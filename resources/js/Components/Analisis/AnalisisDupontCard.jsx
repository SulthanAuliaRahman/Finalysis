import { Layers } from 'lucide-react';
import { RatioCardBase } from './RatioCardBase';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);

export function AnalisisDupontCard({ data, neraca, labaRugi, perusahaanId, analisisId }) {
    return (
        <RatioCardBase
            title="DuPont Analysis"
            icon={<Layers className="w-5 h-5" />}
            iconBgColor="bg-indigo-100"
            iconColor="text-indigo-600"
            ratios={[
                {
                    label: 'Net Profit Margin (NPM)',
                    value: data?.net_profit_margin ?? null, suffix: '%',
                    formula: 'Laba Bersih / Pendapatan',
                    breakdown: labaRugi ? `${formatNum(labaRugi.laba_bersih)} / ${formatNum(labaRugi.pendapatan)}` : null
                },
                {
                    label: 'Total Asset Turnover (TATO)',
                    value: data?.total_asset_turnover ?? null, suffix: 'x',
                    formula: 'Pendapatan / Total Aset',
                    breakdown: (labaRugi && neraca) ? `${formatNum(labaRugi.pendapatan)} / ${formatNum(neraca.total_assets)}` : null
                },
                {
                    label: 'Leverage Multiplier',
                    value: data?.leverage_multiplier ?? null, suffix: 'x',
                    formula: 'Total Aset / Total Ekuitas',
                    breakdown: neraca ? `${formatNum(neraca.total_assets)} / ${formatNum(neraca.total_equity)}` : null
                },
                {
                    label: 'Return on Equity (ROE)',
                    value: data?.roe ?? null, suffix: '%',
                    formula: 'NPM x TATO x Leverage',
                    breakdown: data ? `${data.net_profit_margin}% x ${data.total_asset_turnover}x x ${data.leverage_multiplier}x` : null
                },
            ]}
            narasi={data?.narasi_dupont_AI}
            section="dupont"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        />
    );
}