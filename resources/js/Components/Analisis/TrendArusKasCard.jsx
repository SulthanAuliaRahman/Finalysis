import { Wallet } from 'lucide-react';
import { TrendCardBase } from './TrendCardBase';
import { TabelPeriode, LineChartBlock, formatNum } from './trendHelpers'; // Import formatNum dari helper kamu

// Tabel row definitions
const ARUS_KAS_ROWS = [
    {
        label: 'Kas Masuk',
        render: (p) => (
            <span className="text-emerald-600 font-medium">
                {formatNum(p.kas_masuk)}
            </span>
        )
    },
    {
        label: 'Kas Keluar',
        render: (p) => (
            <span className="text-rose-600 font-medium">
                {formatNum(p.kas_keluar)}
            </span>
        )
    },
    {
        label: 'Status Arus Kas',
        render: (p) => {
            // Pastikan data tidak kosong
            if (p.kas_masuk === undefined || p.kas_keluar === undefined || p.kas_masuk === null || p.kas_keluar === null) {
                return <span className="text-slate-400">—</span>;
            }

            const net = Number(p.kas_masuk) - Number(p.kas_keluar);

            if (net > 0) {
                // Gunakan tampilan "Badge" agar status lebih menonjol
                return (
                    <span className="px-2 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-600 text-[10px] rounded-full font-semibold uppercase tracking-wider">
                        Surplus
                    </span>
                );
            } else if (net < 0) {
                return (
                    <span className="px-2 py-0.5 bg-rose-50 border border-rose-200 text-rose-600 text-[10px] rounded-full font-semibold uppercase tracking-wider">
                        Defisit
                    </span>
                );
            } else {
                return (
                    <span className="px-2 py-0.5 bg-slate-100 border border-slate-200 text-slate-600 text-[10px] rounded-full font-semibold uppercase tracking-wider">
                        Break Even
                    </span>
                );
            }
        }
    },
];

const ARUS_KAS_LINES = [
    { key: 'kas_masuk', label: 'Kas Masuk', color: '#10b981', get: (a) => a?.kas_masuk },
    { key: 'kas_keluar', label: 'Kas Keluar', color: '#f43f5e', get: (a) => a?.kas_keluar },
];

export function TrendArusKasCard({ data, perusahaanId, analisisId }) {
    const periodeData = data?.periode_data ?? [];
    const dataKurang = periodeData.length < 2;
    const chartDataMapped = periodeData.map(p => ({
        ...p,
        analisis: {
            ...p.analisis,
            kas_masuk: p.kas_masuk,
            kas_keluar: p.kas_keluar
        }
    }));

    return (
        <TrendCardBase
            title="Tren Arus Kas"
            icon={<Wallet className="w-5 h-5" />}
            iconBgColor="bg-blue-100"
            iconColor="text-blue-600"
            section="trend_arus_kas"
            narasi={data?.narasi_arus_kas_AI}
            narasiLabel="Arus Kas"
            isDataIlustratif={data?.is_data_ilustratif ?? false}
            dataKurang={dataKurang}
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        >

            <TabelPeriode
                title="Ringkasan Pergerakan Kas"
                rows={ARUS_KAS_ROWS}
                periodeData={periodeData}
            />

            <div className="mt-6">
                <LineChartBlock
                    title="Grafik Kas Masuk vs Kas Keluar"
                    periodeData={chartDataMapped}
                    lines={ARUS_KAS_LINES}
                    leftUnit=""
                />
            </div>
        </TrendCardBase>
    );
}
