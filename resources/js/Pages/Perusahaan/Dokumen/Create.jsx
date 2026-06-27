import { useForm, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { ArrowLeft, Loader2, CloudUpload, FileText, CheckCircle2 } from "lucide-react";

export default function Create({ perusahaan }) {
    const { data, setData, post, processing, errors } = useForm({
        file: null,
        periode: new Date().getFullYear().toString(),
        statement_types: ["neraca", "laba_rugi"] // Default tercentang
    });

    function handleCheckboxChange(type) {
        if (data.statement_types.includes(type)) {
            setData("statement_types", data.statement_types.filter(t => t !== type));
        } else {
            setData("statement_types", [...data.statement_types, type]);
        }
    }

    function handleSubmit(e) {
        e.preventDefault();
        // Inertia otomatis mengirimkan data multipart/form-data jika mendeteksi objek File
        post(`/perusahaan/${perusahaan.id}/dokumen`);
    }

    return (
        <div className="max-w-2xl mx-auto space-y-4">
            <Link href={`/perusahaan/${perusahaan.id}/dokumen`} className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
                <ArrowLeft className="w-3.5 h-3.5" /> Kembali ke Berkas Dokumen
            </Link>

            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-6">
                <div>
                    <h2 className="text-lg font-bold text-slate-900">Unggah & Ekstraksi Dokumen</h2>
                    <p className="text-xs text-slate-500 mt-0.5">Sistem akan menyimpan berkas PDF lalu mengekstrak tabel finansial via Python API otomatis.</p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-5">
                    {/* Input Drag & Drop File */}
                    <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-semibold text-slate-700">Berkas Laporan Keuangan (PDF) <span className="text-red-500">*</span></label>
                        <div className={`border-2 border-dashed rounded-lg p-8 flex flex-col items-center justify-center gap-3 transition-colors ${data.file ? 'border-blue-500 bg-blue-50/20' : 'border-slate-200 bg-slate-50/50 hover:bg-slate-50'}`}>
                            <input
                                type="file"
                                accept="application/pdf"
                                className="hidden"
                                id="pdf-file"
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
                                    <label htmlFor="pdf-file" className="text-xs text-blue-600 hover:underline cursor-pointer">Ganti file</label>
                                </>
                            ) : (
                                <>
                                    <CloudUpload className="w-10 h-10 text-slate-400" />
                                    <div className="text-center">
                                        <label htmlFor="pdf-file" className="text-sm font-semibold text-blue-600 hover:underline cursor-pointer">Klik untuk memilih berkas</label>
                                        <p className="text-xs text-slate-400 mt-0.5">Pastikan file bertipe PDF jernih</p>
                                    </div>
                                </>
                            )}
                        </div>
                        {errors.file && <p className="text-xs text-red-500">{errors.file}</p>}
                    </div>

                    {/* Input Periode Tahun */}
                    <div className="flex flex-col gap-1.5">
                        <label className="text-xs font-semibold text-slate-700" htmlFor="periode">Tahun Laporan Keuangan <span className="text-red-500">*</span></label>
                        <input
                            id="periode"
                            type="text"
                            maxLength={4}
                            value={data.periode}
                            onChange={e => setData("periode", e.target.value)}
                            className="px-3 py-2 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 w-full max-w-[200px]"
                            disabled={processing}
                        />
                        {errors.periode && <p className="text-xs text-red-500">{errors.periode}</p>}
                    </div>

                    {/* Checkbox Jenis Statement */}
                    <div className="flex flex-col gap-2">
                        <label className="text-xs font-semibold text-slate-700">Komponen Tabel yang Ingin Diekstrak</label>
                        <div className="flex gap-4">
                            {["neraca", "laba_rugi", "cash_flow"].map((type) => (
                                <label key={type} className="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={data.statement_types.includes(type)}
                                        onChange={() => handleCheckboxChange(type)}
                                        className="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4"
                                        disabled={processing}
                                    />
                                    <span className="capitalize">{type.replace('_', ' ')}</span>
                                </label>
                            ))}
                        </div>
                    </div>

                    {/* TombolSubmit */}
                    <div className="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <Link href={`/perusahaan/${perusahaan.id}/dokumen`}>
                            <Button type="button" variant="outline" disabled={processing}>Batal</Button>
                        </Link>
                        <Button type="submit" disabled={!data.file || processing} className="min-w-[160px]">
                            {processing ? (
                                <><Loader2 className="w-4 h-4 animate-spin mr-1.5" /> Mengekstrak AI...</>
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

Create.layout = page => <AppLayout title="Proses Upload Laporan" children={page} />;
