import { Link, usePage } from '@inertiajs/react'
import {
    LayoutDashboard,
    Building2,
    FileText,
    BarChart3,
    Settings2,
    ChevronLeft,
    ChevronRight,
    BrainCircuit,
    Users,
} from "lucide-react"

export default function Sidebar({ collapsed, setCollapsed, mobileOpen, setMobileOpen }) {
    const { url, props } = usePage();
    const userRole = props.auth?.user?.role;

    const navItems = [
        { href: "/dashboard", label: "Dashboard", icon: LayoutDashboard },
        ...(userRole === 'super_admin' ? [{ href: "/perusahaan", label: "Perusahaan", icon: Building2 }] : []),
        ...(userRole === 'super_admin' || userRole === 'manager' ? [{ href: "/users", label: "Kelola User", icon: Users }] : []),
        { href: "/settings/ai", label: "Konfigurasi AI", icon: Settings2 },
    ];

    return (
        <>
            {/* Mobile Overlay: Menutup sidebar saat area luar diklik di HP */}
            {mobileOpen && (
                <div
                    className="fixed inset-0 bg-black/50 z-40 md:hidden"
                    onClick={() => setMobileOpen(false)}
                />
            )}

            {/* Sidebar Container */}
            <aside
                className={`fixed md:relative z-50 md:z-auto flex flex-col h-full bg-slate-900 text-slate-300 border-r border-slate-800 transition-all duration-200 ${
                    collapsed ? "w-16" : "w-64"
                } ${
                    mobileOpen ? "translate-x-0" : "-translate-x-full md:translate-x-0"
                }`}
            >
                {/* Logo & Identitas Aplikasi */}
                <div className={`flex items-center gap-3 px-4 py-5 border-b border-slate-800 ${collapsed && "justify-center px-2"}`}>
                    <div className="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center">
                        <BrainCircuit className="w-4 h-4 text-white" />
                    </div>
                    {!collapsed && (
                        <div className="flex flex-col min-w-0">
                            <span className="text-sm text-white truncate font-semibold tracking-tight">
                                Finalisis
                            </span>
                            <span className="text-xs text-slate-500">RAG Keuangan</span>
                        </div>
                    )}
                </div>

                {/* Menu Navigasi */}
                <nav className="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                    {navItems.map(({ href, label, icon: Icon }) => {
                        // Memeriksa apakah URL rute saat ini cocok dengan menu
                        const isActive = url.startsWith(href);

                        return (
                            <Link
                                key={href}
                                href={href}
                                onClick={() => setMobileOpen(false)}
                                className={`flex items-center gap-3 px-3 py-2.5 rounded-md transition-all text-sm ${
                                    collapsed ? "justify-center px-2" : ""
                                } ${
                                    isActive
                                        ? "bg-blue-600/10 text-blue-400 font-medium"
                                        : "text-slate-400 hover:bg-slate-800/60 hover:text-white"
                                }`}
                                title={collapsed ? label : undefined}
                            >
                                <Icon className={`flex-shrink-0 ${collapsed ? "w-5 h-5" : "w-4 h-4"}`} />
                                {!collapsed && <span className="truncate">{label}</span>}
                            </Link>
                        );
                    })}
                </nav>

                {/* Tombol Collapse/Ciutkan (Hanya muncul di Desktop) */}
                <div className="hidden md:flex p-2 border-t border-slate-800">
                    <button
                        onClick={() => setCollapsed(!collapsed)}
                        className={`flex items-center justify-center w-full py-2 rounded-md text-slate-500 hover:text-white hover:bg-slate-800 transition-colors ${
                            collapsed ? "px-2" : "px-3 gap-2"
                        }`}
                    >
                        {collapsed ? (
                            <ChevronRight className="w-4 h-4" />
                        ) : (
                            <>
                                <ChevronLeft className="w-4 h-4" />
                                <span className="text-xs">Ciutkan</span>
                            </>
                        )}
                    </button>
                </div>
            </aside>
        </>
    );
}
