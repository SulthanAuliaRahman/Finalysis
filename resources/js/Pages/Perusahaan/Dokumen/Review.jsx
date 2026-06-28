import { useForm, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { ArrowLeft, Loader2, Scale, TrendingUp, Wallet, MapPin } from "lucide-react";

// Sub-Komponen Reusable untuk Input Field beserta data Found At
function InputFieldWithMeta({ label, section, fieldKey, value, onChange, meta, disabled }) {
    return (
        <div className="space-y-1 bg-white border border-slate-100 rounded-lg p-3 shadow-2xs">
            <label className="text-xs font-semibold text-slate-700 block">{label}</label>

            {/* Input Angka Finansial */}
            <input
                type="number"
                value={value}
                onChange={e => onChange(section, fieldKey, e.target.value)}
                className="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 font-mono bg-white text-slate-900"
                disabled={disabled}
            />

            {/* Panel Metadata Found At (Hanya tampil jika data meta ditemukan oleh Python) */}
            {meta ? (
                <div className="mt-1.5 pt-1.5 border-t border-dashed border-slate-100 flex flex-col gap-0.5 text-[10px] text-slate-500">
                    <div className="flex items-center gap-1 font-medium text-blue-600">
                        <MapPin className="w-2.5 h-2.5" /> Sumber PDF:
                    </div>
                    <div>• Halaman: <span className="font-bold text-slate-700">{meta.page}</span></div>
                    <div>• Label PDF: <span className="italic text-slate-700 font-medium">"{meta.label_in_pdf}"</span></div>
                    <div className="truncate">
                        • Baris Angka: <span className="font-mono bg-slate-100 px-1 rounded text-slate-700 font-medium">{meta.all_numbers_on_row?.join(" | ")}</span>
                    </div>
                </div>
            ) : (
                <div className="text-[10px] text-slate-400 italic mt-1">• Posisi teks tidak terdeteksi oleh AI</div>
            )}
        </div>
    );
}

export default function Review({ perusahaan, dokumen, extractedData, foundAt }) {
    const { data, setData, post, processing } = useForm({
        found_at: JSON.stringify(foundAt), // Dikirim balik utuh ke backend untuk parameter /chunk

        neraca: {
            total_equity: extractedData?.neraca?.total_equity ?? 0,
            total_liabilities: extractedData?.neraca?.total_liabilities ?? 0,
            current_liabilities: extractedData?.neraca?.current_liabilities ?? 0,
            total_assets: extractedData?.neraca?.total_assets ?? 0,
            current_assets: extractedData?.neraca?.current_assets ?? 0,
        },
        laba_rugi: {
            pendapatan: extractedData?.laba_rugi?.pendapatan ?? 0,
            laba_kotor: extractedData?.laba_rugi?.laba_kotor ?? 0,
            laba_bersih: extractedData?.laba_rugi?.laba_bersih ?? 0,
        },
        arus_kas: {
            kas_masuk: extractedData?.arus_kas?.kas_masuk ?? 0,
            kas_keluar: extractedData?.arus_kas?.kas_keluar ?? 0,
        }
    });

    const handleNestedChange = (section, key, value) => {
        setData(section, {
            ...data[section],
            [key]: value === "" ? 0 : parseFloat(value)
        });
    };

    function handleSubmit(e) {
        e.preventDefault();
        post(`/perusahaan/${perusahaan.id}/dokumen/${dokumen.id}/chunk`);
    }

    return (
        <div className="max-w-5xl mx-auto space-y-4">
            <Link href={`/perusahaan/${perusahaan.id}/dokumen`} className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
                <ArrowLeft className="w-3.5 h-3.5" /> Simpan Sementara & Kembali
            </Link>

            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-6">
                <div>
                    <h2 className="text-lg font-bold text-slate-900">Langkah 2: Tinjau & Verifikasi Struktur Ekstraksi</h2>
                    <p className="text-xs text-slate-500 mt-0.5">Berikut adalah hasil ekstraksi tabel terstruktur sesuai skema database Anda. Silakan verifikasi ketepatan nilai numerik berdasarkan letak koordinat dokumen yang dibaca AI.</p>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-5">

                        {/* 1. SEKSI TABEL NERACA */}
                        <div className="border border-slate-100 rounded-xl p-4 bg-slate-50/40 space-y-4">
                            <div className="flex items-center gap-1.5 border-b border-slate-200/60 pb-2 text-slate-800 font-bold text-sm">
                                <Scale className="w-4 h-4 text-blue-600" /> Tabel Neraca
                            </div>
                            <div className="space-y-3">
                                <InputFieldWithMeta
                                    label="Aset Lancar (Current Assets)" section="neraca" fieldKey="current_assets"
                                    value={data.neraca.current_assets} onChange={handleNestedChange}
                                    meta={foundAt?.current_assets} disabled={processing}
                                />
                                <InputFieldWithMeta
                                    label="Total Aset (Total Assets)" section="neraca" fieldKey="total_assets"
                                    value={data.neraca.total_assets} onChange={handleNestedChange}
                                    meta={foundAt?.total_assets} disabled={processing}
                                />
                                <InputFieldWithMeta
                                    label="Liabilitas Jangka Pendek (Current Liabilities)" section="neraca" fieldKey="current_liabilities"
                                    value={data.neraca.current_liabilities} onChange={handleNestedChange}
                                    meta={foundAt?.current_liabilities} disabled={processing}
                                />
                                <InputFieldWithMeta
                                    label="Total Liabilitas (Total Liabilities)" section="neraca" fieldKey="total_liabilities"
                                    value={data.neraca.total_liabilities} onChange={handleNestedChange}
                                    meta={foundAt?.total_liabilities} disabled={processing}
                                />
                                <InputFieldWithMeta
                                    label="Total Ekuitas (Total Equity)" section="neraca" fieldKey="total_equity"
                                    value={data.neraca.total_equity} onChange={handleNestedChange}
                                    meta={foundAt?.total_equity} disabled={processing}
                                />
                            </div>
                        </div>

                        {/* 2. SEKSI TABEL LABA RUGI */}
                        <div className="border border-slate-100 rounded-xl p-4 bg-slate-50/40 space-y-4">
                            <div className="flex items-center gap-1.5 border-b border-slate-200/60 pb-2 text-slate-800 font-bold text-sm">
                                <TrendingUp className="w-4 h-4 text-emerald-600" /> Tabel Laba Rugi
                            </div>
                            <div className="space-y-3">
                                <InputFieldWithMeta
                                    label="Pendapatan Usaha (Revenue)" section="laba_rugi" fieldKey="pendapatan"
                                    value={data.laba_rugi.pendapatan} onChange={handleNestedChange}
                                    meta={foundAt?.revenue} disabled={processing}
                                />
                                <InputFieldWithMeta
                                    label="Laba Kotor (Gross Profit)" section="laba_rugi" fieldKey="laba_kotor"
                                    value={data.laba_rugi.laba_kotor} onChange={handleNestedChange}
                                    meta={foundAt?.gross_profit} disabled={processing}
                                />
                                <InputFieldWithMeta
                                    label="Laba Bersih (Net Profit)" section="laba_rugi" fieldKey="laba_bersih"
                                    value={data.laba_rugi.laba_bersih} onChange={handleNestedChange}
                                    meta={foundAt?.net_profit} disabled={processing}
                                />
                            </div>
                        </div>

                        {/* 3. SEKSI TABEL ARUS KAS */}
                        <div className="border border-slate-100 rounded-xl p-4 bg-slate-50/40 space-y-4">
                            <div className="flex items-center gap-1.5 border-b border-slate-200/60 pb-2 text-slate-800 font-bold text-sm">
                                <Wallet className="w-4 h-4 text-indigo-600" /> Tabel Arus Kas
                            </div>
                            <div className="space-y-3">
                                <InputFieldWithMeta
                                    label="Kas Masuk (Cash Inflow)" section="arus_kas" fieldKey="kas_masuk"
                                    value={data.arus_kas.kas_masuk} onChange={handleNestedChange}
                                    meta={foundAt?.cfo} disabled={processing}
                                />
                                <InputFieldWithMeta
                                    label="Kas Keluar (Cash Outflow)" section="arus_kas" fieldKey="kas_keluar"
                                    value={data.arus_kas.kas_keluar} onChange={handleNestedChange}
                                    meta={foundAt?.cff} disabled={processing}
                                />
                            </div>
                        </div>

                    </div>

                    {/* Footer Kontrol Kirim Form */}
                    <div className="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <Link href={`/perusahaan/${perusahaan.id}/dokumen`}>
                            <Button type="button" variant="outline" disabled={processing}>Batal</Button>
                        </Link>
                        <Button type="submit" disabled={processing} className="min-w-[180px] bg-amber-600 text-white hover:bg-amber-700 shadow-xs">
                            {processing ? (
                                <><Loader2 className="w-4 h-4 animate-spin mr-1.5" /> Memotong Teks...</>
                            ) : (
                                "Proses Chunking"
                            )}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
}

Review.layout = page => <AppLayout title="Verifikasi & Validasi Hasil Ekstraksi" children={page} />;
