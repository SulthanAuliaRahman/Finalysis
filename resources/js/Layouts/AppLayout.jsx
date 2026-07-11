import { useState } from "react";
import { Menu, X } from "lucide-react";
import Sidebar from "@/Components/Sidebar";
import Dropdown from "@/Components/Dropdown";
import { usePage } from "@inertiajs/react";

export default function AppLayout({ children, title }) {
    const [collapsed, setCollapsed] = useState(false);
    const [mobileOpen, setMobileOpen] = useState(false);
    const { auth } = usePage().props;
    const user = auth?.user;
    const {flash} = usePage().props;
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

                    {/* Dropdown Profile & Logout */}
                    {user && (
                        <div className="relative">
                            <Dropdown>
                                <Dropdown.Trigger>
                                    <span className="inline-flex rounded-md">
                                        <button
                                            type="button"
                                            className="inline-flex items-center rounded-md border border-transparent bg-white px-2 py-1.5 text-sm font-medium leading-4 text-slate-700 transition duration-150 ease-in-out hover:text-slate-900 focus:outline-none focus:bg-slate-50 gap-2"
                                        >
                                            <span className="w-7 h-7 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-600 text-xs uppercase">
                                                {user.name.slice(0, 2)}
                                            </span>
                                            <span className="hidden sm:inline text-xs text-slate-600 font-semibold">{user.name}</span>
                                            <svg
                                                className="h-3.5 h-3.5 text-slate-400"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fillRule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clipRule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </Dropdown.Trigger>

                                <Dropdown.Content>
                                    <Dropdown.Link href={route("profile.edit")}>
                                        Profile
                                    </Dropdown.Link>
                                    <Dropdown.Link
                                        href={route("logout")}
                                        method="post"
                                        as="button"
                                    >
                                        Log Out
                                    </Dropdown.Link>
                                </Dropdown.Content>
                            </Dropdown>
                        </div>
                    )}
                </header>

                {/* Flash Message */}
                {flash?.success && (
                    <div className="mx-6 mt-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {flash.success}
                    </div>
                )}

                {flash?.error && (
                    <div className="mx-6 mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {flash.error}
                    </div>
                )}
                {/* Area Halaman/Page Content */}
                <main className="flex-1 overflow-y-auto p-4 md:p-6">
                    {children}
                </main>
            </div>
        </div>
    );
}
