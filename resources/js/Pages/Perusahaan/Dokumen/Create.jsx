import { useState, useEffect } from "react";
import { useForm, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import {
    ArrowLeft, Loader2, CloudUpload, FileText,
    HeartPulse, CheckCircle2, AlertCircle
} from "lucide-react";

export default function Create({ perusahaan }) {
    // State khusus untuk memantau Health Check Python Service
    const [health, setHealth] = useState({ status: "checking", version: "" });
    const { data, setData, post, processing, errors } = useForm({
        file: null,
        periode_type: 'annual', // default
        tahun: new Date().getFullYear().toString(),
        quarter: '',
        bulan: '',
        statement_types: ["neraca", "laba_rugi"] // at the start ini yang aktif
    });

    // Fungsi Asynchronous untuk menembak rute internal Laravel /python-health
    const runHealthCheck = async () => {
        setHealth({ status: "checking", version: "" });
        try {
            const response = await fetch("/python-health");
            const result = await response.json();

            if (response.ok && result.ok) {
                setHealth({ status: "healthy", version: result.version });
            } else {
                setHealth({ status: "unreachable", version: "" });
            }
        } catch (error) {
            setHealth({ status: "unreachable", version: "" });
        }
    };

    // (Auto-check) tiap di load
    useEffect(() => {
        runHealthCheck();
    }, []);

    function handleCheckboxChange(type) {
        if (data.statement_types.includes(type)) {
            setData("statement_types", data.statement_types.filter(t => t !== type));
        } else {
            setData("statement_types", [...data.statement_types, type]);
        }
    }

    function handleSubmit(e) {
        e.preventDefault();
        post(`/perusahaan/${perusahaan.id}/dokumen`);
    }

    function renderHealthBadge() {
        if (health.status === "checking") {
            return (
                <button
                    type="button"
                    disabled
                    className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-50 border border-slate-200 text-xs text-slate-500 font-medium animate-pulse"
                >
                    <Loader2 className="w-3 h-3 animate-spin text-slate-400" /> Mengecek Layanan AI...
                </button>
            );
        }

        if (health.status === "healthy") {
            return (
                <button
                    type="button"
                    onClick={runHealthCheck}
                    title="Klik untuk mengecek ulang"
                    className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-50 border border-green-200 text-xs text-green-700 font-medium hover:bg-green-100 transition-colors cursor-pointer"
                >
                    <span className="w-2 h-2 rounded-full bg-green-500"></span>
                    Layanan Aktif — v{health.version}
                </button>
            );
        }

        return (
            <button
                type="button"
                onClick={runHealthCheck}
                title="Klik untuk mencoba menghubungkan kembali"
                className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 border border-red-200 text-xs text-red-700 font-medium hover:bg-red-100 transition-colors cursor-pointer animate-bounce"
            >
                <AlertCircle className="w-3 h-3 text-red-500" />
                Layanan Terputus (Hubungkan Kembali)
            </button>
        );
    }

    return (
        <div className="max-w mx-auto space-y-4">
            <Link href={`/perusahaan/${perusahaan.id}/dokumen`} className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
                <ArrowLeft className="w-3.5 h-3.5" /> Kembali ke Berkas Dokumen
            </Link>

            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-6">

                {/* Bagian Header + Penempatan Health Badge */}
                <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-3 border-b border-slate-100 pb-4">
                    <div className="space-y-0.5">
                        <h2 className="text-lg font-bold text-slate-900">Unggah & Ekstraksi Dokumen</h2>
                        <p className="text-xs text-slate-500">Sistem akan menyimpan berkas PDF lalu mengekstrak tabel finansial via Python API otomatis.</p>
                    </div>
                    {/* Render badge status di pojok kanan atas form */}
                    <div className="flex-shrink-0">
                        {renderHealthBadge()}
                    </div>
                </div>

                {/* Notifikasi Warning Jika Server Python Mati */}
                {health.status === "unreachable" && (
                    <div className="p-3.5 rounded-lg bg-red-50 border border-red-100 text-xs text-red-800 flex items-start gap-2.5">
                        <AlertCircle className="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5" />
                        <div>
                            <span className="font-bold">Peringatan Sistem:</span> Server pemrosesan ekstraksi dokumen kecerdasan buatan (FastAPI Python) saat ini tidak dapat dijangkau. Harap nyalakan kembali service python Anda sebelum melakukan pengunggahan laporan keuangan untuk menghindari kegagalan sistem.
                        </div>
                    </div>
                )}

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
                                disabled={processing || health.status === "unreachable"}
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
                                        <label htmlFor="pdf-file" className={`text-sm font-semibold ${health.status === 'unreachable' ? 'text-slate-400 cursor-not-allowed' : 'text-blue-600 hover:underline cursor-pointer'}`}>
                                            Klik untuk memilih berkas
                                        </label>
                                        <p className="text-xs text-slate-400 mt-0.5">Pastikan file bertipe PDF jernih</p>
                                    </div>
                                </>
                            )}
                        </div>
                        {errors.file && <p className="text-xs text-red-500">{errors.file}</p>}
                    </div>

                    {/* Input Periode Tahun */}
                    {/* Input Periode Tahun */}
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
                                disabled={processing || health.status === "unreachable"}
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
                                disabled={processing || health.status === "unreachable"}
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

                    {/* Checkbox Jenis Statement */}
                    <div className="flex flex-col gap-2">
                        <label className="text-xs font-semibold text-slate-700">Komponen Tabel yang Ingin Diekstrak</label>
                        <div className="flex gap-4">
                            {["neraca", "laba_rugi", "arus_kas"].map((type) => (
                                <label key={type} className="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={data.statement_types.includes(type)}
                                        onChange={() => handleCheckboxChange(type)}
                                        className="rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-4 h-4"
                                        disabled={processing || health.status === "unreachable"}
                                    />
                                    <span className="capitalize">{type.replace('_', ' ')}</span>
                                </label>
                            ))}
                        </div>
                    </div>

                    {/* Tombol Submit */}
                    <div className="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <Link href={`/perusahaan/${perusahaan.id}/dokumen`}>
                            <Button type="button" variant="outline" disabled={processing}>Batal</Button>
                        </Link>
                        {/* Tombol disabilitas otomatis jika server terputus */}
                        <Button
                            type="submit"
                            disabled={!data.file || processing || health.status === "unreachable"}
                            className="min-w-[160px]"
                        >
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
