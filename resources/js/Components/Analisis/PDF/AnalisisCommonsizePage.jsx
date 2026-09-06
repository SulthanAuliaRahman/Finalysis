import { Page, Text, View } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter, NarasiAiBlock, ChartImageBlock  } from './shared/pdfComponents';

export function AnalisisCommonsizePage({
    perusahaan,
    analisis,
    commonsize,
    chartImageBase64
}) {
    const rows = [
        // Posisi Keuangan
        { label: 'Aset Lancar (%)',           value: commonsize?.aset_lancar_persen },
        { label: 'Aset Tetap (%)',            value: commonsize?.aset_tetap_persen },
        { label: 'Liabilitas Lancar (%)',     value: commonsize?.liabilitas_pendek_persen },
        { label: 'Liabilitas Jangka Panjang (%)', value: commonsize?.liabilitas_panjang_persen },
        { label: 'Ekuitas (%)',               value: commonsize?.ekuitas_persen },

        // Laba Rugi
        { label: 'Pendapatan (%)',            value: commonsize?.pendapatan_persen },
        { label: 'Beban (%)',                 value: commonsize?.beban_persen },
        { label: 'Laba Bersih (%)',           value: commonsize?.laba_bersih_persen },

    ];

    return (
        <Page size="A4" style={pdfStyles.page}>
            <PdfPageHeader
                title="Analisis Common-Size"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[pdfStyles.sectionTitle, pdfStyles.sectionFirst]}>
                Common-Size Analysis
            </Text>
            <View style={pdfStyles.table}>
                <View style={pdfStyles.tableHeader}>
                    <Text style={pdfStyles.tableHeaderCell}>Komponen</Text>
                    <Text style={pdfStyles.tableHeaderCellRight}>Persentase</Text>
                </View>
                {rows.map((row, index) => (
                    <View key={index} style={pdfStyles.tableRow}>
                        <Text style={pdfStyles.tableCell}>{row.label}</Text>
                        <Text style={pdfStyles.tableCellBold}>{row.value}</Text>
                    </View>
                ))}
            </View>
            <NarasiAiBlock narasi={commonsize?.narasi_commonsize_AI} label="Common-Size" />
            <ChartImageBlock judul="Diagram CommonSize" chartImageBase64={chartImageBase64} />

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
