import { Droplet } from 'lucide-react';
import { RatioCardBase } from './RatioCardBase';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
// Helper: Mengubah angka database (persentase) menjadi desimal murni kelipatan
const parseVal = (val) => val ? Number((val / 100).toFixed(2)) : 0;

export function AnalisisLikuiditasCard({ data, neraca, perusahaanId, analisisId }) {

    const chartData = [
        { name: 'CR', value: parseVal(data?.current_ratio), benchmark: 1.5 },
        { name: 'QR', value: parseVal(data?.quick_ratio), benchmark: 1.0 },
        { name: 'Cash', value: parseVal(data?.cash_ratio), benchmark: 0.2 },
    ];

    return (
        <RatioCardBase
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
                    formula: 'Aset Lancar / Kewajiban Lancar',
                    breakdown: neraca ? `${formatNum(neraca.current_assets)} / ${formatNum(neraca.current_liabilities)}` : null,
                    rawResult: data?.current_ratio != null ? `${parseVal(data.current_ratio)}` : null
                },
                {
                    label: 'Quick Ratio',
                    value: data?.quick_ratio != null ? parseVal(data.quick_ratio) : null,
                    suffix: 'x',
                    formula: '(Aset Lancar - Persediaan) / Kewajiban Lancar',
                    breakdown: neraca ? `(${formatNum(neraca.current_assets)} - ${formatNum(neraca.inventory)} ) / ${formatNum(neraca.current_liabilities)}` : null,
                    rawResult: data?.quick_ratio != null ? `${parseVal(data.quick_ratio)}` : null
                },
                {
                    label: 'Cash Ratio',
                    value: data?.cash_ratio != null ? parseVal(data.cash_ratio) : null,
                    suffix: 'x',
                    formula: 'Kas / Kewajiban Lancar',
                    breakdown: neraca ? `${formatNum(neraca.cash_equivalent)} / ${formatNum(neraca.current_liabilities)}` : null,
                    rawResult: data?.cash_ratio != null ? `${parseVal(data.cash_ratio)}` : null
                },
            ]}
            narasi={data?.narasi_likuiditas_AI}
            section="likuiditas"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        />
    );
}
