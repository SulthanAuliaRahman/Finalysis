import { Link, usePage } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Building2, Users, FileText, BarChart3, ArrowRight } from "lucide-react";
import { CompanyHeader } from "@/Components/Dashboard/CompanyHeader";
import { FinancialHealthOverview } from "@/Components/Dashboard/FinancialHealthOverview";
import { FinancialRatioOverview } from "@/Components/Dashboard/FinancialRatioOverview";
import { FinancialTrendChart } from "@/Components/Dashboard/FinancialTrendChart";
import { FinancialRatioTrendChart } from "@/Components/Dashboard/FinancialRatioTrendChart";
import { ReportSummary } from "@/Components/Dashboard/ReportSummary";

export default function Dashboard({ role, stats, recentPerusahaan = [], recentDokumen = [], dashboard }) {
    if (role === "super_admin") return <SuperAdminDashboard stats={stats} recentPerusahaan={recentPerusahaan} recentDokumen={recentDokumen} />;
    return <CompanyDashboard dashboard={dashboard} />;
}

function CompanyDashboard({ dashboard }) {
    const hasData = Boolean(dashboard?.selectedPeriod);
    return (
        <div className="mx-auto max-w-7xl space-y-8 pb-8">
            <CompanyHeader company={dashboard?.company} selectedPeriod={dashboard?.selectedPeriod} periodOptions={dashboard?.periodOptions ?? []} />
            {!hasData ? <EmptyState company={dashboard?.company} /> : <>
                <FinancialHealthOverview metrics={dashboard.financialOverview} />
                <FinancialRatioOverview ratios={dashboard.ratios} />
                <FinancialTrendChart data={dashboard.trendSeries} />
                <FinancialRatioTrendChart data={dashboard.trendSeries} />
                <ReportSummary summary={dashboard.selectedPeriod?.reportSummary} periodLabel={dashboard.selectedPeriod?.label} />
            </>}
        </div>
    );
}

function EmptyState({ company }) {
    const uploadUrl = company?.id ? `/perusahaan/${company.id}/dokumen/create` : "/";
    return <div className="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm">
        <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><FileText className="h-6 w-6" /></div>
        <h2 className="mt-4 text-lg font-semibold text-slate-900">Data keuangan belum siap ditampilkan</h2>
        <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">Unggah dan lengkapi Neraca serta Laba Rugi untuk melihat ringkasan kondisi keuangan perusahaan.</p>
        {company && <Link href={uploadUrl} className="mt-6 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Unggah Laporan</Link>}
    </div>;
}

function SuperAdminDashboard({ stats = {}, recentPerusahaan, recentDokumen }) {
    const cards = [
        ["Total Perusahaan", stats.total_perusahaan, Building2, "bg-blue-50 text-blue-600"],
        ["Total Akun", stats.total_users, Users, "bg-emerald-50 text-emerald-600"],
        ["Dokumen Terunggah", stats.total_dokumen, FileText, "bg-amber-50 text-amber-600"],
        ["Laporan Analisis", stats.total_analisis, BarChart3, "bg-indigo-50 text-indigo-600"],
    ];
    return <div className="mx-auto max-w-7xl space-y-6">
        <div><h1 className="text-2xl font-bold text-slate-900">Dashboard Platform</h1><p className="mt-1 text-sm text-slate-500">Ringkasan operasional Finalysis.</p></div>
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">{cards.map(([label, value, Icon, color]) => <div key={label} className="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div><p className="text-sm text-slate-500">{label}</p><p className="mt-1 text-2xl font-bold text-slate-900">{value ?? 0}</p></div><div className={`rounded-xl p-3 ${color}`}><Icon className="h-5 w-5" /></div></div>)}</div>
        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <ListCard title="Perusahaan Terbaru" items={recentPerusahaan} primary="nama" secondary={(item) => `${item.dokumen_count ?? 0} dokumen`} />
            <ListCard title="Dokumen Terbaru" items={recentDokumen} primary="nama_file" secondary={(item) => item.perusahaan?.nama ?? "—"} />
        </div>
    </div>;
}

function ListCard({ title, items, primary, secondary }) { return <section className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"><div className="border-b border-slate-100 px-5 py-4"><h2 className="font-semibold text-slate-900">{title}</h2></div>{items.length ? <div className="divide-y divide-slate-100">{items.map((item) => <div key={item.id} className="flex items-center justify-between px-5 py-4"><div><p className="text-sm font-medium text-slate-800">{item[primary]}</p><p className="mt-0.5 text-xs text-slate-500">{secondary(item)}</p></div><ArrowRight className="h-4 w-4 text-slate-300" /></div>)}</div> : <p className="p-6 text-sm text-slate-500">Belum ada data.</p>}</section>; }

Dashboard.layout = (page) => <AppLayout title="Dashboard">{page}</AppLayout>;
