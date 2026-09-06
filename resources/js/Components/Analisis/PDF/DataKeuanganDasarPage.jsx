import { Page, Text, View } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter } from './shared/pdfComponents';
import { formatAngka } from './shared/pdfHelpers';

export function DataKeuanganDasarPage({ perusahaan, analisis, neraca, labaRugi }) {
    const neracaRows = [
        { label: 'Kas & Setara Kas',   value: formatAngka(neraca?.total_kas_setara_kas) },
        { label: 'Aset Lancar',        value: formatAngka(neraca?.total_asset_lancar) },
        { label: 'Aset Tetap',         value: formatAngka(neraca?.total_asset_tetap) },
        { label: 'Total Aset',         value: formatAngka(neraca?.total_asset) },
        { label: 'Liabilitas Lancar',  value: formatAngka(neraca?.total_liabilities_pendek) },
        { label: 'Liabilitas Panjang',  value: formatAngka(neraca?.total_liabilities_panjang) },
        { label: 'Total Liabilitas',   value: formatAngka(neraca?.total_liabilities) },
        { label: 'Total Ekuitas',      value: formatAngka(neraca?.total_equitas) },
    ];

    const labaRugiRows = [
        { label: 'Total Pendapatan',  value: formatAngka(labaRugi?.total_pendapatan) },
        { label: 'Total Beban', value: formatAngka(labaRugi?.total_beban) },
        { label: 'Biaya Pajak',  value: formatAngka(labaRugi?.total_biaya_pajak) },
        { label: 'Laba Bersih', value: formatAngka(labaRugi?.laba_bersih_sesudah_pajak) },

    ];

    return (
        <Page size="A4" style={pdfStyles.page}>
            <PdfPageHeader
                title="Data Keuangan Dasar"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[pdfStyles.sectionTitle, pdfStyles.sectionFirst]}>
                Neraca (Balance Sheet)
            </Text>
            <View style={pdfStyles.table}>
                <View style={pdfStyles.tableHeader}>
                    <Text style={pdfStyles.tableHeaderCell}>Akun</Text>
                    <Text style={pdfStyles.tableHeaderCellRight}>Nilai (Rp)</Text>
                </View>
                {neracaRows.map((row, index) => (
                    <View key={index} style={pdfStyles.tableRow}>
                        <Text style={pdfStyles.tableCell}>{row.label}</Text>
                        <Text style={pdfStyles.tableCellBold}>{row.value}</Text>
                    </View>
                ))}
            </View>

            <Text style={pdfStyles.sectionTitle}>Laporan Laba Rugi (Income Statement)</Text>
            <View style={pdfStyles.table}>
                <View style={pdfStyles.tableHeader}>
                    <Text style={pdfStyles.tableHeaderCell}>Akun</Text>
                    <Text style={pdfStyles.tableHeaderCellRight}>Nilai (Rp)</Text>
                </View>
                {labaRugiRows.map((row, index) => (
                    <View key={index} style={pdfStyles.tableRow}>
                        <Text style={pdfStyles.tableCell}>{row.label}</Text>
                        <Text style={pdfStyles.tableCellBold}>{row.value}</Text>
                    </View>
                ))}
            </View>

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
