import { Page, Text, View } from '@react-pdf/renderer';
import { pdfStyles } from './shared/pdfStyles';
import { getTanggalSekarang } from './shared/pdfHelpers';

export function CoverPage({ perusahaan, analisis }) {
    return (
        <Page size="A4" style={pdfStyles.coverPage}>
            <View>
                <Text style={pdfStyles.coverLabel}>Analisis Laporan Keuangan</Text>
                <Text style={pdfStyles.coverTitle}>{perusahaan.nama}</Text>
                <Text style={pdfStyles.coverPeriode}>Periode {analisis.periode_label}</Text>

                <View style={pdfStyles.coverDivider} />

                <View style={{ flexDirection: 'row', gap: 40, marginTop: 10 }}>
                    <View>
                        <Text style={pdfStyles.coverMeta}>Sektor</Text>
                        <Text style={pdfStyles.coverMetaValue}>
                            {perusahaan.sektor ?? 'Tidak diketahui'}
                        </Text>
                    </View>
                    <View>
                        <Text style={pdfStyles.coverMeta}>Tanggal Dibuat</Text>
                        <Text style={pdfStyles.coverMetaValue}>{getTanggalSekarang()}</Text>
                    </View>
                </View>
            </View>

            <Text style={pdfStyles.coverFooter}>
                Dokumen ini digenerate otomatis oleh Finalysis. Seluruh analisis bersifat indikatif
                dan tidak menggantikan opini profesional keuangan.
            </Text>
        </Page>
    );
}
