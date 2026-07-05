import { TrendingUp } from 'lucide-react';
import { RatioCardBase } from './RatioCardBase';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);

export function AnalisisProfitabilitasCard({ data, neraca, labaRugi, perusahaanId, analisisId }) {
    return (
        <RatioCardBase
            title="Profitabilitas"
            icon={<TrendingUp className="w-5 h-5" />}
            iconBgColor="bg-green-100"
            iconColor="text-green-600"
            ratios={[
                {
                    label: 'Net Profit Margin',
                    value: data?.net_profit_margin ?? null, suffix: '%',
                    formula: 'Laba Bersih / Pendapatan',
                    breakdown: labaRugi ? `${formatNum(labaRugi.laba_bersih)} / ${formatNum(labaRugi.pendapatan)}` : null
                },
                {
                    label: 'Return on Assets (ROA)',
                    value: data?.ROA ?? null, suffix: '%',
                    formula: 'Laba Bersih / Total Aset',
                    breakdown: (labaRugi && neraca) ? `${formatNum(labaRugi.laba_bersih)} / ${formatNum(neraca.total_assets)}` : null
                },
                {
                    label: 'Return on Equity (ROE)',
                    value: data?.ROE ?? null, suffix: '%',
                    formula: 'Laba Bersih / Total Ekuitas',
                    breakdown: (labaRugi && neraca) ? `${formatNum(labaRugi.laba_bersih)} / ${formatNum(neraca.total_equity)}` : null
                },
            ]}
            narasi={data?.narasi_profitabilitas_AI}
            section="profitabilitas"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        />
    );
}
