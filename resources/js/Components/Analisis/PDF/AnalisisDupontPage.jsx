import { Page, Text } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter, TabelRasio, NarasiAiBlock, ChartImageBlock  } from './shared/pdfComponents';
import { formatPersentase, formatRasio } from './shared/pdfHelpers';

export function AnalisisDupontPage({
    perusahaan,
    analisis,
    dupont,
    chartImageBase64
}) {
    const rows = [
        {
            label:   'Net Profit Margin (%)',
            value:   dupont?.net_profit_margin,
            formula: 'Laba Bersih / Pendapatan',
        },
        {
            label:   'Total Asset Turnover (x)',
            value:   dupont?.total_asset_turnover,
            formula: 'Pendapatan / Total Aset',
        },
        {
            label:   'Leverage Multiplier (x)',
            value:   dupont?.leverage_multiplier,
            formula: 'Total Aset / Total Ekuitas',
        },
        {
            label:   'Return on Equity / ROE (DuPont) (%)',
            value:   dupont?.roe,
            formula: 'NPM × TATO × Leverage',
        },
    ];

    return (
        <Page size="A4" style={pdfStyles.page}>
            <PdfPageHeader
                title="Analisis DuPont"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[pdfStyles.sectionTitle, pdfStyles.sectionFirst]}>Analisis DuPont</Text>
            <TabelRasio rows={rows} />
            <NarasiAiBlock narasi={dupont?.narasi_dupont_AI} label="DuPont" />
            <ChartImageBlock judul="Grafik Dupont" chartImageBase64={chartImageBase64} />

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
