import { router } from "@inertiajs/react";
import { Building2, CheckCircle2, Clock3 } from "lucide-react";

export function CompanyHeader({ company, selectedPeriod, periodOptions }) {
    const changePeriod = (event) => router.get("/dashboard", { dokumen: event.target.value }, { preserveScroll: true, preserveState: true });
    return <header className="flex flex-col justify-between gap-4 border-b border-slate-200 pb-6 md:flex-row md:items-end">
        <div className="flex items-start gap-3"><div className="rounded-xl bg-blue-50 p-2.5 text-blue-600"><Building2 className="h-5 w-5" /></div><div><p className="text-sm font-medium text-blue-700">Ringkasan Keuangan</p><h1 className="mt-0.5 text-2xl font-bold tracking-tight text-slate-900">{company?.name ?? "Perusahaan"}</h1><p className="mt-1 text-sm text-slate-500">Analisis keuangan {selectedPeriod?.label ?? ""}</p></div></div>
        {selectedPeriod && <div className="flex flex-wrap items-center gap-3"><div className="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">{selectedPeriod.reportStatus === "selesai" ? <CheckCircle2 className="h-4 w-4 text-emerald-600" /> : <Clock3 className="h-4 w-4 text-amber-600" />}Status laporan: <span className="capitalize text-slate-700">{selectedPeriod.reportStatus ?? "tersedia"}</span></div><select aria-label="Pilih periode laporan" value={selectedPeriod.documentId} onChange={changePeriod} className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">{periodOptions.map((period) => <option key={period.documentId} value={period.documentId}>{period.label}</option>)}</select></div>}
    </header>;
}
