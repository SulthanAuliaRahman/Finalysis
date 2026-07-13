import { Link, usePage } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { 
    Building2, 
    Users, 
    FileText, 
    BarChart3, 
    Plus, 
    ArrowRight, 
    Settings, 
    FileUp, 
    Compass,
    Activity,
    FolderOpen
} from "lucide-react";
import { cn } from "@/lib/utils";

// Mapping Sektor untuk Company Card
const SEKTOR_COLOR = {
    Manufaktur: "bg-amber-50 text-amber-700 border-amber-200",
    Jasa: "bg-blue-50 text-blue-700 border-blue-200",
    Perdagangan: "bg-pink-50 text-pink-700 border-pink-200",
    Lainnya: "bg-slate-50 text-slate-700 border-slate-200",
};

// Badges untuk Status Dokumen
const STATUS_BADGES = {
    draft: "bg-slate-100 text-slate-700 border-slate-200",
    chunked: "bg-indigo-50 text-indigo-700 border-indigo-200",
    embedded: "bg-emerald-50 text-emerald-700 border-emerald-200",
};

export default function Dashboard({ role, stats, ...props }) {
    const { auth } = usePage().props;
    const currentUser = auth?.user;

    if (role === "super_admin") {
        return (
            <SuperAdminDashboard 
                user={currentUser} 
                stats={stats} 
                recentPerusahaan={props.recentPerusahaan} 
                recentDokumen={props.recentDokumen} 
            />
        );
    }

    return (
        <CompanyDashboard 
            user={currentUser} 
            role={role} 
            perusahaan={props.perusahaan} 
            stats={stats} 
            recentDokumen={props.recentDokumen} 
            recentAnalisis={props.recentAnalisis} 
        />
    );
}

// -------------------------------------------------------------
// 1. DASHBOARD SUPER ADMIN
// -------------------------------------------------------------
function SuperAdminDashboard({ user, stats, recentPerusahaan, recentDokumen }) {
    return (
        <div className="space-y-8">
            {/* Welcoming Header */}
            <div>
                <h1 className="text-2xl font-bold text-slate-900">Halo, {user.name} 👋</h1>
                <p className="text-xs text-slate-500 mt-1">
                    Sistem RAG Analisis Laporan Keuangan Keuangan terpusat. Kelola konfigurasi dan korporasi terdaftar di bawah ini.
                </p>
            </div>

            {/* Stats Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                {/* Total Perusahaan */}
                <div className="bg-white border border-slate-200 rounded-xl p-5 flex items-center justify-between shadow-xs">
                    <div className="space-y-1">
                        <p className="text-xs text-slate-400 font-medium">Total Perusahaan</p>
                        <h3 className="text-2xl font-bold text-slate-900">{stats.total_perusahaan}</h3>
                    </div>
                    <div className="w-12 h-12 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                        <Building2 className="w-6 h-6" />
                    </div>
                </div>

                {/* Total Users */}
                <div className="bg-white border border-slate-200 rounded-xl p-5 flex items-center justify-between shadow-xs">
                    <div className="space-y-1">
                        <p className="text-xs text-slate-400 font-medium">Total Akun</p>
                        <h3 className="text-2xl font-bold text-slate-900">{stats.total_users}</h3>
                    </div>
                    <div className="w-12 h-12 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                        <Users className="w-6 h-6" />
                    </div>
                </div>

                {/* Total Dokumen */}
                <div className="bg-white border border-slate-200 rounded-xl p-5 flex items-center justify-between shadow-xs">
                    <div className="space-y-1">
                        <p className="text-xs text-slate-400 font-medium">Dokumen dTerunggah</p>
                        <h3 className="text-2xl font-bold text-slate-900">{stats.total_dokumen}</h3>
                    </div>
                    <div className="w-12 h-12 bg-amber-50 border border-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                        <FileText className="w-6 h-6" />
                    </div>
                </div>

                {/* Total Analisis */}
                <div className="bg-white border border-slate-200 rounded-xl p-5 flex items-center justify-between shadow-xs">
                    <div className="space-y-1">
                        <p className="text-xs text-slate-400 font-medium">Laporan Analisis AI</p>
                        <h3 className="text-2xl font-bold text-slate-900">{stats.total_analisis}</h3>
                    </div>
                    <div className="w-12 h-12 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-center text-indigo-600">
                        <BarChart3 className="w-6 h-6" />
                    </div>
                </div>
            </div>

            {/* Split Panels */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Left Area: Perusahaan & Dokumen */}
                <div className="lg:col-span-2 space-y-6">
                    {/* Perusahaan Terbaru */}
                    <div className="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                        <div className="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                            <h3 className="text-sm font-bold text-slate-900">Perusahaan Baru Terdaftar</h3>
                            <Link href="/perusahaan" className="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1">
                                Lihat Semua <ArrowRight className="w-3.5 h-3.5" />
                            </Link>
                        </div>
                        <div className="divide-y divide-slate-100">
                            {recentPerusahaan.length === 0 ? (
                                <div className="p-6 text-center text-xs text-slate-400">Belum ada perusahaan.</div>
                            ) : (
                                recentPerusahaan.map((p) => (
                                    <div key={p.id} className="p-4 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500">
                                                <Building2 className="w-4 h-4" />
                                            </div>
                                            <div>
                                                <h4 className="text-xs font-bold text-slate-800">{p.nama}</h4>
                                                <span className="text-[10px] text-slate-400">{p.dokumen_count || 0} berkas dokumen</span>
                                            </div>
                                        </div>
                                        <Badge variant="outline" className={cn("text-[9px] py-0", SEKTOR_COLOR[p.sektor] || SEKTOR_COLOR.Lainnya)}>
                                            {p.sektor}
                                        </Badge>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>

                    {/* Dokumen Terbaru */}
                    <div className="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                        <div className="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                            <h3 className="text-sm font-bold text-slate-900">Dokumen Terunggah Terakhir</h3>
                            <span className="text-[10px] text-slate-400">Seluruh Platform</span>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-100">
                                <thead className="bg-slate-50/80">
                                    <tr>
                                        <th className="px-5 py-2.5 text-left text-[10px] font-semibold text-slate-500">Nama Dokumen</th>
                                        <th className="px-5 py-2.5 text-left text-[10px] font-semibold text-slate-500">Perusahaan</th>
                                        <th className="px-5 py-2.5 text-left text-[10px] font-semibold text-slate-500">Tahun</th>
                                        <th className="px-5 py-2.5 text-right text-[10px] font-semibold text-slate-500">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {recentDokumen.length === 0 ? (
                                        <tr>
                                            <td colSpan={4} className="p-6 text-center text-xs text-slate-400">Belum ada dokumen yang diunggah.</td>
                                        </tr>
                                    ) : (
                                        recentDokumen.map((d) => (
                                            <tr key={d.id} className="hover:bg-slate-50/50 transition-colors">
                                                <td className="px-5 py-3 whitespace-nowrap text-xs font-medium text-slate-800 truncate max-w-[200px]">
                                                    {d.nama_file}
                                                </td>
                                                <td className="px-5 py-3 whitespace-nowrap text-xs text-slate-600">
                                                    {d.perusahaan?.nama || "—"}
                                                </td>
                                                <td className="px-5 py-3 whitespace-nowrap text-xs text-slate-500">
                                                    {d.tahun}
                                                </td>
                                                <td className="px-5 py-3 whitespace-nowrap text-right">
                                                    <Badge variant="outline" className={cn("text-[9px] font-semibold py-0 border capitalize", STATUS_BADGES[d.status] || STATUS_BADGES.draft)}>
                                                        {d.status}
                                                    </Badge>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {/* Right Area: Quick Action Panel */}
                <div className="space-y-6">
                    

                    {/* <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-xs space-y-4">
                        <h4 className="text-xs font-bold text-slate-700 flex items-center gap-1.5 uppercase tracking-wider">
                            <Activity className="w-4 h-4 text-emerald-500" /> Status Koneksi RAG
                        </h4>
                        <div className="space-y-3 pt-1">
                            <div className="flex justify-between items-center text-xs">
                                <span className="text-slate-500">Koneksi Database</span>
                                <span className="font-semibold text-emerald-600 flex items-center gap-1">
                                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-500" /> Terkoneksi
                                </span>
                            </div>
                            <div className="flex justify-between items-center text-xs">
                                <span className="text-slate-500">Vector Store (ChromaDB)</span>
                                <span className="font-semibold text-emerald-600 flex items-center gap-1">
                                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-500" /> Aktif
                                </span>
                            </div>
                            <div className="flex justify-between items-center text-xs">
                                <span className="text-slate-500">Python RAG Worker</span>
                                <span className="font-semibold text-emerald-600 flex items-center gap-1">
                                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-500" /> Sehat (Online)
                                </span>
                            </div>
                        </div>
                    </div> */}
                </div>
            </div>
        </div>
    );
}

// -------------------------------------------------------------
// 2. DASHBOARD PERUSAHAAN (KORPORAT)
// -------------------------------------------------------------
function CompanyDashboard({ user, role, perusahaan, stats, recentDokumen, recentAnalisis }) {
    return (
        <div className="space-y-8">
            {/* Welcoming Header */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">{perusahaan?.nama || "Perusahaan"} 🏢</h1>
                    <p className="text-xs text-slate-500 mt-1 flex items-center gap-2">
                        <span>Portal Analisis Laporan Keuangan Korporasi</span>
                        <span>•</span>
                        <Badge variant="outline" className={cn("text-[9px] py-0 border", SEKTOR_COLOR[perusahaan?.sektor] || SEKTOR_COLOR.Lainnya)}>
                            {perusahaan?.sektor || "Sektor Umum"}
                        </Badge>
                    </p>
                </div>

                <div className="flex gap-2">
                    <Link href={`/perusahaan/${perusahaan?.id}/dokumen/create`}>
                        <Button className="shadow-xs gap-1.5 text-xs h-9">
                            <FileUp className="w-3.5 h-3.5" /> Unggah Laporan
                        </Button>
                    </Link>
                </div>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
                {/* Dokumen Perusahaan */}
                <div className="bg-white border border-slate-200 rounded-xl p-5 flex items-center justify-between shadow-xs">
                    <div className="space-y-1">
                        <p className="text-xs text-slate-400 font-medium">Berkas Keuangan</p>
                        <h3 className="text-2xl font-bold text-slate-900">{stats.total_dokumen} PDF</h3>
                    </div>
                    <div className="w-12 h-12 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                        <FileText className="w-6 h-6" />
                    </div>
                </div>

                {/* Analisis Terbuat */}
                <div className="bg-white border border-slate-200 rounded-xl p-5 flex items-center justify-between shadow-xs">
                    <div className="space-y-1">
                        <p className="text-xs text-slate-400 font-medium">Laporan Analisis AI</p>
                        <h3 className="text-2xl font-bold text-slate-900">{stats.total_analisis} Terbuat</h3>
                    </div>
                    <div className="w-12 h-12 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-center text-indigo-600">
                        <BarChart3 className="w-6 h-6" />
                    </div>
                </div>

                {/* Pengguna / Anggota Tim */}
                <div className="bg-white border border-slate-200 rounded-xl p-5 flex items-center justify-between shadow-xs">
                    <div className="space-y-1">
                        <p className="text-xs text-slate-400 font-medium">Anggota Tim Analis</p>
                        <h3 className="text-2xl font-bold text-slate-900">{stats.total_users} Anggota</h3>
                    </div>
                    <div className="w-12 h-12 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center justify-center text-emerald-600">
                        <Users className="w-6 h-6" />
                    </div>
                </div>
            </div>

            {/* Split panels */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Left Panel: Dokumen Baru di Perusahaan */}
                <div className="lg:col-span-2 space-y-6">
                    <div className="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                        <div className="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                            <h3 className="text-sm font-bold text-slate-900">Arsip Laporan Keuangan Terakhir</h3>
                            <Link href={`/perusahaan/${perusahaan?.id}/dokumen`} className="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1">
                                Buka Dokumen <ArrowRight className="w-3.5 h-3.5" />
                            </Link>
                        </div>
                        <div className="divide-y divide-slate-100">
                            {recentDokumen.length === 0 ? (
                                <div className="p-8 text-center text-xs text-slate-400 flex flex-col items-center justify-center gap-2">
                                    <FolderOpen className="w-8 h-8 text-slate-300" />
                                    <span>Belum ada dokumen yang diunggah untuk korporasi ini.</span>
                                </div>
                            ) : (
                                recentDokumen.map((d) => (
                                    <div key={d.id} className="p-4 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                                        <div className="flex items-center gap-3">
                                            <div className="w-8 h-8 rounded bg-red-50 border border-red-100 flex items-center justify-center text-red-500 font-mono text-[9px] font-bold">
                                                PDF
                                            </div>
                                            <div>
                                                <h4 className="text-xs font-bold text-slate-800 truncate max-w-[250px]">{d.nama_file}</h4>
                                                <span className="text-[10px] text-slate-400">Periode: {d.periode || d.tahun}</span>
                                            </div>
                                        </div>
                                        <Badge variant="outline" className={cn("text-[9px] py-0 border capitalize", STATUS_BADGES[d.status] || STATUS_BADGES.draft)}>
                                            {d.status}
                                        </Badge>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>

                {/* Right Panel: Analisis & Tim */}
                <div className="space-y-6">
                    {/* Analisis AI Terakhir */}
                    <div className="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                        <div className="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                            <h3 className="text-sm font-bold text-slate-900">Analisis AI Terakhir</h3>
                            <Link href={`/perusahaan/${perusahaan?.id}/analisis`} className="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1">
                                Seluruh Analisis <ArrowRight className="w-3.5 h-3.5" />
                            </Link>
                        </div>
                        <div className="divide-y divide-slate-100">
                            {recentAnalisis.length === 0 ? (
                                <div className="p-8 text-center text-xs text-slate-400">
                                    Belum ada hasil analisis.
                                </div>
                            ) : (
                                recentAnalisis.map((a) => (
                                    <div key={a.id} className="p-4 flex items-center justify-between hover:bg-slate-50/50 transition-colors">
                                        <div>
                                            <h4 className="text-xs font-bold text-slate-800">Analisis Keuangan</h4>
                                            <span className="text-[10px] text-slate-400">Periode: {a.periode || a.tahun}</span>
                                        </div>
                                        <Link href={`/perusahaan/${perusahaan?.id}/analisis/${a.id}`}>
                                            <Button variant="ghost" size="sm" className="h-7 text-[10px] text-blue-600 border border-blue-200 hover:bg-blue-50 px-2.5">
                                                Buka Laporan
                                            </Button>
                                        </Link>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>

                    {/* Akses Cepat */}
                    {role === "manager" && (
                        <div className="bg-blue-50 border border-blue-100 rounded-xl p-5 shadow-xs space-y-3">
                            <h3 className="text-xs font-bold text-blue-800">Akses Manajer Tim</h3>
                            <p className="text-[11px] text-blue-600 leading-relaxed">
                                Tambahkan atau perbarui data akun tim analis pada perusahaan Anda untuk berkolaborasi dalam RAG.
                            </p>
                            <Link href="/users" className="block">
                                <Button className="w-full text-xs bg-blue-600 hover:bg-blue-700 text-white border-0" size="sm">
                                    <Users className="w-3.5 h-3.5 mr-1.5" /> Kelola Anggota Tim
                                </Button>
                            </Link>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

Dashboard.layout = (page) => (
    <AppLayout title="Dashboard Utama">
        {page}
    </AppLayout>
);
