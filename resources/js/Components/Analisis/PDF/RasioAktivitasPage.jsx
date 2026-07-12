import { Page, Text } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter, TabelRasio, NarasiAiBlock, ChartImageBlock } from './shared/pdfComponents';
import { formatRasio } from './shared/pdfHelpers';

export function RasioAktivitasPage({ perusahaan, analisis, aktivitas, chartImageBase64 }) {
    const rows = [
        {
            label:   'Total Asset Turnover (TATO) x',
            value:   aktivitas?.total_asset_turnover,
            formula: 'Pendapatan / Total Aset',
        },
    ];

    return (
        <Page size="A4" style={pdfStyles.page}>
            <PdfPageHeader
                title="Rasio Aktivitas"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[pdfStyles.sectionTitle, pdfStyles.sectionFirst]}>Rasio Aktivitas</Text>
            <TabelRasio rows={rows} />
            <NarasiAiBlock narasi={aktivitas?.narasi_aktivitas_AI} label="Aktivitas" />
            <ChartImageBlock judul="Grafik Rasio Aktivitas" chartImageBase64={chartImageBase64} />

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
