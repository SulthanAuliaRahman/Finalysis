import { useState } from "react";
import { Menu, X } from "lucide-react";
import Sidebar from "@/Components/Sidebar";

export default function AppLayout({ children, title }) {
    const [collapsed, setCollapsed] = useState(false);
    const [mobileOpen, setMobileOpen] = useState(false);

    return (
        <div className="flex h-screen bg-slate-50 overflow-hidden font-sans">
            {/* Mengirim state kontrol ke komponen Sidebar */}
            <Sidebar
                collapsed={collapsed}
                setCollapsed={setCollapsed}
                mobileOpen={mobileOpen}
                setMobileOpen={setMobileOpen}
            />

            {/* Area Konten Kanan */}
            <div className="flex flex-col flex-1 min-w-0 overflow-hidden">
                {/* Header Navbar atas */}
                <header className="flex items-center gap-3 px-4 md:px-6 py-3.5 border-b border-slate-200 bg-white flex-shrink-0">
                    {/* Tombol Menu khusus tampilan HP / Mobile */}
                    <button
                        className="md:hidden p-1.5 rounded-md text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors"
                        onClick={() => setMobileOpen(!mobileOpen)}
                    >
                        {mobileOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
                    </button>

                    <div className="flex-1 min-w-0">
                        <h1 className="text-slate-900 truncate font-semibold text-base">
                            {title || "Finalisis"}
                        </h1>
                        <p className="text-xs text-slate-500 hidden sm:block">
                            Analisis Laporan Keuangan Berbasis LLM + RAG
                        </p>
                    </div>

                    {/* Status RAG Badge */}
                    TODO: Ubah Jadi Logout
                    {/* <div className="flex items-center gap-2">
                        <div className="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-50 border border-blue-100">
                            <span className="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse" />
                            <span className="text-xs text-blue-700 hidden sm:inline font-mono">
                                RAG Active
                            </span>
                        </div>
                    </div> */}
                </header>

                {/* Area Halaman/Page Content */}
                <main className="flex-1 overflow-y-auto p-4 md:p-6">
                    {children}
                </main>
            </div>
        </div>
    );
}
