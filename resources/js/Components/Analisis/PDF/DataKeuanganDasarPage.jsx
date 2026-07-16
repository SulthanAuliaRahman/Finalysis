import { Page, Text, View } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter } from './shared/pdfComponents';
import { formatAngka } from './shared/pdfHelpers';

export function DataKeuanganDasarPage({ perusahaan, analisis, neraca, labaRugi }) {
    const neracaRows = [
        { label: 'Kas & Setara Kas',   value: formatAngka(neraca?.cash_equivalent) },
        { label: 'Persediaan',         value: formatAngka(neraca?.inventory) },
        { label: 'Aset Lancar',        value: formatAngka(neraca?.current_assets) },
        { label: 'Total Aset',         value: formatAngka(neraca?.total_assets) },
        { label: 'Liabilitas Lancar',  value: formatAngka(neraca?.current_liabilities) },
        { label: 'Total Liabilitas',   value: formatAngka(neraca?.total_liabilities) },
        { label: 'Total Ekuitas',      value: formatAngka(neraca?.total_equity) },
    ];

    const labaRugiRows = [
        { label: 'Pendapatan',  value: formatAngka(labaRugi?.pendapatan) },
        { label: 'Laba Kotor',  value: formatAngka(labaRugi?.laba_kotor) },
        { label: 'Laba Bersih', value: formatAngka(labaRugi?.laba_bersih) },
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
