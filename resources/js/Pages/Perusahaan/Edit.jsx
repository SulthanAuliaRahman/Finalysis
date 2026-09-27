import { useForm, Link, usePage } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { ArrowLeft, Loader2, Save } from "lucide-react";

const FIELDS = [
    {
        key: "nama",
        label: "Nama Perusahaan",
        placeholder: "contoh: PT Maju Bersama Tbk",
        type: "input"
    },
    {
        key: "profile",
        label: "Profil & skala usaha",
        placeholder: "Contoh: tahun berdiri, lokasi, jumlah karyawan, dan skala usaha.",
        type: "textarea"
    },
    {
        key: "bidang_jasa",
        label: "Bidang dan jasa utama",
        placeholder: "Produk atau jasa utama yang ditawarkan.",
        type: "textarea"
    },
    {
        key: "model_pendapatan",
        label: "Model pendapatan & pola pembayaran",
        placeholder: "Sumber pendapatan, pelanggan utama, termin pembayaran, dan kebiasaan penagihan.",
        type: "textarea"
    }
];

export default function Edit({ perusahaan }) {
    const { data, setData, put, processing, errors } = useForm({
        nama: perusahaan.nama ?? "",
        profile: perusahaan.profile ?? "",
        bidang_jasa: perusahaan.bidang_jasa ?? "",
        model_pendapatan: perusahaan.model_pendapatan ?? ""
    });

    const { props } = usePage();
    const userRole = props.auth?.user?.role;

    const backUrl = userRole == "super_admin" ? "/perusahaan" : "/dashboard";

    function handleSubmit(e) {
        e.preventDefault();
        put(`/perusahaan/${perusahaan.id}`);
    }

    return (
        <div className="max-w-2xl mx-auto space-y-4">
            <Link href={backUrl} className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
                <ArrowLeft className="w-3.5 h-3.5" />
                {userRole === "super_admin" ? "Kembali ke Daftar Perusahaan" : "Kembali ke Dashboard"}
            </Link>

            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-6">
                <div>
                    <h2 className="text-lg font-bold text-slate-900">Perbarui Profil Perusahaan</h2>
                </div>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-4">
                        {FIELDS.map(({ key, label, placeholder, type }) => (
                            <div key={key} className="flex flex-col gap-1.5">
                                <label className="text-xs font-semibold text-slate-700" htmlFor={key}>
                                    {label} <span className="text-red-500">*</span>
                                </label>

                                {type === "textarea" ? (
                                    <textarea
                                        id={key}
                                        placeholder={placeholder}
                                        value={data[key]}
                                        onChange={e => setData(key, e.target.value)}
                                        rows={3}
                                        required
                                        className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                        disabled={processing}
                                    />
                                ) : (
                                    <input
                                        id={key}
                                        type="text"
                                        placeholder={placeholder}
                                        value={data[key]}
                                        onChange={e => setData(key, e.target.value)}
                                        required
                                        className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        disabled={processing}
                                    />
                                )}

                                {errors[key] && <p className="text-xs text-red-500">{errors[key]}</p>}
                            </div>
                        ))}
                    </div>

                    <div className="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <Link href={backUrl}>
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
