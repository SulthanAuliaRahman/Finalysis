import { useForm, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import {
    ArrowLeft, Loader2, CloudUpload, FileText, AlertCircle, Download, Info
} from "lucide-react";

export default function Create({ perusahaan }) {
    const { data, setData, post, processing, errors } = useForm({
        file: null,
        periode_type: "annual", // default
        tahun: new Date().getFullYear().toString(),
        quarter: "",
        bulan: "",
    });

    function handleSubmit(e) {
        e.preventDefault();
        post(`/perusahaan/${perusahaan.id}/dokumen/create`, {
            forceFormData: true,
            onError: (errs) => {
                // Prioritaskan pesan error dari file (exception DokumenService),
                // baru fallback ke error validasi lain
                const pesan = errs.file || Object.values(errs)[0];
                if (pesan) {
                    alert(pesan);
                } else {
                    alert("Terdapat isian yang belum sesuai. Silakan periksa kembali form Anda.");
                }
            },
        });
    }

    return (
        <div className="max-w mx-auto space-y-4">
            <Link href={`/perusahaan/${perusahaan.id}/dokumen`} className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
                <ArrowLeft className="w-3.5 h-3.5" /> Kembali ke Berkas Dokumen
            </Link>

            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-6">

                {/* Header + Tombol Unduh Format */}
                <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-3 border-b border-slate-100 pb-4">
                    <div className="space-y-0.5">
                        <h2 className="text-lg font-bold text-slate-900">Unggah Laporan Keuangan</h2>
                        <p className="text-xs text-slate-500">Unggah berkas Excel laporan keuangan sesuai format yang telah ditentukan.</p>
                    </div>
                    <a
                        href="/templates/format-laporan-keuangan.xlsx"
                        download
                        className="inline-flex flex-shrink-0 items-center gap-1.5 px-3 py-1.5 rounded-md border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors"
                    >
                        <Download className="w-3.5 h-3.5" /> Unduh Format Laporan Keuangan
                    </a>
                </div>

                {/* Info: sheet wajib */}
                <div className="p-3.5 rounded-lg bg-blue-50 border border-blue-100 text-xs text-blue-800 flex items-start gap-2.5">
                    <Info className="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" />
                    <div>
                        Berkas Excel wajib memiliki sheet <strong>&quot;Laporan Posisi Keuangan&quot;</strong> dan{" "}
                        <strong>&quot;Laporan Laba Rugi&quot;</strong>. Gunakan template di atas agar proses ekstraksi berjalan sempurna.
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-5">
                    {/* Input Drag & Drop File Excel */}
                    <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-semibold text-slate-700">Berkas Laporan Keuangan (Excel) <span className="text-red-500">*</span></label>
                        <div className={`border-2 border-dashed rounded-lg p-8 flex flex-col items-center justify-center gap-3 transition-colors ${data.file ? 'border-blue-500 bg-blue-50/20' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50'}`}>
                            <input
                                type="file"
                                accept=".xlsx,.xls"
                                className="hidden"
                                id="excel-file"
                                onChange={e => setData("file", e.target.files[0])}
                                disabled={processing}
                            />
                            {data.file ? (
                                <>
                                    <div className="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                                        <FileText className="w-6 h-6 text-blue-600" />
                                    </div>
                                    <div className="text-center">
                                        <p className="text-sm font-semibold text-slate-800 font-mono">{data.file.name}</p>
                                        <p className="text-xs text-slate-400">{(data.file.size / 1024 / 1024).toFixed(2)} MB</p>
                                    </div>
                                    <label htmlFor="excel-file" className="text-xs text-blue-600 hover:underline cursor-pointer">Ganti file</label>
                                </>
                            ) : (
                                <>
                                    <CloudUpload className="w-10 h-10 text-slate-400" />
                                    <div className="text-center">
                                        <label htmlFor="excel-file" className="text-sm font-semibold text-blue-600 hover:underline cursor-pointer">
                                            Klik untuk memilih berkas
                                        </label>
                                        <p className="text-xs text-slate-400 mt-0.5">Format .xlsx atau .xls sesuai template</p>
                                    </div>
                                </>
                            )}
                        </div>
                        {errors.file && (
                            <p className="text-xs text-red-500 flex items-center gap-1">
                                <AlertCircle className="w-3 h-3" /> {errors.file}
                            </p>
                        )}
                    </div>

                    {/* Input Periode */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {/* Tipe Periode */}
                        <div className="flex flex-col gap-1.5">
                            <label className="text-xs font-semibold text-slate-700">Tipe Laporan <span className="text-red-500">*</span></label>
                            <select
                                value={data.periode_type}
                                onChange={event => {
                                    setData(prevData => ({
                                        ...prevData,
                                        periode_type: event.target.value,
                                        quarter: '', // reset field lain saat ganti tipe
                                        bulan: ''
                                    }));
                                }}
                                className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 w-full"
                                disabled={processing}
                            >
                                <option value="annual">Tahunan (Annual)</option>
                                <option value="quarterly">Kuartal (Quarterly)</option>
                                <option value="monthly">Bulanan (Monthly)</option>
                            </select>
                            {errors.periode_type && <p className="text-xs text-red-500">{errors.periode_type}</p>}
                        </div>

                        {/* Input Tahun */}
                        <div className="flex flex-col gap-1.5">
                            <label className="text-xs font-semibold text-slate-700">Tahun <span className="text-red-500">*</span></label>
                            <input
                                type="number"
                                min="1900"
                                max="2100"
                                value={data.tahun}
                                onChange={event => setData("tahun", event.target.value)}
                                className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 w-full"
                                disabled={processing}
                            />
                            {errors.tahun && <p className="text-xs text-red-500">{errors.tahun}</p>}
                        </div>

                        {/* Input Dinamis Quarter / Bulan */}
                        {data.periode_type === 'quarterly' && (
                            <div className="flex flex-col gap-1.5">
                                <label className="text-xs font-semibold text-slate-700">Kuartal <span className="text-red-500">*</span></label>
                                <select
                                    value={data.quarter}
                                    onChange={event => setData("quarter", event.target.value)}
                                    className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 w-full"
                                    disabled={processing}
                                >
                                    <option value="" disabled>Pilih Kuartal</option>
                                    <option value="1">Q1</option>
                                    <option value="2">Q2</option>
                                    <option value="3">Q3</option>
                                    <option value="4">Q4</option>
                                </select>
                                {errors.quarter && <p className="text-xs text-red-500">{errors.quarter}</p>}
                            </div>
                        )}

                        {data.periode_type === 'monthly' && (
                            <div className="flex flex-col gap-1.5">
                                <label className="text-xs font-semibold text-slate-700">Bulan <span className="text-red-500">*</span></label>
                                <select
                                    value={data.bulan}
                                    onChange={event => setData("bulan", event.target.value)}
                                    className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 w-full"
                                    disabled={processing}
                                >
                                    <option value="" disabled>Pilih Bulan</option>
                                    {[
                                        { value: 1, label: "Januari" },
                                        { value: 2, label: "Februari" },
                                        { value: 3, label: "Maret" },
                                        { value: 4, label: "April" },
                                        { value: 5, label: "Mei" },
                                        { value: 6, label: "Juni" },
                                        { value: 7, label: "Juli" },
                                        { value: 8, label: "Agustus" },
                                        { value: 9, label: "September" },
                                        { value: 10, label: "Oktober" },
                                        { value: 11, label: "November" },
                                        { value: 12, label: "Desember" }
                                    ].map(bulanOption => (
                                        <option key={bulanOption.value} value={bulanOption.value}>{bulanOption.label}</option>
                                    ))}
                                </select>
                                {errors.bulan && <p className="text-xs text-red-500">{errors.bulan}</p>}
                            </div>
                        )}
                    </div>

                    {/* Tombol Submit */}
                    <div className="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <Link href={`/perusahaan/${perusahaan.id}/dokumen`}>
                            <Button type="button" variant="outline" disabled={processing}>Batal</Button>
                        </Link>
                        <Button
                            type="submit"
                            disabled={!data.file || processing}
                            className="min-w-[160px]"
                        >
                            {processing ? (
                                <><Loader2 className="w-4 h-4 animate-spin mr-1.5" /> Mengunggah...</>
                            ) : (
                                "Unggah & Ekstrak"
                            )}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
}

Create.layout = page => <AppLayout title="Unggah Laporan Keuangan" children={page} />;
