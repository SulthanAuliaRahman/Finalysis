import { useForm, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { ArrowLeft, Loader2 } from "lucide-react";
import PdfViewer from "@/Components/Dokumen/PdfViewer";
import ExtractionForm from "@/Components/Dokumen/ExtractionForm";

export default function Review({ perusahaan, dokumen, extractedData, foundAt }) {
    // Menyimpan found_at sebagai Object, bukan JSON string agar mudah dimanipulasi
    const { data, setData, post, processing } = useForm({
        found_at: foundAt || {},

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

    // Handler untuk mengubah nilai angka finansial
    const handleDataChange = (section, key, value) => {
        setData(section, {
            ...data[section],
            [key]: value === "" ? 0 : parseFloat(value)
        });
    };

    // Handler untuk mengubah nilai metadata (Halaman & Label)
    const handleMetaChange = (metaKey, keyToUpdate, value) => {
        setData('found_at', {
            ...data.found_at,
            [metaKey]: {
                ...(data.found_at[metaKey] || {}),
                [keyToUpdate]: value
            }
        });
    };

    function handleSubmit(e) {
        e.preventDefault();
        // Backend sudah menangani JSON decode/array, jadi post object found_at langsung aman
        post(`/perusahaan/${perusahaan.id}/dokumen/${dokumen.id}/chunk`);
    }

    // URL PDF (Sesuaikan dengan endpoint controller backend Anda yang melakukan serve/stream PDF)
    const pdfUrl = `/perusahaan/${perusahaan.id}/dokumen/${dokumen.id}/view-pdf`;

    return (
        <div className="max-w mx-auto space-y-4">
            <Link href={`/perusahaan/${perusahaan.id}/dokumen`} className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
                <ArrowLeft className="w-3.5 h-3.5" /> Simpan Sementara & Kembali
            </Link>

            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-6">
                <div>
                    <h2 className="text-lg font-bold text-slate-900">Langkah 2: Tinjau & Verifikasi Struktur Ekstraksi</h2>
                    <p className="text-xs text-slate-500 mt-0.5">
                        Verifikasi ketepatan nilai numerik di sebelah kanan berdasarkan letak koordinat pada dokumen PDF di sebelah kiri. Kamu juga dapat menyesuaikan metadata halaman dan label.
                    </p>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    {/* Kolom Kiri: PDF Viewer */}
                    <div className="sticky top-6">
                        <PdfViewer fileUrl={pdfUrl} title={dokumen.nama_file} />
                    </div>

                    {/* Kolom Kanan: Form Ekstraksi & Metadata */}
                    <div>
                        <form onSubmit={handleSubmit} className="space-y-6">

                            <ExtractionForm
                                data={data}
                                foundAt={data.found_at}
                                onDataChange={handleDataChange}
                                onMetaChange={handleMetaChange}
                                disabled={processing}
                            />

                            {/* Footer Kontrol Kirim Form */}
                            <div className="flex justify-end gap-2 pt-4 border-t border-slate-100 sticky bottom-0 bg-white py-4 z-10">
                                <Link href={`/perusahaan/${perusahaan.id}/dokumen`}>
                                    <Button type="button" variant="outline" disabled={processing}>Batal</Button>
                                </Link>
                                <Button type="submit" disabled={processing} className="min-w-[180px] bg-amber-600 text-white hover:bg-amber-700 shadow-xs">
                                    {processing ? (
                                        <><Loader2 className="w-4 h-4 animate-spin mr-1.5" /> Memotong Teks...</>
                                    ) : (
                                        "Simpan & Proses Chunking"
                                    )}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    );
}

Review.layout = page => <AppLayout title="Verifikasi & Validasi Hasil Ekstraksi" children={page} />;
