import { PieChart } from 'lucide-react';
import { forwardRef } from 'react';
import { TrendCardBase } from './TrendCardBase';
import { TabelPeriode, LineChartBlock } from './trendHelpers';

const COMMONSIZE_ROWS = [
    { label: 'HPP',                    get: (a) => a?.commonsize?.hpp_persen },
    { label: 'Laba Kotor',             get: (a) => a?.commonsize?.laba_kotor_persen },
    { label: 'Beban Lain & Pajak',     get: (a) => a?.commonsize?.beban_lain_pajak_persen },
    { label: 'Laba Bersih',            get: (a) => a?.commonsize?.laba_bersih_persen },
    { label: 'Aset Lancar',            get: (a) => a?.commonsize?.aset_lancar_persen },
    { label: 'Aset Tetap',             get: (a) => a?.commonsize?.aset_tetap_persen },
    { label: 'Liabilitas Lancar',      get: (a) => a?.commonsize?.liabilitas_lancar_persen },
    { label: 'Liabilitas Jk. Panjang', get: (a) => a?.commonsize?.liabilitas_panjang_persen },
    { label: 'Ekuitas',                get: (a) => a?.commonsize?.ekuitas_persen },
].map((r) => ({
    label: r.label,
    render: (p) => {
        const val = r.get(p.analisis);
        return (
            <span className="text-slate-800 font-medium">
                {val !== null && val !== undefined ? `${val}%` : '—'}
            </span>
        );
    },
}));

const LABA_RUGI_LINES = [
    { key: 'hpp',         label: 'HPP',                 color: '#f97316', get: (a) => a?.commonsize?.hpp_persen },
    { key: 'laba_kotor',  label: 'Laba Kotor',         color: '#a3e635', get: (a) => a?.commonsize?.laba_kotor_persen },
    { key: 'beban_lain',  label: 'Beban Lain & Pajak', color: '#ef4444', get: (a) => a?.commonsize?.beban_lain_pajak_persen },
    { key: 'laba_bersih', label: 'Laba Bersih',        color: '#16a34a', get: (a) => a?.commonsize?.laba_bersih_persen },
];

const NERACA_LINES = [
    { key: 'aset_lancar',  label: 'Aset Lancar',             color: '#3b82f6', get: (a) => a?.commonsize?.aset_lancar_persen },
    { key: 'aset_tetap',   label: 'Aset Tetap',              color: '#1e3a8a', get: (a) => a?.commonsize?.aset_tetap_persen },
    { key: 'liab_lancar',  label: 'Liabilitas Lancar',       color: '#eab308', get: (a) => a?.commonsize?.liabilitas_lancar_persen },
    { key: 'liab_panjang', label: 'Liabilitas Jk. Panjang',  color: '#f97316', get: (a) => a?.commonsize?.liabilitas_panjang_persen },
    { key: 'ekuitas',      label: 'Ekuitas',                 color: '#a855f7', get: (a) => a?.commonsize?.ekuitas_persen },
];

export const TrendCommonsizeCard = forwardRef(function TrendCommonsizeCard({ data, perusahaanId, analisisId, referenceDocuments }, ref) {
    const periodeData = data?.periode_data ?? [];
    const dataKurang  = periodeData.length < 2;
    const hasGap      = data?.has_gap ?? false;

    return (
        <TrendCardBase
            title="Tren Common-size"
            icon={<PieChart className="w-5 h-5" />}
            iconBgColor="bg-cyan-100"
            iconColor="text-cyan-600"
            section="trend_commonsize"
            narasi={data?.narasi_trend_commonsize_AI}
            narasiLabel="Common-size"
            hasGap={hasGap}
            dataKurang={dataKurang}
            perusahaanId={perusahaanId}
            analisisId={analisisId}
            referenceDocuments={referenceDocuments}
        >
            <TabelPeriode
                title="Ringkasan Common-size"
                rows={COMMONSIZE_ROWS}
                periodeData={periodeData}
            />

            <div ref={ref} className="grid grid-cols-1 sm:grid-cols-2 gap-x-4 w-full bg-white mt-4 pb-2">
                <LineChartBlock
                    title="Laba Rugi — basis Pendapatan"
                    periodeData={periodeData}
                    lines={LABA_RUGI_LINES}
                    leftUnit="%"
                />
                <LineChartBlock
                    title="Neraca — basis Total Aset"
                    periodeData={periodeData}
                    lines={NERACA_LINES}
                    leftUnit="%"
                />
            </div>
        </TrendCardBase>
    );
});
