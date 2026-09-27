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

export const AnalisisProfitabilitasCard = forwardRef(function AnalisisProfitabilitasCard({ data, neraca,neracaSebelumnya, labaRugi,  perusahaanId, analisisId, sectionStatus, canRegenerasi, onRegenerasiStart }, ref) {

    const chartData = [
        { name: 'NPM', value: parseVal(data?.net_profit_margin)},
        { name: 'ROA', value: parseVal(data?.ROA) },
        { name: 'ROE', value: parseVal(data?.ROE) },
    ];

    const adaPeriodeSebelumnya = !!neracaSebelumnya;
    const labaBersih = labaRugi?.laba_bersih_sesudah_pajak;

     const roaBreakdown = (neraca && labaBersih != null)
        ? (adaPeriodeSebelumnya
            ? `${formatNum(labaBersih)} / ((${formatNum(neracaSebelumnya.total_asset)} + ${formatNum(neraca.total_asset)}) / 2)`
            : `${formatNum(labaBersih)} / ${formatNum(neraca.total_asset)}`)
        : null;

    const roeBreakdown = (neraca && labaBersih != null)
        ? (adaPeriodeSebelumnya
            ? `${formatNum(labaBersih)} / ((${formatNum(neracaSebelumnya.total_equitas)} + ${formatNum(neraca.total_equitas)}) / 2)`
            : `${formatNum(labaBersih)} / ${formatNum(neraca.total_equitas)}`)
        : null;

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
                    formula: adaPeriodeSebelumnya ? 'Laba Bersih / Rata-rata Total Aset' : 'Laba Bersih / Total Aset',
                    breakdown: roaBreakdown,
                    rawResult: data?.ROA != null ? getRawDecimal(data.ROA) : null
                },
                {
                    label: 'Return on Equity (ROE)',
                    value: data?.ROE ?? null,
                    suffix: '%',
                    formula: adaPeriodeSebelumnya ? 'Laba Bersih / Rata-rata Total Ekuitas' : 'Laba Bersih / Total Ekuitas',
                    breakdown: roeBreakdown,
                    rawResult: data?.ROE != null ? getRawDecimal(data.ROE) : null
                },
            ]}
            narasi={data?.narasi_profitabilitas_AI}
            section="profitabilitas"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
            sectionStatus={sectionStatus}
            canRegenerasi={canRegenerasi}
            onRegenerasiStart={onRegenerasiStart}
        />
    );
});
