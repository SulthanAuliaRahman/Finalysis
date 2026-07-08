// resources/js/Pages/Perusahaan/Dokumen/EditDokumen.jsx
import { useState } from "react";
import { useForm, Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { ArrowLeft, Loader2, Calendar, Lock } from "lucide-react";
import PdfViewer from "@/Components/Dokumen/PdfViewer";
import EditNeracaForm from "@/Components/Dokumen/EditNeracaForm";
import EditLabaRugiForm from "@/Components/Dokumen/EditLabaRugiForm";
import EditArusKasForm from "@/Components/Dokumen/EditArusKasForm";

export default function EditDokumen({ perusahaan, dokumen, extractedData }) {
    const { data, setData, put, processing } = useForm({
        neraca: {
            cash_equivalent:    extractedData?.neraca?.cash_equivalent    ?? 0,
            inventory:          extractedData?.neraca?.inventory          ?? 0,
            total_equity:       extractedData?.neraca?.total_equity       ?? 0,
            total_liabilities:  extractedData?.neraca?.total_liabilities  ?? 0,
            current_liabilities:extractedData?.neraca?.current_liabilities?? 0,
            total_assets:       extractedData?.neraca?.total_assets       ?? 0,
            current_assets:     extractedData?.neraca?.current_assets     ?? 0,
        },
        laba_rugi: {
            pendapatan:  extractedData?.laba_rugi?.pendapatan  ?? 0,
            laba_kotor:  extractedData?.laba_rugi?.laba_kotor  ?? 0,
            laba_bersih: extractedData?.laba_rugi?.laba_bersih ?? 0,
        },
        arus_kas: {
            cash_flow_from_operations: extractedData?.arus_kas?.cash_flow_from_operations ?? null,
            cash_flow_from_investing:  extractedData?.arus_kas?.cash_flow_from_investing  ?? null,
            cash_flow_from_financing:  extractedData?.arus_kas?.cash_flow_from_financing  ?? null,
            kas_masuk:  extractedData?.arus_kas?.kas_masuk  ?? 0,
            kas_keluar: extractedData?.arus_kas?.kas_keluar ?? 0,
        }
    });

    const handleDataChange = (section, key, value) => {
        setData(section, { ...data[section], [key]: value });
    };

    const handleCashFlowComponentChange = (key, value) => {
        const parsed = value === '' ? null : parseFloat(value);
        const updated = { ...data.arus_kas, [key]: parsed };

        const cfo = updated.cash_flow_from_operations ?? 0;
        const cfi = updated.cash_flow_from_investing  ?? 0;
        const cff = updated.cash_flow_from_financing  ?? 0;

        updated.kas_masuk  = Math.max(0, cfo) + Math.max(0, cfi) + Math.max(0, cff);
        updated.kas_keluar = Math.abs(Math.min(0, cfo)) + Math.abs(Math.min(0, cfi)) + Math.abs(Math.min(0, cff));

        setData('arus_kas', updated);
    };

    function handleSubmit(e) {
        e.preventDefault();
        put(`/perusahaan/${perusahaan.id}/dokumen/${dokumen.id}`);
    }

    const pdfUrl = `/perusahaan/${perusahaan.id}/dokumen/${dokumen.id}/view-pdf`;

    return (
        <div className="max-w mx-auto space-y-4">
            <Link
                href={`/perusahaan/${perusahaan.id}/dokumen`}
                className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors"
            >
                <ArrowLeft className="w-3.5 h-3.5" /> Kembali ke Daftar Dokumen
            </Link>

            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-6">
                <div className="flex items-start justify-between">
                    <div>
                        <h2 className="text-lg font-bold text-slate-900">Edit Data Dokumen</h2>
                        <p className="text-xs text-slate-500 mt-0.5">
                            Ubah nilai numerik laporan keuangan. Periode tidak dapat diubah.
                        </p>
                    </div>

                    {/* Periode — read-only informatif */}
                    <div className="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                        <Calendar className="w-3.5 h-3.5 text-slate-400" />
                        <span className="text-xs font-semibold text-slate-700">{dokumen.periode}</span>
                        <Lock className="w-3 h-3 text-slate-300" />
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
                    {/* Kolom Kiri: PDF Viewer */}
                    <div className="sticky top-6">
                        <PdfViewer fileUrl={pdfUrl} title={dokumen.nama_file} />
                    </div>

                    {/* Kolom Kanan: Form Edit */}
                    <div>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <EditNeracaForm
                                data={data}
                                onDataChange={handleDataChange}
                                disabled={processing}
                            />
                            <EditLabaRugiForm
                                data={data}
                                onDataChange={handleDataChange}
                                disabled={processing}
                            />
                            <EditArusKasForm
                                data={data}
                                onDataChange={handleDataChange}
                                onCashFlowComponentChange={handleCashFlowComponentChange}
                                disabled={processing}
                            />

                            <div className="flex justify-end gap-2 pt-4 border-t border-slate-100 sticky bottom-0 bg-white py-4 z-10">
                                <Link href={`/perusahaan/${perusahaan.id}/dokumen`}>
                                    <Button type="button" variant="outline" disabled={processing}>
                                        Batal
                                    </Button>
                                </Link>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="min-w-[160px] bg-blue-600 text-white hover:bg-blue-700 shadow-xs"
                                >
                                    {processing ? (
                                        <><Loader2 className="w-4 h-4 animate-spin mr-1.5" /> Menyimpan...</>
                                    ) : (
                                        "Simpan Perubahan"
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

EditDokumen.layout = page => <AppLayout title="Edit Data Dokumen" children={page} />;
