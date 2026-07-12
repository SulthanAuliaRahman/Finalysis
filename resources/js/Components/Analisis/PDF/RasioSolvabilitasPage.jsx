import { Page, Text } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter, TabelRasio, NarasiAiBlock, ChartImageBlock } from './shared/pdfComponents';
import { formatLikuiditasDanSolvabilitas } from './shared/pdfHelpers';

export function RasioSolvabilitasPage({ perusahaan, analisis, solvabilitas, chartImageBase64 }) {
    const rows = [
        {
            label:   'Debt to Equity Ratio (DER) x',
            value:   solvabilitas?.debt_to_equity,
            formula: 'Total Liabilitas / Total Ekuitas',
        },
        {
            label:   'Debt to Asset Ratio (DAR) x',
            value:   solvabilitas?.debt_to_asset,
            formula: 'Total Liabilitas / Total Aset',
        },
    ];

    return (
        <Page size="A4" style={pdfStyles.page}>
            <PdfPageHeader
                title="Rasio Solvabilitas"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[pdfStyles.sectionTitle, pdfStyles.sectionFirst]}>Rasio Solvabilitas</Text>
            <TabelRasio rows={rows} />
            <NarasiAiBlock narasi={solvabilitas?.narasi_solvabilitas_AI} label="Solvabilitas" />
            <ChartImageBlock judul="Grafik Rasio Solvabilitas" chartImageBase64={chartImageBase64} />

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
