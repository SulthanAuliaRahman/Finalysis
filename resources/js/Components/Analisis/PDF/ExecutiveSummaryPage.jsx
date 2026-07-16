import { Page, Text, View } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter } from './shared/pdfComponents';

export function ExecutiveSummaryPage({ perusahaan, analisis }) {
    return (
        <Page size="A4" style={pdfStyles.page}>
            <PdfPageHeader
                title="Executive Summary"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[pdfStyles.sectionTitle, pdfStyles.sectionFirst]}>
                Ringkasan Eksekutif
            </Text>

            <View style={pdfStyles.summaryBox}>
                <Text style={pdfStyles.summaryText}>{analisis.ai_summary_insight}</Text>
            </View>

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
