import { BarChart2 } from 'lucide-react';
import { TrendCardBase } from './TrendCardBase';
import { formatNum, GrowthBadge, TabelPeriode } from './trendHelpers';

// Helper baru untuk membulatkan desimal persentase
const formatPercentStr = (val) => {
    if (val === null || val === undefined) return null;
    // Ubah string/angka menjadi number, batasi 2 desimal
    return Number(val).toFixed(2);
};

// Row definitions
const AKUN_UTAMA = [
    { key: 'total_asset',       growthKey: 'growth_total_asset',        label: 'Total Aset' },
    { key: 'total_liabilities', growthKey: 'growth_total_liabilities',  label: 'Total Liabilitas' },
    { key: 'total_equitas',     growthKey: 'growth_total_equitas',      label: 'Total Ekuitas' },
    { key: 'total_pendapatan',  growthKey: 'growth_total_pendapatan',   label: 'Total Pendapatan' },
    { key: 'total_beban',       growthKey: 'growth_total_beban',        label: 'Total Beban' },
];

const ROWS = AKUN_UTAMA.map((akun) => ({
    label: akun.label,
    render: (p) => (
        <>
            <div className="text-slate-800 font-medium">{formatNum(p[akun.key])}</div>
            <GrowthBadge value={formatPercentStr(p[akun.growthKey])} />
        </>
    ),
}));

export function TrendAkunUtamaCard({ data, perusahaanId, analisisId }) {
    const periodeData = data?.periode_data ?? [];
    const dataKurang = periodeData.length < 2;
    const hasGap      = data?.has_gap ?? false;

    return (
        <TrendCardBase
            title="Tren Akun Utama"
            icon={<BarChart2 className="w-5 h-5" />}
            iconBgColor="bg-violet-100"
            iconColor="text-violet-600"
            section="trend_akun_utama"
            narasi={data?.narasi_trend_akun_utama_AI}
            narasiLabel="Akun Utama"
            hasGap={hasGap}
            dataKurang={dataKurang}
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        >
            <TabelPeriode
                title="Perbandingan Akun Utama"
                rows={ROWS}
                periodeData={periodeData}
            />
        </TrendCardBase>
    );
}
