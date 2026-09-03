import { forwardRef } from 'react';
import { TrendingUp } from 'lucide-react';
import { RatioCardBase } from './RatioCardBase';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);

// Profitabilitas di chart menggunakan angka persentase murni (tidak dibagi 100)
const parseVal = (val) => val ? Number(val) : 0;

// Helper: Mengubah persentase (15.5) kembali menjadi desimal mentah hasil bagi (0,155)
const getRawDecimal = (val) => {
    if (val == null) return null;
    return Number(val / 100).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
};

export const AnalisisProfitabilitasCard = forwardRef(function AnalisisProfitabilitasCard({ data, neraca, labaRugi, perusahaanId, analisisId, sektor }, ref) {

    const chartData = [
        { name: 'NPM', value: parseVal(data?.net_profit_margin)},
        { name: 'ROA', value: parseVal(data?.ROA) },
        { name: 'ROE', value: parseVal(data?.ROE) },
    ];

    return (
        <RatioCardBase
            ref={ref}
            title="Profitabilitas"
            icon={<TrendingUp className="w-5 h-5" />}
            iconBgColor="bg-green-100"
            iconColor="text-green-600"
            chartColor="#16a34a"
            chartData={chartData}
            ratios={[
                {
                    label: 'Net Profit Margin',
                    value: data?.net_profit_margin ?? null,
                    suffix: '%',
                    formula: 'Laba Bersih / Pendapatan',
                    breakdown: labaRugi ? `${formatNum(labaRugi.laba_bersih_sesudah_pajak)} / ${formatNum(labaRugi.total_pendapatan)}` : null,
                    rawResult: data?.net_profit_margin != null ? getRawDecimal(data.net_profit_margin) : null
                },
                {
                    label: 'Return on Assets (ROA)',
                    value: data?.ROA ?? null,
                    suffix: '%',
                    formula: 'Laba Bersih / Total Aset',
                    breakdown: (labaRugi && neraca) ? `${formatNum(labaRugi.laba_bersih_sesudah_pajak)} / ${formatNum(neraca.total_asset)}` : null,
                    rawResult: data?.ROA != null ? getRawDecimal(data.ROA) : null
                },
                {
                    label: 'Return on Equity (ROE)',
                    value: data?.ROE ?? null,
                    suffix: '%',
                    formula: 'Laba Bersih / Total Ekuitas',
                    breakdown: (labaRugi && neraca) ? `${formatNum(labaRugi.laba_bersih_sesudah_pajak)} / ${formatNum(neraca.total_equitas)}` : null,
                    rawResult: data?.ROE != null ? getRawDecimal(data.ROE) : null
                },
            ]}
            narasi={data?.narasi_profitabilitas_AI}
            section="profitabilitas"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        />
    );
});
