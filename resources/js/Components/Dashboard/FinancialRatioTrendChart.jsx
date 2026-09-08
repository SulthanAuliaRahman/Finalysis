import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from "recharts";
import { ChartNoAxesCombined } from "lucide-react";
import { formatPercent, formatRatio } from "./formatters";

const CHARTS = [
    { title: "Tren Likuiditas", unit: "ratio", lines: [{ key: "currentRatio", label: "Current Ratio", color: "#2563eb" }, { key: "cashRatio", label: "Cash Ratio", color: "#06b6d4" }] },
    { title: "Tren Profitabilitas", unit: "percent", lines: [{ key: "netProfitMargin", label: "Net Profit Margin", color: "#059669" }, { key: "roa", label: "ROA", color: "#d97706" }, { key: "roe", label: "ROE", color: "#7c3aed" }] },
    { title: "Tren Solvabilitas", unit: "percent", lines: [{ key: "debtToAsset", label: "Debt to Asset", color: "#dc2626" }, { key: "debtToEquity", label: "Debt to Equity", color: "#ea580c" }] },
];

export function FinancialRatioTrendChart({ data = [] }) {
    return <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"><div className="flex items-start gap-3"><div className="rounded-lg bg-violet-50 p-2 text-violet-600"><ChartNoAxesCombined className="h-5 w-5" /></div><div><h2 className="text-base font-semibold text-slate-900">Grafik Tren Rasio Keuangan</h2><p className="mt-1 text-sm text-slate-500">Maksimal lima periode sejenis sampai periode yang dipilih.</p></div></div>{data.length < 2 ? <div className="mt-6 flex h-56 items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50 text-sm text-slate-500">Minimal dua periode sejenis diperlukan untuk menampilkan tren rasio.</div> : <div className="mt-6 grid gap-8 xl:grid-cols-3">{CHARTS.map((chart) => <RatioChart key={chart.title} {...chart} data={data} />)}</div>}</section>;
}

function RatioChart({ title, unit, lines, data }) { const formatter = unit === "percent" ? formatPercent : formatRatio; return <div><h3 className="text-sm font-semibold text-slate-800">{title}</h3><div className="mt-3 h-60"><ResponsiveContainer width="100%" height="100%"><LineChart data={data} margin={{ top: 8, right: 14, left: 0, bottom: 0 }}><CartesianGrid strokeDasharray="3 3" stroke="#e2e8f0" vertical={false} /><XAxis dataKey="label" tick={{ fontSize: 11, fill: "#64748b" }} axisLine={false} tickLine={false} /><YAxis tickFormatter={formatter} tick={{ fontSize: 10, fill: "#64748b" }} axisLine={false} tickLine={false} width={54} /><Tooltip formatter={(value) => formatter(value)} contentStyle={{ borderRadius: 10, border: "1px solid #e2e8f0", fontSize: 12 }} /><Legend wrapperStyle={{ fontSize: 11, paddingTop: 10 }} />{lines.map((line) => <Line key={line.key} type="monotone" dataKey={line.key} name={line.label} stroke={line.color} strokeWidth={2.25} dot={{ r: 3 }} activeDot={{ r: 4 }} connectNulls={false} />)}</LineChart></ResponsiveContainer></div></div>; }
