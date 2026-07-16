import { Page, Text } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter, NarasiAiBlock, ChartImageBlock, TabelTrendMultiPeriode } from './shared/pdfComponents';
import { formatLabelPeriode } from './shared/pdfHelpers';

export function TrendDupontPage({ perusahaan, analisis, trendDupont, chartImageBase64 }) {
    const periodeData = trendDupont?.periode_data ?? [];

    const rows = [
        { label: 'NPM (%)',               get: (periode) => periode.analisis?.dupont?.net_profit_margin },
        { label: 'TATO (x)',              get: (periode) => periode.analisis?.dupont?.total_asset_turnover, },
        { label: 'Leverage (x)',          get: (periode) => periode.analisis?.dupont?.leverage_multiplier, },
        { label: 'ROE (DuPont) %',      get: (periode) => periode.analisis?.dupont?.roe },
    ];

    return (
        <Page size="A4" style={pdfStyles.page}>
            <PdfPageHeader
                title="Tren DuPont"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[pdfStyles.sectionTitle, pdfStyles.sectionFirst]}>Tren Analisis DuPont</Text>
            <TabelTrendMultiPeriode
                periodeData={periodeData}
                rows={rows}
                formatLabelPeriode={formatLabelPeriode}
            />
            <NarasiAiBlock narasi={trendDupont?.narasi_trend_dupont_AI} label="Tren DuPont" />
            <ChartImageBlock
                judul="Grafik Tren DuPont"
                chartImageBase64={chartImageBase64}
            />

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
