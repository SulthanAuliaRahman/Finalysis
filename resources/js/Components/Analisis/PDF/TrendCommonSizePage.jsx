import { Page, Text } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter, NarasiAiBlock, ChartImageBlock, TabelTrendMultiPeriode } from './shared/pdfComponents';
import {  formatLabelPeriode } from './shared/pdfHelpers';

export function TrendCommonsizePage({ perusahaan, analisis, trendCommonsize, chartImageBase64 }) {
    const periodeData = trendCommonsize?.periode_data ?? [];

    const rows = [
        { label: 'Laba Kotor (%)',  get: (periode) => periode.analisis?.commonsize?.laba_kotor_persen },
        { label: 'Laba Bersih (%)', get: (periode) => periode.analisis?.commonsize?.laba_bersih_persen },
        { label: 'Ekuitas (%)',     get: (periode) => periode.analisis?.commonsize?.ekuitas_persen },
    ];

    return (
        <Page size="A4" style={pdfStyles.page}>
            <PdfPageHeader
                title="Tren Common-Size"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[pdfStyles.sectionTitle, pdfStyles.sectionFirst]}>Tren Analisis Common-Size</Text>
            <TabelTrendMultiPeriode
                periodeData={periodeData}
                rows={rows}
                formatLabelPeriode={formatLabelPeriode}
            />
            <NarasiAiBlock narasi={trendCommonsize?.narasi_trend_commonsize_AI} label="Tren Common-Size" />
            <ChartImageBlock
                judul="Grafik Tren Common-Size"
                chartImageBase64={chartImageBase64}
            />

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
