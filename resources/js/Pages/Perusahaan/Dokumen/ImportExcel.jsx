import { Head, Link, useForm } from "@inertiajs/react";
import { useState } from "react";

const RINGKASAN_LABELS = {
    total_asset_lancar: "Total Asset Lancar",
    total_asset_tetap: "Total Asset Tetap",
    total_liabilitas: "Total Liabilitas",
    total_ekuitas: "Total Ekuitas",
    total_pendapatan: "Total Pendapatan",
    total_beban: "Total Beban",
};

const DETAIL_LABELS = {
    "asset lancar": "Asset Lancar",
    "asset tetap": "Asset Tetap",
    liabilitas: "Liabilitas",
    ekuitas: "Ekuitas",
    pendapatan: "Pendapatan",
    beban: "Beban",
    tidak_dikenal: "Kelompok Akun Tidak Dikenal",
};

const DETAIL_ORDER = [
    "asset lancar",
    "asset tetap",
    "liabilitas",
    "ekuitas",
    "pendapatan",
    "beban",
    "tidak_dikenal",
];

function formatRupiah(value) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    }).format(value ?? 0);
}

function RingkasanCard({ label, value }) {
    const negatif = value < 0;

    return (
        <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div className="text-sm text-gray-500">{label}</div>
            <div
                className={`mt-1 text-lg font-semibold ${
                    negatif ? "text-red-600" : "text-gray-900"
                }`}
            >
                {formatRupiah(value)}
            </div>
        </div>
    );
}

function DetailTable({ label, akunList }) {
    if (!akunList || akunList.length === 0) {
        return null;
    }

    return (
        <div className="space-y-2">
            <h4 className="text-sm font-semibold text-gray-900">{label}</h4>

            <div className="overflow-x-auto rounded-md border border-gray-200 bg-white">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-3 py-2 text-left text-gray-600 font-medium">Nama Akun</th>
                            <th className="px-3 py-2 text-right text-gray-600 font-medium">Nilai</th>
                        </tr>
                    </thead>

                    <tbody>
                        {akunList.map((akun, index) => (
                            <tr
                                key={`${akun.nama_akun}-${index}`}
                                className="border-t border-gray-200"
                            >
                                <td className="px-3 py-2 text-gray-800">{akun.nama_akun}</td>
                                <td
                                    className={`px-3 py-2 text-right ${
                                        akun.nilai < 0 ? "text-red-600" : "text-gray-800"
                                    }`}
                                >
                                    {formatRupiah(akun.nilai)}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default function ImportExcel({ hasilImport }) {
    const { data, setData, post, processing, errors } = useForm({
        file: null,
    });

    const [fileName, setFileName] = useState("");

    const submit = (e) => {
        e.preventDefault();

        // Route disesuaikan karena parameter perusahaan sudah dihapus
        post(route("dokumen.import-excel.store"), {
            forceFormData: true,
        });
    };

    return (
        <div className="min-h-screen bg-gray-50/50 text-gray-900">
            <Head title="Import Excel" />

            <div className="max-w-4xl mx-auto py-8 px-4 space-y-6">
                {/* Form Upload */}
                <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm space-y-6">
                    <h2 className="text-lg font-semibold">
                        Import Laporan Keuangan (Excel)
                    </h2>

                    {/* Download Template */}
                    <div>
                        <span className="block text-sm font-medium text-gray-700 mb-2">Template Excel</span>
                        <div>
                            <button
                                type="button"
                                disabled
                                className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md opacity-50 cursor-not-allowed"
                            >
                                Download Template (Coming Soon)
                            </button>
                        </div>
                    </div>

                    {/* Upload */}
                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <span className="block text-sm font-medium text-gray-700 mb-2">File Excel</span>
                            <input
                                type="file"
                                accept=".xlsx,.xls"
                                onChange={(e) => {
                                    const file = e.target.files[0];
                                    setData("file", file);
                                    setFileName(file?.name ?? "");
                                }}
                                className="block w-full text-sm text-gray-900 border border-gray-300 rounded-md cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                            />

                            {errors.file && (
                                <div className="mt-1 text-sm text-red-600">
                                    {errors.file}
                                </div>
                            )}
                        </div>

                        {fileName && (
                            <div className="text-sm text-gray-500 bg-gray-50 p-3 rounded-md border border-gray-100">
                                File dipilih: <strong className="text-gray-900">{fileName}</strong>
                            </div>
                        )}

                        <div className="flex gap-3 pt-2">
                            <button
                                type="submit"
                                disabled={processing || !data.file}
                                className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                {processing ? 'Memproses...' : 'Import Excel'}
                            </button>

                            <Link
                                href="/" // Silakan ubah rute ini sesuai kebutuhan aplikasi Anda
                                className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                            >
                                Kembali
                            </Link>
                        </div>
                    </form>
                </div>

                {/* Hasil preview - belum disimpan ke DB */}
                {hasilImport && (
                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm space-y-6">
                        <div>
                            <h2 className="text-lg font-semibold">
                                Preview Hasil Import
                                {hasilImport.perusahaan && ` — ${hasilImport.perusahaan}`}
                                {hasilImport.tahun && ` (${hasilImport.tahun})`}
                            </h2>

                            <div className="mt-1 text-sm text-gray-500">
                                Data di bawah ini hasil parsing dari file Excel dan belum disimpan ke database.
                            </div>
                        </div>

                        {/* Ringkasan */}
                        <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                            {Object.entries(RINGKASAN_LABELS).map(([key, label]) => (
                                <RingkasanCard
                                    key={key}
                                    label={label}
                                    value={hasilImport.ringkasan[key]}
                                />
                            ))}
                        </div>

                        {/* Detail per kelompok */}
                        <div className="space-y-6 mt-6">
                            {DETAIL_ORDER.map((key) => (
                                <DetailTable
                                    key={key}
                                    label={DETAIL_LABELS[key]}
                                    akunList={hasilImport.detail[key]}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
