import { Page, Text, View } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter, NarasiAiBlock } from './shared/pdfComponents';
import { formatPersentase } from './shared/pdfHelpers';

export function AnalisisCommonsizePage({ perusahaan, analisis, commonsize }) {
    const rows = [
        { label: 'HPP (%)',                   value: commonsize?.hpp_persen },
        { label: 'Laba Kotor (%)',            value: commonsize?.laba_kotor_persen },
        { label: 'Beban Lain & Pajak (%)',    value: commonsize?.beban_lain_pajak_persen },
        { label: 'Laba Bersih (%)',           value: commonsize?.laba_bersih_persen },
        { label: 'Aset Lancar (%)',           value: commonsize?.aset_lancar_persen },
        { label: 'Aset Tetap (%)',            value: commonsize?.aset_tetap_persen },
        { label: 'Liabilitas Lancar (%)',     value: commonsize?.liabilitas_lancar_persen },
        { label: 'Liabilitas Jangka Panjang (%)', value: commonsize?.liabilitas_panjang_persen },
        { label: 'Ekuitas (%)',               value: commonsize?.ekuitas_persen },
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

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
