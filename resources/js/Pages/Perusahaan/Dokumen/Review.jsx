import { useForm, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { ArrowLeft, Loader2 } from "lucide-react";
import PdfViewer from "@/Components/Dokumen/PdfViewer";
import ExtractionForm from "@/Components/Dokumen/ExtractionForm";

export default function Review({ perusahaan, dokumen, extractedData, foundAt }) {
    const { data, setData, post, processing } = useForm({
        found_at: foundAt || {},

        neraca: {
            cash_equivalent: extractedData?.neraca?.cash_equivalent ?? 0,
            inventory: extractedData?.neraca?.inventory ?? 0,
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
            // Komponen detail (nullable — hanya diisi kalau user aktifkan toggle)
            cash_flow_from_operations: extractedData?.arus_kas?.cash_flow_from_operations ?? null,
            cash_flow_from_investing:  extractedData?.arus_kas?.cash_flow_from_investing  ?? null,
            cash_flow_from_financing:  extractedData?.arus_kas?.cash_flow_from_financing  ?? null,
            // Hasil akhir
            kas_masuk:  extractedData?.arus_kas?.kas_masuk  ?? 0,
            kas_keluar: extractedData?.arus_kas?.kas_keluar ?? 0,
        }
    });

    const handleDataChange = (section, key, value) => {
        setData(section, {
            ...data[section],
            [key]: value === "" ? 0 : parseFloat(value)
        });
    };

    // Handler khusus arus kas: kalau CFO/CFI/CFF berubah, auto-kalkulasi kas masuk/keluar
    // tapi tetap bisa di-override manual lewat handleDataChange biasa
    const handleCashFlowComponentChange = (key, value) => {
        const parsed = value === "" ? null : parseFloat(value);

        const updated = {
            ...data.arus_kas,
            [key]: parsed,
        };

        // Kalkulasi realtime kas masuk & keluar dari komponen
        const cfo = updated.cash_flow_from_operations ?? 0;
        const cfi = updated.cash_flow_from_investing  ?? 0;
        const cff = updated.cash_flow_from_financing  ?? 0;

        updated.kas_masuk  = Math.max(0, cfo) + Math.max(0, cfi) + Math.max(0, cff);
        updated.kas_keluar = Math.abs(Math.min(0, cfo)) + Math.abs(Math.min(0, cfi)) + Math.abs(Math.min(0, cff));

        setData('arus_kas', updated);
    };

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
        post(`/perusahaan/${perusahaan.id}/dokumen/${dokumen.id}/chunk`);
    }

    const pdfUrl = `/perusahaan/${perusahaan.id}/dokumen/${dokumen.id}/view-pdf`;

    return (
        <div className="max-w mx-auto space-y-4">
            <Link href={`/perusahaan/${perusahaan.id}/dokumen`} className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
                <ArrowLeft className="w-3.5 h-3.5" /> Simpan Sementara & Kembali
            </Link>

            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-6">
                <div>
                    <h2 className="text-lg font-bold text-slate-900">Tinjau & Verifikasi</h2>
                    <p className="text-xs text-slate-500 mt-0.5">
                        Verifikasi ketepatan nilai numerik yang di extract secara otomatis.
                    </p>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    <div className="sticky top-6">
                        <PdfViewer fileUrl={pdfUrl} title={dokumen.nama_file} />
                    </div>

                    <div>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <ExtractionForm
                                data={data}
                                foundAt={data.found_at}
                                onDataChange={handleDataChange}
                                onMetadataChange={handleMetaChange}
                                onCashFlowComponentChange={handleCashFlowComponentChange}
                                disabled={processing}
                                statementTypes={dokumen.statement_types || []}
                            />

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
