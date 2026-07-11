import { useForm, Link, usePage } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { ArrowLeft, Loader2, Save } from "lucide-react";
import { useEffect } from "react";

export default function Create({ perusahaanList }) {
    const { auth } = usePage().props;
    const currentUser = auth?.user;
    const isManager = currentUser?.role === "manager";

    const { data, setData, post, processing, errors } = useForm({
        name: "",
        email: "",
        password: "",
        role: isManager ? "user" : "",
        perusahaan_id: isManager ? (currentUser?.perusahaan_id || "") : "",
        is_active: 1, // 1 = Aktif, 0 = Nonaktif
    });

    // Auto-select perusahaan if there's only one option
    useEffect(() => {
        if (perusahaanList.length === 1 && !data.perusahaan_id) {
            setData("perusahaan_id", perusahaanList[0].id.toString());
        }
    }, [perusahaanList]);

    function handleSubmit(e) {
        e.preventDefault();
        post("/users");
    }

    return (
        <div className="max-w-2xl mx-auto space-y-4">
            {/* Tombol Kembali */}
            <Link 
                href="/users" 
                className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors"
            >
                <ArrowLeft className="w-3.5 h-3.5" /> Kembali ke Daftar Pengguna
            </Link>

            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-6">
                <div>
                    <h2 className="text-lg font-bold text-slate-900">Tambah Pengguna Baru</h2>
                    <p className="text-xs text-slate-500 mt-0.5">Lengkapi data akun untuk mendaftarkan pengguna baru.</p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Nama Lengkap */}
                    <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-semibold text-slate-700" htmlFor="name">
                            Nama Lengkap <span className="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            type="text"
                            placeholder="Nama Lengkap"
                            value={data.name}
                            onChange={e => setData("name", e.target.value)}
                            className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
                            disabled={processing}
                        />
                        {errors.name && <p className="text-xs text-red-500">{errors.name}</p>}
                    </div>

                    {/* Alamat Email */}
                    <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-semibold text-slate-700" htmlFor="email">
                            Alamat Email <span className="text-red-500">*</span>
                        </label>
                        <input
                            id="email"
                            type="email"
                            placeholder="nama@email.com"
                            value={data.email}
                            onChange={e => setData("email", e.target.value)}
                            className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
                            disabled={processing}
                        />
                        {errors.email && <p className="text-xs text-red-500">{errors.email}</p>}
                    </div>

                    {/* Kata Sandi */}
                    <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-semibold text-slate-700" htmlFor="password">
                            Kata Sandi <span className="text-red-500">*</span>
                        </label>
                        <input
                            id="password"
                            type="password"
                            placeholder="Minimal 8 karakter"
                            value={data.password}
                            onChange={e => setData("password", e.target.value)}
                            className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
                            disabled={processing}
                        />
                        {errors.password && <p className="text-xs text-red-500">{errors.password}</p>}
                    </div>

                    {/* Dropdown Perusahaan */}
                    <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-semibold text-slate-700" htmlFor="perusahaan_id">
                            Perusahaan <span className="text-red-500">*</span>
                        </label>
                        <select
                            id="perusahaan_id"
                            value={data.perusahaan_id}
                            onChange={e => setData("perusahaan_id", e.target.value)}
                            className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white disabled:bg-slate-50 disabled:text-slate-500"
                            disabled={processing || isManager}
                        >
                            <option value="">Pilih Perusahaan...</option>
                            {perusahaanList.map(p => (
                                <option key={p.id} value={p.id}>{p.nama}</option>
                            ))}
                        </select>
                        {errors.perusahaan_id && <p className="text-xs text-red-500">{errors.perusahaan_id}</p>}
                    </div>

                    {/* Dropdown Role */}
                    <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-semibold text-slate-700" htmlFor="role">
                            Peran (Role) <span className="text-red-500">*</span>
                        </label>
                        <select
                            id="role"
                            value={data.role}
                            onChange={e => setData("role", e.target.value)}
                            className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white disabled:bg-slate-50 disabled:text-slate-500"
                            disabled={processing || isManager}
                        >
                            <option value="">Pilih Peran...</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="manager">Manager</option>
                            <option value="user">User</option>
                        </select>
                        {errors.role && <p className="text-xs text-red-500">{errors.role}</p>}
                    </div>

                    {/* Status Keaktifan */}
                    <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-semibold text-slate-700" htmlFor="is_active">
                            Status Akun <span className="text-red-500">*</span>
                        </label>
                        <select
                            id="is_active"
                            value={data.is_active}
                            onChange={e => setData("is_active", parseInt(e.target.value))}
                            className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
                            disabled={processing}
                        >
                            <option value={1}>Aktif</option>
                            <option value={0}>Nonaktif</option>
                        </select>
                        {errors.is_active && <p className="text-xs text-red-500">{errors.is_active}</p>}
                    </div>

                    <div className="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <Link href="/users">
                            <Button type="button" variant="outline" disabled={processing}>
                                Batal
                            </Button>
                        </Link>
                        <Button type="submit" disabled={processing} className="min-w-[120px]">
                            {processing ? (
                                <>
                                    <Loader2 className="w-4 h-4 animate-spin mr-1.5" />
                                    Menyimpan
                                </>
                            ) : (
                                <>
                                    <Save className="w-4 h-4 mr-1.5" />
                                    Simpan
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
}

Create.layout = page => <AppLayout title="Tambah Pengguna Baru" children={page} />;
