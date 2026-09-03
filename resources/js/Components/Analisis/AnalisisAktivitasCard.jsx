import { forwardRef } from 'react';
import { Activity } from 'lucide-react';
import { RatioCardBase } from './RatioCardBase';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
const parseVal = (val) => val ? parseFloat(val) : 0;

export const AnalisisAktivitasCard = forwardRef(function AnalisisAktivitasCard({ data, neraca, labaRugi, perusahaanId, analisisId }, ref) {

    const chartData = [
        { name: 'TATO', value: parseVal(data?.total_asset_turnover) },
        { name: 'WCT', value: parseVal(data?.working_capital_turnover) },
        // { name: 'fixedAsset', value: parseVal(data?.fixed_asset_turnover) },
    ];

    const modalKerja = neraca ? neraca.total_asset_lancar - neraca.total_liabilities_pendek : null;

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
                    formula: 'Pendapatan / Total Aset',
                    breakdown: (labaRugi && neraca) ? `${formatNum(labaRugi.total_pendapatan)} / ${formatNum(neraca.total_asset)}` : null,
                    rawResult: data?.total_asset_turnover != null ? parseVal(data.total_asset_turnover) : null,
                },
                {
                    label: 'Working Capital Turnover (WCT)',
                    value: data?.working_capital_turnover != null ? parseVal(data.working_capital_turnover) : null,
                    suffix: 'x',
                    formula: 'Pendapatan / Modal Kerja (Aset Lancar - Liabilitas Pendek)',
                    breakdown: (labaRugi && neraca) ? `${formatNum(labaRugi.total_pendapatan)} / ${formatNum(modalKerja)}` : null,
                    rawResult: data?.working_capital_turnover != null ? parseVal(data.working_capital_turnover) : null,
                },
                // di comment dulu soalnya harus traversal ke belakang agar mendapatkan fixed aset sebelumnya
                // {
                //     label: 'Fixed Asset Turnover (FAT)',
                //     value: data?.fixed_asset_turnover != null ? parseVal(data.fixed_asset_turnover) : null,
                //     suffix: 'x',
                //     formula: 'Pendapatan / (aset total(sebelumnya) + aset total(sekarang)/ 2 )  ',
                //     breakdown: (labaRugi && neraca) ? `${formatNum(labaRugi.total_pendapatan)} / ${formatNum(neraca.total
                // },_asset_tetap)}` : null,
                //     rawResult: data?.fixed_asset_turnover != null ? parseVal(data.fixed_asset_turnover) : null,
            ]}
            narasi={data?.narasi_aktivitas_AI}
            section="aktivitas"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        />
    );
});
