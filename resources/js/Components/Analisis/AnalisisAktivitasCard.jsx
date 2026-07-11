import { Activity } from 'lucide-react';
import { RatioCardBase } from './RatioCardBase';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
const parseVal = (val) => val ? parseFloat(val) : 0;

export function AnalisisAktivitasCard({ data, neraca, labaRugi, perusahaanId, analisisId, sektor }) {

    let npmBenchmark = null;
    //temp
    if (sektor === "Jasa") npmBenchmark = 1;
    else if (sektor === "Manufaktur") npmBenchmark = 1;
    else if (sektor === "Perdagangan") npmBenchmark = 1;

    const chartData = [
        { name: 'TATO', value: parseVal(data?.total_asset_turnover), benchmark: 1.0 },
    ];

    return (
        <RatioCardBase
            title="Aktivitas"
            icon={<Activity className="w-5 h-5" />}
            iconBgColor="bg-orange-100"
            iconColor="text-orange-600"
            chartColor="#ea580c"
            chartData={chartData}
            ratios={[
                {
                    label: 'Total Asset Turnover (TATO)',
                    value:  data?.total_asset_turnover != null ? parseVal(data.total_asset_turnover) : null,
                    suffix: 'x',
                    formula: 'Pendapatan / Total Aset',
                    breakdown: (labaRugi && neraca) ? `${formatNum(labaRugi.pendapatan)} / ${formatNum(neraca.total_assets)}` : null,
                    rawResult: data?.total_asset_turnover != null ? parseVal(data.total_asset_turnover) : null,
                },
            ]}
            narasi={data?.narasi_aktivitas_AI}
            section="aktivitas"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        />
    );
}
