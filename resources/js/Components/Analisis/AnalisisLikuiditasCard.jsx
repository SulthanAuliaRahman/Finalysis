import { Droplet } from 'lucide-react';
import { RatioCardBase } from './RatioCardBase';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);

export function AnalisisLikuiditasCard({ data, neraca, perusahaanId, analisisId }) {
    return (
        <RatioCardBase
            title="Likuiditas"
            icon={<Droplet className="w-5 h-5" />}
            iconBgColor="bg-blue-100"
            iconColor="text-blue-600"
            ratios={[
                {
                    label: 'Current Ratio',
                    value: data?.current_ratio ?? null, suffix: '%',
                    formula: 'Aset Lancar / Kewajiban Lancar',
                    breakdown: neraca ? `${formatNum(neraca.current_assets)} / ${formatNum(neraca.current_liabilities)}` : null
                },
                {
                    label: 'Quick Ratio',
                    value: data?.quick_ratio ?? null, suffix: '%',
                    formula: '(Aset Lancar - Persediaan) / Kewajiban Lancar',
                    breakdown: neraca ? `(${formatNum(neraca.current_assets)} - 0) / ${formatNum(neraca.current_liabilities)}` : null
                },
                {
                    label: 'Cash Ratio',
                    value: data?.cash_ratio ?? null, suffix: '%',
                    formula: 'Kas / Kewajiban Lancar',
                    breakdown: neraca ? `0 / ${formatNum(neraca.current_liabilities)}` : null
                },
            ]}
            narasi={data?.narasi_likuiditas_AI}
            section="likuiditas"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        />
    );
}
