import { useForm, Link, usePage } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { ArrowLeft, Loader2, Save } from "lucide-react";

const SEKTORS = ["Manufaktur", "Jasa", "Perdagangan", "Lainnya"];

export default function Edit({ perusahaan }) {
    const { data, setData, put, processing, errors } = useForm({
        nama: perusahaan.nama ?? "",
        sektor: perusahaan.sektor ?? "",
        deskripsi: perusahaan.deskripsi ?? ""
    });

    const { props } = usePage();
    const userRole = props.auth?.user?.role;

    const backUrl =
        userRole === "super_admin"
            ? "/perusahaan"
            : "/dashboard";

    function handleSubmit(e) {
        e.preventDefault();
        put(`/perusahaan/${perusahaan.id}`);
    }

    return (
        
        <div className="max-w-2xl mx-auto space-y-4">
            <Link href={backUrl} className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
                <ArrowLeft className="w-3.5 h-3.5" />

            {userRole === "super_admin"? "Kembali ke Daftar Perusahaan": "Kembali ke Dashboard"}
            </Link>

            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-6">
                <div>
                    <h2 className="text-lg font-bold text-slate-900">Perbarui Profil Perusahaan</h2>
                    <p className="text-xs text-slate-500 mt-0.5">Ubah informasi metadata entitas untuk pencarian RAG yang akurat.</p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-semibold text-slate-700" htmlFor="nama">Nama Perusahaan <span className="text-red-500">*</span></label>
                        <input
                            id="nama"
                            type="text"
                            value={data.nama}
                            onChange={e => setData("nama", e.target.value)}
                            className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                            disabled={processing}
                        />
                        {errors.nama && <p className="text-xs text-red-500">{errors.nama}</p>}
                    </div>

                    <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-semibold text-slate-700" htmlFor="sektor">Sektor Industri <span className="text-red-500">*</span></label>
                        <select
                            id="sektor"
                            value={data.sektor}
                            onChange={e => setData("sektor", e.target.value)}
                            className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white"
                            disabled={processing}
                        >
                            {SEKTORS.map(s => <option key={s} value={s}>{s}</option>)}
                        </select>
                        {errors.sektor && <p className="text-xs text-red-500">{errors.sektor}</p>}
                    </div>

                    <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-semibold text-slate-700" htmlFor="deskripsi">Deskripsi Profil</label>
                        <textarea
                            id="deskripsi"
                            value={data.deskripsi}
                            onChange={e => setData("deskripsi", e.target.value)}
                            rows={4}
                            className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                            disabled={processing}
                        />
                        {errors.deskripsi && <p className="text-xs text-red-500">{errors.deskripsi}</p>}
                    </div>

                    <div className="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <Link href="/perusahaan">
                            <Button type="button" variant="outline" disabled={processing}>Batal</Button>
                        </Link>
                        <Button type="submit" disabled={processing} className="min-w-[120px]">
                            {processing ? (
                                <><Loader2 className="w-4 h-4 animate-spin mr-1.5" /> Memperbarui</>
                            ) : (
                                <><Save className="w-4 h-4 mr-1.5" /> Simpan Perubahan</>
                            )}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
}

Edit.layout = page => <AppLayout title="Edit Perusahaan" children={page} />;
