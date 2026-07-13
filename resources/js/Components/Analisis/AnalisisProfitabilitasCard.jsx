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

export const AnalisisProfitabilitasCard = forwardRef(function AnalisisProfitabilitasCard({ data, neraca, labaRugi, perusahaanId, analisisId, sektor, referenceDocuments }, ref) {

    // Logika Dinamis Benchmark NPM berdasarkan sektor
    let npmBenchmark = null;
    if (sektor === "Jasa") npmBenchmark = 10;
    else if (sektor === "Manufaktur") npmBenchmark = 7.5;
    else if (sektor === "Perdagangan") npmBenchmark = 3.5;

    const chartData = [
        { name: 'NPM', value: parseVal(data?.net_profit_margin), benchmark: npmBenchmark },
        { name: 'ROA', value: parseVal(data?.ROA), benchmark: 5 },
        { name: 'ROE', value: parseVal(data?.ROE), benchmark: 15 },
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
                    breakdown: labaRugi ? `${formatNum(labaRugi.laba_bersih)} / ${formatNum(labaRugi.pendapatan)}` : null,
                    rawResult: data?.net_profit_margin != null ? getRawDecimal(data.net_profit_margin) : null
                },
                {
                    label: 'Return on Assets (ROA)',
                    value: data?.ROA ?? null,
                    suffix: '%',
                    formula: 'Laba Bersih / Total Aset',
                    breakdown: (labaRugi && neraca) ? `${formatNum(labaRugi.laba_bersih)} / ${formatNum(neraca.total_assets)}` : null,
                    rawResult: data?.ROA != null ? getRawDecimal(data.ROA) : null
                },
                {
                    label: 'Return on Equity (ROE)',
                    value: data?.ROE ?? null,
                    suffix: '%',
                    formula: 'Laba Bersih / Total Ekuitas',
                    breakdown: (labaRugi && neraca) ? `${formatNum(labaRugi.laba_bersih)} / ${formatNum(neraca.total_equity)}` : null,
                    rawResult: data?.ROE != null ? getRawDecimal(data.ROE) : null
                },
            ]}
            narasi={data?.narasi_profitabilitas_AI}
            section="profitabilitas"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
            referenceDocuments={referenceDocuments}
        />
    );
});
