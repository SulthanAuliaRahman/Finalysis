import { Page, Text } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter, TabelRasio, NarasiAiBlock, ChartImageBlock } from './shared/pdfComponents';

export function RasioProfitabilitasPage({ perusahaan, analisis, profitabilitas, neracaSebelumnya, chartImageBase64 }) {
    const adaPeriodeSebelumnya = !!neracaSebelumnya;

    const rows = [
        {
            label:   'Net Profit Margin (NPM) %',
            value:   profitabilitas?.net_profit_margin,
            formula: 'Laba Bersih / Pendapatan',
        },
        {
            label:   'Return on Asset (ROA) %',
            value:   profitabilitas?.ROA,
            formula: adaPeriodeSebelumnya ? 'Laba Bersih / Rata-rata Total Aset' : 'Laba Bersih / Total Aset',
        },
        {
            label:   'Return on Equity (ROE) %',
            value:   profitabilitas?.ROE,
            formula: adaPeriodeSebelumnya ? 'Laba Bersih / Rata-rata Total Ekuitas' : 'Laba Bersih / Total Ekuitas',
        },
    ];

    return (
        <Page size="A4" style={pdfStyles.page}>
            <PdfPageHeader
                title="Rasio Profitabilitas"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[pdfStyles.sectionTitle, pdfStyles.sectionFirst]}>Rasio Profitabilitas</Text>
            <TabelRasio rows={rows} />
            <NarasiAiBlock narasi={profitabilitas?.narasi_profitabilitas_AI} label="Profitabilitas" />
            <ChartImageBlock judul="Grafik Rasio Profitabilitas" chartImageBase64={chartImageBase64} />

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
