import { useState } from "react";
import AppLayout from "@/Layouts/AppLayout";
import CompanyCard from "@/Components/Perusahaan/CompanyCard";
import { Plus, Search, Building2 } from "lucide-react";
import { Button } from "@/Components/ui/button";

// Data Dummy
const DUMMY_DATA = [
    { id: "p1", nama: "PT Maju Bersama Tbk", sektor: "Manufaktur", deskripsi: "Perusahaan manufaktur komponen otomotif terbesar di Jawa Barat.", tanggalDibuat: "2024-01-15", dokCount: 12, analisisCount: 3, skorKesehatan: "85/100" },
    { id: "p2", nama: "Nusantara Tech", sektor: "Teknologi", deskripsi: "Startup pengembang solusi AI untuk perbankan.", tanggalDibuat: "2024-02-20", dokCount: 4, analisisCount: 1, skorKesehatan: "92/100" },
    { id: "p3", nama: "Bank Syariah Sejahtera", sektor: "Keuangan", deskripsi: "Bank syariah dengan fokus pada pembiayaan UMKM.", tanggalDibuat: "2024-03-05", dokCount: 24, analisisCount: 8, skorKesehatan: "78/100" },
    { id: "p4", nama: "Agro Tani Abadi", sektor: "Pertanian", deskripsi: "Distributor pupuk dan alat berat pertanian.", tanggalDibuat: "2024-04-10", dokCount: 0, analisisCount: 0, skorKesehatan: null },
];

export default function Index() {
    const [search, setSearch] = useState("");

    // Filter data berdasarkan nama atau sektor
    const filteredPerusahaan = DUMMY_DATA.filter(p =>
        p.nama.toLowerCase().includes(search.toLowerCase()) ||
        p.sektor.toLowerCase().includes(search.toLowerCase())
    );

    return (
        <div className="space-y-6">
            {/* Action Bar: Search & Tambah */}
            <div className="flex flex-col sm:flex-row gap-4 sm:items-center justify-between">
                <div className="relative w-full max-w-sm">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input
                        type="text"
                        placeholder="Cari nama perusahaan atau sektor..."
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        className="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors"
                    />
                </div>

                <Button className="flex-shrink-0" onClick={() => console.log('Buka Modal Tambah')}>
                    <Plus className="w-4 h-4 mr-1.5" />
                    Tambah Perusahaan
                </Button>
            </div>

            {/* Grid Layout Cards */}
            {filteredPerusahaan.length === 0 ? (
                <div className="flex flex-col items-center justify-center py-20 gap-3 text-center bg-white border border-dashed border-slate-300 rounded-xl">
                    <Building2 className="w-10 h-10 text-slate-300" />
                    <p className="text-slate-500 text-sm">Tidak ada perusahaan yang ditemukan.</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    {filteredPerusahaan.map((perusahaan) => (
                        <CompanyCard key={perusahaan.id} perusahaan={perusahaan} />
                    ))}
                </div>
            )}
        </div>
    );
}

// Persistent Layout Setup
Index.layout = page => <AppLayout title="Data Perusahaan" children={page} />;
