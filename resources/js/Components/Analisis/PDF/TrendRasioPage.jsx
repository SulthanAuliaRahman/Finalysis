import { Page, Text } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter, NarasiAiBlock, ChartImageBlock, TabelTrendMultiPeriode } from './shared/pdfComponents';
import { formatPersentase, formatRasio, formatLikuiditasDanSolvabilitas, formatLabelPeriode } from './shared/pdfHelpers';

export function TrendRasioPage({ perusahaan, analisis, trendRasio, chartImageBase64 }) {
    const periodeData = trendRasio?.periode_data ?? [];

    const rows = [
        // Liquiditas
        { label: 'Current Ratio (x)', get: (periode) => periode.analisis?.likuiditas?.current_ratio },
        { label: 'Cash Ratio (x)',   get: (periode) => periode.analisis?.likuiditas?.cash_ratio },

        // Profitabilitas
        { label: 'NPM (%)',           get: (periode) => periode.analisis?.profitabilitas?.net_profit_margin },
        { label: 'ROA (%)',           get: (periode) => periode.analisis?.profitabilitas?.ROA },
        { label: 'ROE (%)',           get: (periode) => periode.analisis?.profitabilitas?.ROE },

        // Solvabilitas
        { label: 'DER (x)',           get: (periode) => periode.analisis?.solvabilitas?.debt_to_equity },
        { label: 'DAR (%)',           get: (periode) => periode.analisis?.solvabilitas?.debt_to_asset },
        { label: 'Leverage (X)',      get: (periode) => periode.analisis?.solvabilitas?.leverage_multiplier },

        //Aktivitas
        { label: 'TATO (x)',          get: (periode) => periode.analisis?.aktivitas?.total_asset_turnover },
        { label: 'WCT (x)',          get: (periode) => periode.analisis?.aktivitas?.working_capital_turnover },
        { label: 'FAT (x)',          get: (periode) => periode.analisis?.aktivitas?.fixed_asset_turnover },
    ];

    return (
        <Page size="A4" style={pdfStyles.page}>
            <PdfPageHeader
                title="Tren Rasio Keuangan"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[pdfStyles.sectionTitle, pdfStyles.sectionFirst]}>Tren Rasio Keuangan</Text>
            <TabelTrendMultiPeriode
                periodeData={periodeData}
                rows={rows}
                formatLabelPeriode={formatLabelPeriode}
            />
            <NarasiAiBlock narasi={trendRasio?.narasi_trend_rasio_AI} label="Tren Rasio" />
            <ChartImageBlock
                judul="Grafik Tren Rasio Keuangan"
                chartImageBase64={chartImageBase64}
            />

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
