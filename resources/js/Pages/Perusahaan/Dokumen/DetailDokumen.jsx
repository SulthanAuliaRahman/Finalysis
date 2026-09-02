import { Link } from "@inertiajs/react";
import AppLayout from "@/Layouts/AppLayout";
import { Badge } from "@/Components/ui/badge";
import { ArrowLeft, Download, FileText, Building2 } from "lucide-react";

// Sesuai enum sub_kelompok_akun di tabel chart_of_accounts
const NERACA_LABELS = {
    kas_setara_kas: "Kas dan Setara Kas",
    aset_lancar_selain_kas: "Aset Lancar Lainnya",
    aset_tetap: "Aset Tetap",
    liabilitas_jangka_pendek: "Liabilitas Jangka Pendek",
    liabilitas_jangka_panjang: "Liabilitas Jangka Panjang",
    ekuitas: "Ekuitas",
    lainnya: "Lainnya",
};

const LABA_RUGI_LABELS = {
    pendapatan: "Pendapatan",
    beban: "Beban",
    beban_pajak: "Beban Pajak",
    lainnya: "Lainnya",
};

const BULAN_LABELS = {
    1: "Januari", 2: "Februari", 3: "Maret", 4: "April", 5: "Mei", 6: "Juni",
    7: "Juli", 8: "Agustus", 9: "September", 10: "Oktober", 11: "November", 12: "Desember",
};

function formatRupiah(value) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    }).format(value ?? 0);
}

function formatPeriode(dokumen) {
    if (dokumen.periode_type === "quarterly") return `Kuartal ${dokumen.quarter} ${dokumen.tahun}`;
    if (dokumen.periode_type === "monthly") return `${BULAN_LABELS[dokumen.bulan] ?? dokumen.bulan} ${dokumen.tahun}`;
    return `Tahunan ${dokumen.tahun}`;
}

// Kelompokkan daftar akun (chart_of_accounts) berdasarkan sub_kelompok_akun
function groupByKelompok(akunList, labels) {
    const grouped = {};
    for (const akun of akunList ?? []) {
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
        <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-xs">
            <div className="text-xs text-slate-500">{label}</div>
            <div className={`mt-1 text-base font-semibold ${negatif ? "text-red-600" : "text-slate-900"}`}>
                {formatRupiah(value)}
            </div>
        </div>
    );
}

function DetailTable({ label, akunList }) {
    return (
        <div className="space-y-2">
            <h4 className="text-sm font-semibold text-slate-900">{label}</h4>
            <div className="overflow-x-auto rounded-md border border-slate-200 bg-white">
                <table className="w-full text-sm">
                    <thead className="bg-slate-50">
                        <tr>
                            <th className="px-3 py-2 text-left text-slate-500 font-medium">Nama Akun</th>
                            <th className="px-3 py-2 text-right text-slate-500 font-medium">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        {akunList.map((akun, index) => (
                            <tr key={`${akun.nama_akun}-${index}`} className="border-t border-slate-100">
                                <td className="px-3 py-2 text-slate-800">{akun.nama_akun}</td>
                                <td className={`px-3 py-2 text-right ${akun.nilai_akun < 0 ? "text-red-600" : "text-slate-800"}`}>
                                    {formatRupiah(akun.nilai_akun)}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

// Props yang diharapkan (lihat catatan controller di akhir jawaban):
// perusahaan: { id, nama, ... }
// dokumen: { id, nama_file, storage_path, periode_type, tahun, quarter, bulan,
//            neraca: {...}, laba_rugi: {...}, chart_of_accounts: [{ nama_akun, kelompok_akun, sub_kelompok_akun, nilai_akun }] }
export default function DetailDokumen({ perusahaan, dokumen }) {
    const chartOfAccounts = dokumen.chart_of_accounts ?? [];
    const neracaAkun = chartOfAccounts.filter((akun) => ["aset", "liabilitas", "ekuitas"].includes(akun.kelompok_akun));
    const labaRugiAkun = chartOfAccounts.filter((akun) => ["pendapatan", "beban"].includes(akun.kelompok_akun));

    const neracaDetail = groupByKelompok(neracaAkun, NERACA_LABELS);
    const labaRugiDetail = groupByKelompok(labaRugiAkun, LABA_RUGI_LABELS);

    const neraca = dokumen.neraca ?? {};
    const labaRugi = dokumen.laba_rugi ?? {};

    return (
        <div className="max-w-5xl mx-auto space-y-4">
            <div className="flex items-center justify-between">
                <Link href={`/perusahaan/${perusahaan.id}/dokumen`} className="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-800 gap-1 transition-colors">
                    <ArrowLeft className="w-3.5 h-3.5" /> Kembali ke Berkas Dokumen
                </Link>

                <a
                    href={`/storage/${dokumen.storage_path}`}
                    download
                    className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors"
                >
                    <Download className="w-3.5 h-3.5" /> Unduh Berkas Asli
                </a>
            </div>

            {/* Header dokumen */}
            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-2">
                <div className="flex items-center gap-2.5 text-blue-800">
                    <Building2 className="w-4 h-4 text-blue-600" />
                    <span className="text-sm font-semibold">{perusahaan.nama}</span>
                </div>
                <div className="flex items-center gap-3 pt-1">
                    <div className="w-9 h-9 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center flex-shrink-0">
                        <FileText className="w-4 h-4 text-blue-600" />
                    </div>
                    <div>
                        <p className="text-sm font-semibold text-slate-900 font-mono">{dokumen.nama_file}</p>
                        <Badge variant="outline" className="mt-1">{formatPeriode(dokumen)}</Badge>
                    </div>
                </div>
            </div>

            {/* Hasil ekstraksi */}
            <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-8">
                <div className="space-y-3">
                    <h3 className="text-base font-semibold text-slate-900">Laporan Posisi Keuangan</h3>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <RingkasanCard label="Total Kas & Setara Kas" value={neraca.total_kas_setara_kas} />
                        <RingkasanCard label="Total Aset Lancar" value={neraca.total_asset_lancar} />
                        <RingkasanCard label="Total Aset Tetap" value={neraca.total_asset_tetap} />
                        <RingkasanCard label="Total Aset" value={neraca.total_asset} />
                        <RingkasanCard label="Total Liab. Pendek" value={neraca.total_liabilities_pendek} />
                        <RingkasanCard label="Total Liab. Panjang" value={neraca.total_liabilities_panjang} />
                        <RingkasanCard label="Total Liabilitas" value={neraca.total_liabilities} />
                        <RingkasanCard label="Total Ekuitas" value={neraca.total_equitas} />
                    </div>
                    <div className="space-y-6 mt-4">
                        {neracaDetail.map((group) => (
                            <DetailTable key={group.label} {...group} />
                        ))}
                    </div>
                </div>

                <div className="space-y-3">
                    <h3 className="text-base font-semibold text-slate-900">Laporan Laba Rugi</h3>
                    <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <RingkasanCard label="Total Pendapatan" value={labaRugi.total_pendapatan} />
                        <RingkasanCard label="Total Beban" value={labaRugi.total_beban} />
                        <RingkasanCard label="Laba Sebelum Pajak" value={labaRugi.laba_bersih_sebelum_pajak} />
                        <RingkasanCard label="Total Biaya Pajak" value={labaRugi.total_biaya_pajak} />
                        <RingkasanCard label="Laba Sesudah Pajak" value={labaRugi.laba_bersih_sesudah_pajak} />
                    </div>
                    <div className="space-y-6 mt-4">
                        {labaRugiDetail.map((group) => (
                            <DetailTable key={group.label} {...group} />
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}

DetailDokumen.layout = page => <AppLayout title="Detail Laporan Keuangan" children={page} />;
