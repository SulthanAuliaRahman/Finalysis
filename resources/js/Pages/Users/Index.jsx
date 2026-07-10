import { useState, useEffect } from "react";
import { router, Link, usePage } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { Plus, Search, Edit2, Trash2, User, Building } from "lucide-react";
import { cn } from "@/lib/utils";

const ROLE_BADGES = {
    super_admin: "bg-red-50 text-red-700 border-red-200 hover:bg-red-50",
    manager: "bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-50",
    user: "bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-50",
};

const ROLE_LABELS = {
    super_admin: "Super Admin",
    manager: "Manager",
    user: "User",
};

export default function Index({ users, filters }) {
    const { auth } = usePage().props;
    const currentUser = auth?.user;

    const [search, setSearch] = useState(filters.search || "");
    const [role, setRole] = useState(filters.role || "");
    const [status, setStatus] = useState(filters.status || "");

    useEffect(() => {
        const timeout = setTimeout(() => {
            const params = { search, status };
            if (currentUser?.role === "super_admin") {
                params.role = role;
            }
            router.get("/users", params, {
                preserveState: true,
                replace: true,
            });
        }, 300);

        return () => clearTimeout(timeout);
    }, [search, role, status]);

    function handleDelete(user) {
        if (user.id === currentUser?.id) {
            alert("Anda tidak dapat menghapus akun Anda sendiri.");
            return;
        }

        if (confirm(`Apakah Anda yakin ingin menghapus user ${user.name}? Semua data yang berkaitan akan terhapus.`)) {
            router.delete(`/users/${user.id}`);
        }
    }

    return (
        <div className="space-y-6">
            {/* Header section */}
            <div className="flex flex-col sm:flex-row gap-4 sm:items-center justify-between">
                <div>
                    <h2 className="text-xl font-bold text-slate-900">Kelola Pengguna</h2>
                    <p className="text-xs text-slate-500 mt-0.5">
                        {currentUser?.role === "super_admin"
                            ? "Kelola semua akun pengguna, peran, dan perusahaan terdaftar."
                            : `Kelola akun pengguna (role User) pada perusahaan.`}
                    </p>
                </div>

                <Link href="/users/create">
                    <Button className="flex-shrink-0 shadow-xs gap-1.5">
                        <Plus className="w-4 h-4" />
                        Tambah User
                    </Button>
                </Link>
            </div>

            {/* Filter controls */}
            <div className="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center bg-white p-4 border border-slate-200 rounded-xl shadow-xs">
                <div className="relative flex-1 max-w-md">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input
                        type="text"
                        placeholder="Cari nama atau email..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white transition-colors"
                    />
                </div>

                <div className="flex flex-wrap gap-2 items-center">
                    {/* Role Filter (Hanya tampil untuk Super Admin) */}
                    {currentUser?.role === "super_admin" && (
                        <select
                            value={role}
                            onChange={(e) => setRole(e.target.value)}
                            className="text-sm border border-slate-200 rounded-md pl-3  py-2 bg-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="">Semua Peran</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="manager">Manager</option>
                            <option value="user">User</option>
                        </select>
                    )}

                    {/* Status Filter */}
                    <select
                        value={status}
                        onChange={(e) => setStatus(e.target.value)}
                        className="text-sm border border-slate-200 rounded-md pl-3 py-2 bg-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                    >
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            {/* Users table */}
            <div className="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama</th>
                                <th className="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                                <th className="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Peran</th>
                                <th className="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Perusahaan</th>
                                <th className="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                <th className="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 bg-white">
                            {users.data.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-6 py-10 text-center text-slate-400 text-sm">
                                        <User className="w-8 h-8 mx-auto text-slate-300 mb-2" />
                                        Tidak ada data pengguna yang ditemukan.
                                    </td>
                                </tr>
                            ) : (
                                users.data.map((user) => (
                                    <tr key={user.id} className="hover:bg-slate-50/50 transition-colors">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center gap-3">
                                                <div className="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-semibold text-slate-600 text-xs">
                                                    {user.name.slice(0, 2).toUpperCase()}
                                                </div>
                                                <div className="font-medium text-slate-900 text-sm">{user.name}</div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                            {user.email}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <Badge
                                                variant="outline"
                                                className={cn("text-[10px] font-semibold border", ROLE_BADGES[user.role] || ROLE_BADGES.user)}
                                            >
                                                {ROLE_LABELS[user.role] || user.role}
                                            </Badge>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center gap-1.5 text-sm text-slate-600">
                                                <Building className="w-3.5 h-3.5 text-slate-400" />
                                                <span>{user.perusahaan?.nama || "—"}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center gap-1.5">
                                                <span
                                                    className={cn(
                                                        "w-1.5 h-1.5 rounded-full",
                                                        user.is_active ? "bg-emerald-500" : "bg-slate-300"
                                                    )}
                                                />
                                                <span className="text-xs text-slate-600 font-medium">
                                                    {user.is_active ? "Aktif" : "Nonaktif"}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link href={`/users/${user.id}/edit`}>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="w-8 h-8 text-slate-500 hover:text-slate-900 hover:bg-slate-100"
                                                        title="Edit User"
                                                    >
                                                        <Edit2 className="w-3.5 h-3.5" />
                                                    </Button>
                                                </Link>

                                                {user.id !== currentUser?.id && (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="w-8 h-8 text-red-500 hover:text-red-700 hover:bg-red-50"
                                                        onClick={() => handleDelete(user)}
                                                        title="Hapus User"
                                                    >
                                                        <Trash2 className="w-3.5 h-3.5" />
                                                    </Button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination section */}
                {users.links && users.links.length > 3 && (
                    <div className="flex items-center justify-between border-t border-slate-100 px-6 py-4 bg-slate-50/50">
                        <div className="text-xs text-slate-500">
                            Menampilkan <span className="font-semibold text-slate-700">{users.data.length}</span> dari{" "}
                            <span className="font-semibold text-slate-700">{users.total}</span> data
                        </div>
                        <div className="flex justify-center gap-1">
                            {users.links.map((link, idx) => (
                                <Link
                                    key={idx}
                                    href={link.url || "#"}
                                    disabled={!link.url}
                                    className={cn(
                                        "px-2.5 py-1 text-xs rounded border transition-colors",
                                        link.active
                                            ? "bg-blue-600 border-blue-600 text-white font-medium"
                                            : "bg-white border-slate-200 text-slate-600 hover:bg-slate-50",
                                        !link.url && "opacity-50 cursor-not-allowed"
                                    )}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}

Index.layout = (page) => (
    <AppLayout title="Kelola Pengguna">
        {page}
    </AppLayout>
);