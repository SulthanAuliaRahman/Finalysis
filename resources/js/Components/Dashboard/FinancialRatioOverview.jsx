import { ArrowDownRight, ArrowUpRight, Minus } from "lucide-react";
import { formatPercent, formatRatio } from "./formatters";

const GROUPS = [
    { name: "Likuiditas", accent: "bg-blue-500", keys: ["currentRatio", "cashRatio"] },
    { name: "Profitabilitas", accent: "bg-emerald-500", keys: ["netProfitMargin", "roa", "roe"] },
    { name: "Solvabilitas", accent: "bg-amber-500", keys: ["debtToAsset", "debtToEquity"] },
];

const PERCENT_RATIOS = ["netProfitMargin", "roa", "roe", "debtToAsset", "debtToEquity"];

export function FinancialRatioOverview({ ratios = [] }) {
    const ratioByKey = Object.fromEntries(ratios.map((ratio) => [ratio.key, ratio]));
    return <section className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"><div className="flex items-center justify-between border-b border-slate-100 px-6 py-5"><h2 className="text-base font-semibold text-slate-900">Rasio Keuangan</h2><span className="text-xs text-slate-400">Periode terpilih</span></div><div className="grid divide-y divide-slate-100 lg:grid-cols-3 lg:divide-x lg:divide-y-0">{GROUPS.map((group) => <RatioGroup key={group.name} group={group} ratios={group.keys.map((key) => ratioByKey[key]).filter(Boolean)} />)}</div></section>;
}

function RatioGroup({ group, ratios }) { return <div className="p-5"><div className="mb-4 flex items-center gap-2"><span className={`h-2 w-2 rounded-full ${group.accent}`} /><h3 className="text-xs font-bold uppercase tracking-wider text-slate-500">{group.name}</h3></div><div className="divide-y divide-slate-100">{ratios.map((ratio) => <RatioRow key={ratio.key} ratio={ratio} />)}</div></div>; }

function RatioRow({ ratio }) {
    const isPercent = PERCENT_RATIOS.includes(ratio.key);
    const value = isPercent ? formatPercent(ratio.value) : formatRatio(ratio.value);
    const delta = ratio.delta === null ? null : (isPercent ? formatPercent(Math.abs(ratio.delta)) : formatRatio(Math.abs(ratio.delta)));
    const Icon = ratio.trend === "up" ? ArrowUpRight : ratio.trend === "down" ? ArrowDownRight : Minus;
    const trendClass = ratio.trend === "down" ? "text-rose-600" : ratio.trend === "up" ? "text-emerald-600" : "text-slate-400";
    return <div className="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"><p className="text-sm font-medium text-slate-600">{ratio.label}</p><div className="text-right"><p className="text-base font-semibold tabular-nums text-slate-900">{value}</p>{delta ? <p className={`mt-0.5 flex items-center justify-end gap-0.5 text-xs font-medium ${trendClass}`}><Icon className="h-3.5 w-3.5" />{delta}</p> : <p className="mt-0.5 text-xs text-slate-400">—</p>}</div></div>;
}
