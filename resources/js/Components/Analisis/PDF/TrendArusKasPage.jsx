import { Page, Text } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter, NarasiAiBlock, ChartImageBlock, TabelTrendMultiPeriode } from './shared/pdfComponents';
import { formatAngka, formatLabelPeriode } from './shared/pdfHelpers';

export function TrendArusKasPage({ perusahaan, analisis, trendArusKas, chartImageBase64 }) {
    const periodeData = trendArusKas?.periode_data ?? [];

    const rows = [
        { label: 'Arus Kas Masuk',   get: (periode) => formatAngka(periode.kas_masuk) },
        { label: 'Arus Kas Keluar', get: (periode) => formatAngka(periode.kas_keluar) },
    ];

    return (
        <Page size="A4" style={pdfStyles.page}>
            <PdfPageHeader
                title="Tren Arus Kas"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[pdfStyles.sectionTitle, pdfStyles.sectionFirst]}>Tren Arus Kas</Text>
            <TabelTrendMultiPeriode
                periodeData={periodeData}
                rows={rows}
                formatLabelPeriode={formatLabelPeriode}
            />
            <NarasiAiBlock narasi={trendArusKas?.narasi_trend_arus_kas_AI} label="Tren Arus Kas" />
            <ChartImageBlock
                judul="Grafik Tren Arus Kas"
                chartImageBase64={chartImageBase64}
            />

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
