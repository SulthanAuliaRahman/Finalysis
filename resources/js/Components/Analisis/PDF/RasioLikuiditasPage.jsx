import { Page, Text } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter, TabelRasio, NarasiAiBlock, ChartImageBlock } from './shared/pdfComponents';

export function RasioLikuiditasPage({ perusahaan, analisis, likuiditas, chartImageBase64 }) {
    const rows = [
        {
            label:   'Current Ratio (x) ',
            value:   likuiditas?.current_ratio,
            formula: 'Aset Lancar / Liabilitas Lancar',
        },
        {
            label:   'Quick Ratio (x)',
            value:   likuiditas?.quick_ratio,
            formula: '(Aset Lancar - Persediaan) / Liabilitas Lancar',
        },
        {
            label:   'Cash Ratio (x)',
            value:   likuiditas?.cash_ratio,
            formula: 'Kas / Liabilitas Lancar',
        },
    ];

    return (
        <Page size="A4" style={pdfStyles.page}>
            <PdfPageHeader
                title="Rasio Likuiditas"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[pdfStyles.sectionTitle, pdfStyles.sectionFirst]}>Rasio Likuiditas</Text>
            <TabelRasio rows={rows} />
            <NarasiAiBlock narasi={likuiditas?.narasi_likuiditas_AI} label="Likuiditas" />
            <ChartImageBlock judul="Grafik Rasio Likuiditas" chartImageBase64={chartImageBase64} />

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
