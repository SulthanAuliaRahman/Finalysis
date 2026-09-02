import { Head, Link, useForm } from "@inertiajs/react";
import { useState } from "react";

// 1. SESUAIKAN KEY DENGAN ENUM sub_kelompok_akun DI DATABASE
const KELOMPOK_NERACA_LABELS = {
    aset_lancar: "Aset Lancar",
    aset_tetap: "Aset Tetap",
    liabilitas_jangka_pendek: "Liabilitas Jangka Pendek",
    liabilitas_jangka_panjang: "Liabilitas Jangka Panjang",
    ekuitas: "Ekuitas",
    lainnya: "Lainnya",
};

const KELOMPOK_LABA_RUGI_LABELS = {
    pendapatan: "Pendapatan",
    beban: "Beban",
    beban_pajak: "Beban Pajak",
    lainnya: "Lainnya",
};

function formatRupiah(value) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    }).format(value ?? 0);
}

function groupByKelompok(akunList, labels) {
    const grouped = {};
    for (const akun of akunList ?? []) {
        // 2. KELOMPOKKAN BERDASARKAN sub_kelompok_akun
        const key = akun.sub_kelompok_akun || "lainnya";
        if (!grouped[key]) grouped[key] = [];
        grouped[key].push(akun);
    }
    return Object.keys(labels)
        .filter((key) => grouped[key]?.length)
        .map((key) => ({ label: labels[key], akunList: grouped[key] }));
}

function RingkasanCard({ label, value }) {
    const negatif = value < 0;
    return (
        <div className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div className="text-sm text-gray-500">{label}</div>
            <div className={`mt-1 text-lg font-semibold ${negatif ? "text-red-600" : "text-gray-900"}`}>
                {formatRupiah(value)}
            </div>
        </div>
    );
}

function DetailTable({ label, akunList }) {
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
                            <tr key={`${akun.nama_akun}-${index}`} className="border-t border-gray-200">
                                <td className="px-3 py-2 text-gray-800">{akun.nama_akun}</td>
                                <td className={`px-3 py-2 text-right ${akun.nilai < 0 ? "text-red-600" : "text-gray-800"}`}>
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
    const { data, setData, post, processing, errors } = useForm({ file: null });
    const [fileName, setFileName] = useState("");

    const submit = (e) => {
        e.preventDefault();
        post(route("dokumen.import-excel.store"), { forceFormData: true });
    };

    const neracaDetail = groupByKelompok(hasilImport?.neraca?.akun, KELOMPOK_NERACA_LABELS);
    const labaRugiDetail = groupByKelompok(hasilImport?.labaRugi?.akun, KELOMPOK_LABA_RUGI_LABELS);

    return (
        <div className="min-h-screen bg-gray-50/50 text-gray-900">
            <Head title="Import Excel" />
            <div className="max-w-4xl mx-auto py-8 px-4 space-y-6">
                <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm space-y-6">
                    <h2 className="text-lg font-semibold">Import Laporan Keuangan (Excel)</h2>

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
                                className="block w-full text-sm text-gray-900 border border-gray-300 rounded-md cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                            />
                            {errors.file && <div className="mt-1 text-sm text-red-600">{errors.file}</div>}
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
                                className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                {processing ? "Memproses..." : "Import Excel"}
                            </button>
                            <Link
                                href="/"
                                className="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                            >
                                Kembali
                            </Link>
                        </div>
                    </form>
                </div>

                {hasilImport && (
                    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm space-y-8">
                        <div>
                            <h2 className="text-lg font-semibold">Preview Hasil Import ({hasilImport.tahun})</h2>
                            <div className="mt-1 text-sm text-gray-500">
                                Data di bawah ini hasil parsing dan belum disimpan permanen ke database.
                            </div>
                        </div>

                        <div className="space-y-3">
                            <h3 className="text-base font-semibold">Laporan Posisi Keuangan</h3>
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                {/* 3. SESUAIKAN VARIABEL KEY DARI KEMBALIAN CONTROLLER */}
                                <RingkasanCard label="Total Asset Lancar" value={hasilImport.neraca.total_asset_lancar} />
                                <RingkasanCard label="Total Asset Tetap" value={hasilImport.neraca.total_asset_tetap} />
                                <RingkasanCard label="Total Asset" value={hasilImport.neraca.total_asset} />
                                <RingkasanCard label="Total Liab. Pendek" value={hasilImport.neraca.total_liabilities_pendek} />
                                <RingkasanCard label="Total Liab. Panjang" value={hasilImport.neraca.total_liabilities_panjang} />
                                <RingkasanCard label="Total Liabilitas" value={hasilImport.neraca.total_liabilities} />
                                <RingkasanCard label="Total Ekuitas" value={hasilImport.neraca.total_equitas} />
                                <RingkasanCard label="Total Ekuitas dan Liabilitas" value={hasilImport.neraca.total_equitas + hasilImport.neraca.total_liabilities} />
                            </div>
                            <div className="space-y-6 mt-4">
                                {neracaDetail.map((group) => (
                                    <DetailTable key={group.label} {...group} />
                                ))}
                            </div>
                        </div>

                        <div className="space-y-3">
                            <h3 className="text-base font-semibold">Laporan Laba Rugi</h3>
                            <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                                {/* SESUAIKAN VARIABEL KEY DARI KEMBALIAN CONTROLLER LABA RUGI */}
                                <RingkasanCard label="Total Pendapatan" value={hasilImport.labaRugi.total_pendapatan} />
                                <RingkasanCard label="Total Beban" value={hasilImport.labaRugi.total_beban} />
                                <RingkasanCard label="Laba Sebelum bersih Pajak" value={hasilImport.labaRugi.laba_bersih_sebelum_pajak} />
                                <RingkasanCard label="Total Biaya Pajak" value={hasilImport.labaRugi.total_biaya_pajak} />
                                <RingkasanCard label="Laba Sesudah bersih Pajak" value={hasilImport.labaRugi.laba_bersih_sesudah_pajak} />
                            </div>
                            <div className="space-y-6 mt-4">
                                {labaRugiDetail.map((group) => (
                                    <DetailTable key={group.label} {...group} />
                                ))}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
