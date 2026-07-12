import { TrendingUp, TrendingDown, Minus } from 'lucide-react';
import {
    LineChart as ReLineChart,
    Line,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer,
} from 'recharts';

// Formatters
export const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);

// decimal(12,6) Laravel dikembalikan sebagai string selalu konversi ke Number
export const toNum = (val) => val === null || val === undefined ? null : Number(val);

// Label periode
export function labelPeriode(analisis) {
    if (!analisis) return '—';
    if (analisis.periode_type === 'annual') return `${analisis.tahun}`;
    if (analisis.periode_type === 'quarterly') return `Q${analisis.quarter} ${analisis.tahun}`;
    return `Bln ${analisis.bulan} ${analisis.tahun}`;
}

export function GrowthBadge({ value }) {
    const hasValue = value !== null && value !== undefined;
    const isUp = hasValue && value > 0;
    const isDown = hasValue && value < 0;
    const Icon = isUp ? TrendingUp : isDown ? TrendingDown : Minus;
    const color = isUp ? 'text-green-600' : isDown ? 'text-red-500' : 'text-slate-400';

    if (!hasValue) return <span className="text-slate-300 text-[11px]">—</span>;

    // FIX: Membatasi persentase menjadi 2 angka di belakang koma dengan format id-ID
    const formattedValue = Number(Math.abs(value)).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    return (
        <span className={`inline-flex items-center gap-0.5 text-[11px] font-medium ${color}`}>
            <Icon className="w-3 h-3" />
            {value > 0 ? '+' : value < 0 ? '-' : ''}{formattedValue}%
        </span>
    );
}

export function TabelPeriode({ title, rows, periodeData }) {
    return (
        <>
            <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2">
                {title}
            </p>
            <div className="overflow-x-auto mb-3 -mx-1">
                <table className="w-full text-xs min-w-[500px]">
                    <thead>
                        <tr className="text-slate-400 border-b border-slate-100">
                            <th className="text-left py-1.5 px-1 font-medium">Item</th>
                            {periodeData.map((p) => (
                                <th key={p.urutan} className="text-right py-1.5 px-1 font-medium whitespace-nowrap">
                                    {labelPeriode(p.analisis)}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {rows.map((row) => (
                            <tr key={row.label} className="border-b border-slate-50 last:border-0">
                                <td className="py-1.5 px-1 text-slate-600">{row.label}</td>
                                {periodeData.map((p) => (
                                    <td key={p.urutan} className="text-right py-1.5 px-1">
                                        {row.render(p)}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </>
    );
}

export function LineChartBlock({
    title,
    periodeData,
    lines,
    dualAxis = false,
    leftUnit = '%',
    rightUnit = 'x',
}) {
    const chartData = periodeData.map((p) => {
        const row = { periode: labelPeriode(p.analisis) };
        lines.forEach((line) => {
            row[line.key] = toNum(line.get(p.analisis));
        });
        return row;
    });

    const adaData = lines.some((line) =>
        chartData.some((row) => row[line.key] !== null)
    );

    return (
        <div className="mb-4">
            <p className="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-2">
                {title}
            </p>
            {adaData ? (
                <div style={{ width: '100%', height: 220 }}>
                    <ResponsiveContainer>
                        <ReLineChart
                            data={chartData}
                            margin={{ top: 10, right: 10, left: 0, bottom: 0 }}
                        >
                            <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
                            <XAxis dataKey="periode" tick={{ fontSize: 11 }} />
                            <YAxis
                                yAxisId="left"
                                tick={{ fontSize: 11 }}
                                unit={leftUnit}
                                width={45}
                            />
                            {dualAxis && (
                                <YAxis
                                    yAxisId="right"
                                    orientation="right"
                                    tick={{ fontSize: 11 }}
                                    unit={rightUnit}
                                    width={45}
                                />
                            )}

                            <Tooltip
                                formatter={(val, name) => [
                                    Number(val).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                                    name
                                ]}
                            />

                            <Legend wrapperStyle={{ fontSize: '11px' }} />
                            {lines.map((line) => (
                                <Line
                                    key={line.key}
                                    yAxisId={line.axis || 'left'}
                                    type="monotone"
                                    dataKey={line.key}
                                    name={line.label}
                                    stroke={line.color}
                                    strokeWidth={2}
                                    dot={{ r: 3 }}
                                    connectNulls
                                />
                            ))}
                        </ReLineChart>
                    </ResponsiveContainer>
                </div>
            ) : (
                <div className="h-32 rounded-lg bg-slate-50 border border-dashed border-slate-200 flex items-center justify-center">
                    <span className="text-xs text-slate-400">Belum ada data untuk chart ini</span>
                </div>
            )}
        </div>
    );
}
