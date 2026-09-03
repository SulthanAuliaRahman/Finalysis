import { forwardRef } from 'react';
import { Droplet } from 'lucide-react';
import { RatioCardBase } from './RatioCardBase';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
const parseVal = (val) => val != null ? Number(val) : 0;

export const AnalisisLikuiditasCard = forwardRef(function AnalisisLikuiditasCard({ data, neraca, perusahaanId, analisisId }, ref) {

    const chartData = [
        { name: 'CR', value: parseVal(data?.current_ratio) },
        { name: 'Cash', value: parseVal(data?.cash_ratio) },
    ];

    return (
        <RatioCardBase
            ref={ref}
            title="Likuiditas"
            icon={<Droplet className="w-5 h-5" />}
            iconBgColor="bg-blue-100"
            iconColor="text-blue-600"
            chartColor="#3b82f6"
            chartData={chartData}
            ratios={[
                {
                    label: 'Current Ratio',
                    value: data?.current_ratio != null ? parseVal(data.current_ratio) : null,
                    suffix: 'x',
                    formula: 'Total Aset Lancar / Total Liabilitas Pendek',
                    breakdown: neraca ? `${formatNum(neraca.total_asset_lancar)} / ${formatNum(neraca.total_liabilities_pendek)}` : null,
                    rawResult: data?.current_ratio != null ? `${parseVal(data.current_ratio)}` : null
                },
                {
                    label: 'Cash Ratio',
                    value: data?.cash_ratio != null ? parseVal(data.cash_ratio) : null,
                    suffix: 'x',
                    formula: 'Total Kas & Setara Kas / Total Liabilitas Pendek',
                    breakdown: neraca ? `${formatNum(neraca.total_kas_setara_kas)} / ${formatNum(neraca.total_liabilities_pendek)}` : null,
                    rawResult: data?.cash_ratio != null ? `${parseVal(data.cash_ratio)}` : null
                },
            ]}
            narasi={data?.narasi_likuiditas_AI}
            section="likuiditas"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        />
    );
});
