import { Page, Text } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { PdfPageHeader, PdfPageFooter, NarasiAiBlock, ChartImageBlock, TabelTrendMultiPeriode } from './shared/pdfComponents';
import { formatAngka,formatLabelPeriode } from './shared/pdfHelpers';

export function TrendAkunUtamaPage({ perusahaan, analisis, trendAkunUtama, chartImageBase64 }) {
    const periodeData = trendAkunUtama?.periode_data ?? [];

    const rows = [
        { label: 'Trend Aset',    get: (periode) => formatAngka(periode.total_asset) },
        { label: 'Trend liabilites',    get: (periode) => formatAngka(periode.total_liabilities) },
        { label: 'Trend Ekuitas', get: (periode) => formatAngka(periode.total_equitas) },
        { label: 'Trend Pendapatan',    get: (periode) => formatAngka(periode.total_pendapatan) },
        { label: 'Trend Beban',   get: (periode) => formatAngka(periode.total_beban) },
    ];

    return (
        <Page size="A4" style={pdfStyles.page}>
            <PdfPageHeader
                title="Tren Akun Utama"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[pdfStyles.sectionTitle, pdfStyles.sectionFirst]}>Tren Akun Utama</Text>
            <TabelTrendMultiPeriode
                periodeData={periodeData}
                rows={rows}
                formatLabelPeriode={formatLabelPeriode}
            />
            <NarasiAiBlock
                narasi={trendAkunUtama?.narasi_trend_akun_utama_AI}
                label="Tren Akun Utama"
            />
            <ChartImageBlock
                judul="Grafik Tren Akun Utama"
                chartImageBase64={chartImageBase64}
            />

            <PdfPageFooter namaPerusahaan={perusahaan.nama} periodeLabel={analisis.periode_label} />
        </Page>
    );
}
