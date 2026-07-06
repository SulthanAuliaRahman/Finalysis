import { Activity } from 'lucide-react';
import { TrendCardBase } from './TrendCardBase';
import { TabelPeriode, LineChartBlock } from './trendHelpers';

// Tabel row definitions
const RASIO_ROWS = [
    { label: 'Current Ratio', get: (a) => a?.likuiditas?.current_ratio,         suffix: 'x' },
    { label: 'Quick Ratio',   get: (a) => a?.likuiditas?.quick_ratio,            suffix: 'x' },
    { label: 'Cash Ratio',    get: (a) => a?.likuiditas?.cash_ratio,             suffix: 'x' },
    { label: 'NPM',           get: (a) => a?.profitabilitas?.net_profit_margin,  suffix: '%' },
    { label: 'ROA',           get: (a) => a?.profitabilitas?.ROA,                suffix: '%' },
    { label: 'ROE',           get: (a) => a?.profitabilitas?.ROE,                suffix: '%' },
    { label: 'DER',           get: (a) => a?.solvabilitas?.debt_to_equity,       suffix: '%' },
    { label: 'DAR',           get: (a) => a?.solvabilitas?.debt_to_asset,        suffix: '%' },
    { label: 'TATO',          get: (a) => a?.aktivitas?.total_asset_turnover,    suffix: 'x' },
].map((rasio) => ({
    label: rasio.label,
    render: (p) => {
        const val = rasio.get(p.analisis);
        return (
            <span className="text-slate-800 font-medium">
                {val !== null && val !== undefined ? `${val}${rasio.suffix}` : '—'}
            </span>
        );
    },
}));

// Chart line definitions

const LIKUIDITAS_LINES = [
    { key: 'cr',  label: 'Current Ratio', color: '#0ea5e9', get: (a) => a?.likuiditas?.current_ratio },
    { key: 'qr',  label: 'Quick Ratio',   color: '#6366f1', get: (a) => a?.likuiditas?.quick_ratio },
    { key: 'csr', label: 'Cash Ratio',    color: '#14b8a6', get: (a) => a?.likuiditas?.cash_ratio },
];

const PROFITABILITAS_LINES = [
    { key: 'npm', label: 'NPM', color: '#16a34a', get: (a) => a?.profitabilitas?.net_profit_margin },
    { key: 'roa', label: 'ROA', color: '#f59e0b', get: (a) => a?.profitabilitas?.ROA },
    { key: 'roe', label: 'ROE', color: '#dc2626', get: (a) => a?.profitabilitas?.ROE },
];

const SOLVABILITAS_LINES = [
    { key: 'der', label: 'DER', color: '#ef4444', get: (a) => a?.solvabilitas?.debt_to_equity },
    { key: 'dar', label: 'DAR', color: '#7f1d1d', get: (a) => a?.solvabilitas?.debt_to_asset },
];

const AKTIVITAS_LINES = [
    { key: 'tato', label: 'TATO', color: '#2563eb', get: (a) => a?.aktivitas?.total_asset_turnover },
];

export function TrendRasioCard({ data, perusahaanId, analisisId }) {
    const periodeData = data?.periode_data ?? [];
    const dataKurang = periodeData.length < 2;

    return (
        <TrendCardBase
            title="Tren Rasio Keuangan"
            icon={<Activity className="w-5 h-5" />}
            iconBgColor="bg-emerald-100"
            iconColor="text-emerald-600"
            section="trend_rasio"
            narasi={data?.narasi_rasio_AI}
            narasiLabel="Rasio"
            isDataIlustratif={data?.is_data_ilustratif ?? false}
            dataKurang={dataKurang}
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        >
            {/* Tabel gabung semua rasio */}
            <TabelPeriode
                title="Ringkasan Rasio"
                rows={RASIO_ROWS}
                periodeData={periodeData}
            />

            {/* 4 chart per kategori rasio */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
                <LineChartBlock
                    title="Tren Likuiditas"
                    periodeData={periodeData}
                    lines={LIKUIDITAS_LINES}
                    leftUnit="x"
                />
                <LineChartBlock
                    title="Tren Profitabilitas"
                    periodeData={periodeData}
                    lines={PROFITABILITAS_LINES}
                    leftUnit="%"
                />
                <LineChartBlock
                    title="Tren Solvabilitas"
                    periodeData={periodeData}
                    lines={SOLVABILITAS_LINES}
                    leftUnit="%"
                />
                <LineChartBlock
                    title="Tren Aktivitas"
                    periodeData={periodeData}
                    lines={AKTIVITAS_LINES}
                    leftUnit="x"
                />
            </div>
        </TrendCardBase>
    );
}
