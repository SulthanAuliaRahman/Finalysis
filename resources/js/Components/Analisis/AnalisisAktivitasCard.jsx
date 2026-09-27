import { forwardRef } from 'react';
import { Activity } from 'lucide-react';
import { RatioCardBase } from './RatioCardBase';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
const parseVal = (val) => val ? parseFloat(val) : 0;

export const AnalisisAktivitasCard = forwardRef(function AnalisisAktivitasCard({ data, neraca, neracaSebelumnya, labaRugi, perusahaanId, analisisId, sectionStatus, canRegenerasi, onRegenerasiStart}, ref) {

    const chartData = [
        { name: 'TATO', value: parseVal(data?.total_asset_turnover) },
        { name: 'WCT', value: parseVal(data?.working_capital_turnover) },
        { name: 'fixedAsset', value: parseVal(data?.fixed_asset_turnover) },
    ];

    const adaPeriodeSebelumnya = !!neracaSebelumnya;
    const pendapatan = labaRugi?.total_pendapatan;

    // TATO: Pendapatan / rata-rata Total Aset
    const tatoBreakdown = (neraca && pendapatan != null)
        ? (adaPeriodeSebelumnya
            ? `${formatNum(pendapatan)} / ((${formatNum(neracaSebelumnya.total_asset)} + ${formatNum(neraca.total_asset)}) / 2)`
            : `${formatNum(pendapatan)} / ${formatNum(neraca.total_asset)}`)
        : null;

    // WCT: Pendapatan / rata-rata Modal Kerja — Modal Kerja di-expand jadi
    // (Aset Lancar - Liabilitas Pendek) per periode, bukan angka jadi.
    const wctBreakdown = (neraca && pendapatan != null)
        ? (adaPeriodeSebelumnya
            ? `${formatNum(pendapatan)} / (((${formatNum(neracaSebelumnya.total_asset_lancar)} - ${formatNum(neracaSebelumnya.total_liabilities_pendek)}) + (${formatNum(neraca.total_asset_lancar)} - ${formatNum(neraca.total_liabilities_pendek)})) / 2)`
            : `${formatNum(pendapatan)} / (${formatNum(neraca.total_asset_lancar)} - ${formatNum(neraca.total_liabilities_pendek)})`)
        : null;

    // FAT: Pendapatan / rata-rata Aset Tetap
    const fatBreakdown = (neraca && pendapatan != null)
        ? (adaPeriodeSebelumnya
            ? `${formatNum(pendapatan)} / ((${formatNum(neracaSebelumnya.total_asset_tetap)} + ${formatNum(neraca.total_asset_tetap)}) / 2)`
            : `${formatNum(pendapatan)} / ${formatNum(neraca.total_asset_tetap)}`)
        : null;

    return (
        <RatioCardBase
            ref={ref}
            title="Aktivitas"
            icon={<Activity className="w-5 h-5" />}
            iconBgColor="bg-orange-100"
            iconColor="text-orange-600"
            chartColor="#ea580c"
            chartData={chartData}
            ratios={[
                {
                    label: 'Total Asset Turnover (TATO)',
                    value: data?.total_asset_turnover != null ? parseVal(data.total_asset_turnover) : null,
                    suffix: 'x',
                    formula: adaPeriodeSebelumnya ? 'Pendapatan / Rata-rata Total Aset' : 'Pendapatan / Total Aset',
                    breakdown: tatoBreakdown,
                    rawResult: data?.total_asset_turnover != null ? parseVal(data.total_asset_turnover) : null,
                },
                {
                    label: 'Working Capital Turnover (WCT)',
                    value: data?.working_capital_turnover != null ? parseVal(data.working_capital_turnover) : null,
                    suffix: 'x',
                    formula: adaPeriodeSebelumnya ? 'Pendapatan / Rata-rata (Aset Lancar - Liabilitas Pendek)' : 'Pendapatan / (Aset Lancar - Liabilitas Pendek)',
                    breakdown: wctBreakdown,
                    rawResult: data?.working_capital_turnover != null ? parseVal(data.working_capital_turnover) : null,
                },
                {
                    label: 'Fixed Asset Turnover (FAT)',
                    value: data?.fixed_asset_turnover != null ? parseVal(data.fixed_asset_turnover) : null,
                    suffix: 'x',
                    formula: adaPeriodeSebelumnya ? 'Pendapatan / Rata-rata Aset Tetap' : 'Pendapatan / Aset Tetap',
                    breakdown: fatBreakdown,
                    rawResult: data?.fixed_asset_turnover != null ? parseVal(data.fixed_asset_turnover) : null,
                },
            ]}
            narasi={data?.narasi_aktivitas_AI}
            section="aktivitas"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
            sectionStatus={sectionStatus}
            canRegenerasi={canRegenerasi}
            onRegenerasiStart={onRegenerasiStart}
        />
    );
});
