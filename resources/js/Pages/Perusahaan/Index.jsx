import { useState, useEffect } from "react";
import { router, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import CompanyCard from "@/Components/Perusahaan/CompanyCard";
import { Plus, Search, Building2 } from "lucide-react";
import { Button } from "@/Components/ui/button";

export default function Index({ perusahaanList, filters }) {
    const [search, setSearch] = useState(filters.search || "");

    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            router.get("/perusahaan", { search: search }, { preserveState: true, replace: true });
        }, 300);

        return () => clearTimeout(delayDebounceFn);
    }, [search]);

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row gap-4 sm:items-center justify-between">
                <div className="relative w-full max-w-sm">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input
                        type="text"
                        placeholder="Cari nama perusahaan atau sektor..."
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        className="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white transition-colors"
                    />
                </div>

                {/* Navigasi Link Menuju Halaman Create */}
                <Link href="/perusahaan/create">
                    <Button className="flex-shrink-0 shadow-xs">
                        <Plus className="w-4 h-4 mr-1.5" />
                        Tambah Perusahaan
                    </Button>
                </Link>
            </div>

            {perusahaanList.length === 0 ? (
                <div className="flex flex-col items-center justify-center py-20 gap-3 text-center bg-white border border-dashed border-slate-300 rounded-xl">
                    <Building2 className="w-10 h-10 text-slate-300" />
                    <p className="text-slate-500 text-sm">Belum ada korporasi terdaftar di database.</p>
                    <Link href="/perusahaan/create" className="mt-1">
                        <Button variant="outline" size="sm">Buat Perusahaan Pertama</Button>
                    </Link>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    {perusahaanList.map((perusahaan) => (
                        <CompanyCard key={perusahaan.id} perusahaan={perusahaan} />
                    ))}
                </div>
            )}
        </div>
    );
}

Index.layout = page => <AppLayout title="Data Perusahaan" children={page} />;
