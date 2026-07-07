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
    { key: 'pendapatan',      growthKey: 'growth_pendapatan',      label: 'Pendapatan' },
    { key: 'laba_kotor',      growthKey: 'growth_laba_kotor',      label: 'Laba Kotor' },
    { key: 'laba_bersih',     growthKey: 'growth_laba_bersih',     label: 'Laba Bersih' },
    { key: 'total_assets',    growthKey: 'growth_total_assets',    label: 'Total Aset' },
    { key: 'kas_setara_kas',  growthKey: 'growth_kas_setara_kas',  label: 'Kas & Setara Kas' },
    { key: 'total_equity',    growthKey: 'growth_total_equity',    label: 'Total Ekuitas' },
    { key: 'net_cash_flow',   growthKey: 'growth_net_cash_flow',   label: 'Net Cash Flow' },
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
