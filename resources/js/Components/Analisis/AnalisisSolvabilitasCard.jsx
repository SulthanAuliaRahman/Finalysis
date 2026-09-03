import { forwardRef } from 'react';
import { Shield } from 'lucide-react';
import { RatioCardBase } from './RatioCardBase';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
const parseVal = (val) => val ? parseFloat(val) : 0;

export const AnalisisSolvabilitasCard = forwardRef(function AnalisisSolvabilitasCard({ data, neraca, perusahaanId, analisisId }, ref) {

    const chartData = [
        { name: 'DER', value: parseVal(data?.debt_to_equity)},
        { name: 'DAR', value: parseVal(data?.debt_to_asset)},
        { name: 'leverage', value: parseVal(data?.leverage_multiplier)},
    ];

    return (
        <RatioCardBase
            ref={ref}
            title="Solvabilitas"
            icon={<Shield className="w-5 h-5" />}
            iconBgColor="bg-purple-100"
            iconColor="text-purple-600"
            chartColor="#9333ea" // Warna Ungu Tailwind
            chartData={chartData}
            ratios={[
                {
                    label: 'Debt to Equity (DER)',
                    value: data?.debt_to_equity != null ? parseVal(data.debt_to_equity) : null,
                    suffix: 'x',
                    formula: 'Total Kewajiban / Total Ekuitas',
                    breakdown: neraca ? `${formatNum(neraca.total_liabilities)} / ${formatNum(neraca.total_equitas)}` : null,
                    rawResult: data?.debt_to_equity != null ? parseVal(data.debt_to_equity) : null
                },
                {
                    label: 'Debt to Asset (DAR)',
                    value: data?.debt_to_asset != null ? parseVal(data.debt_to_asset) : null,
                    suffix: 'x',
                    formula: 'Total Kewajiban / Total Aset',
                    breakdown: neraca ? `${formatNum(neraca.total_liabilities)} / ${formatNum(neraca.total_asset)}` : null,
                    rawResult: data?.debt_to_asset != null ? parseVal(data.debt_to_asset) : null,
                },
                {
                    label: 'Leverage Multiplier',
                    value: data?.leverage_multiplier != null ? parseVal(data.leverage_multiplier) : null,
                    suffix: 'x',
                    formula: 'Total Aset / Total Ekuitas',
                    breakdown: neraca ? `${formatNum(neraca.total_asset)} / ${formatNum(neraca.total_equitas)}` : null,
                    rawResult: data?.leverage_multiplier != null ? parseVal(data.leverage_multiplier) : null,
                },
            ]}
            narasi={data?.narasi_solvabilitas_AI}
            section="solvabilitas"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        />
    );
});
