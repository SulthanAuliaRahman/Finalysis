import { Activity } from 'lucide-react';
import { RatioCardBase } from './RatioCardBase';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);
const parseVal = (val) => val ? Number((val / 100).toFixed(2)) : 0;

export function AnalisisAktivitasCard({ data, neraca, labaRugi, perusahaanId, analisisId }) {

    const chartData = [
        { name: 'TATO', value: parseVal(data?.total_asset_turnover), benchmark: 1.0 },
    ];

    return (
        <RatioCardBase
            title="Aktivitas"
            icon={<Activity className="w-5 h-5" />}
            iconBgColor="bg-orange-100"
            iconColor="text-orange-600"
            chartColor="#ea580c" // Warna Oranye Tailwind
            chartData={chartData}
            ratios={[
                {
                    label: 'Total Asset Turnover (TATO)',
                    value: data?.total_asset_turnover ?? null, suffix: '%',
                    formula: 'Pendapatan / Total Aset',
                    breakdown: (labaRugi && neraca) ? `${formatNum(labaRugi.pendapatan)} / ${formatNum(neraca.total_assets)}` : null
                },
            ]}
            narasi={data?.narasi_aktivitas_AI}
            section="aktivitas"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        />
    );
}
