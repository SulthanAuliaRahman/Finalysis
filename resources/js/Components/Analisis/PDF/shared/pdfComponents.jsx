import { Text, View, Image } from '@react-pdf/renderer';
import { pdfStyles } from './pdfStyles';

export function PdfPageHeader({ title, sub }) {
    return (
        <View style={pdfStyles.pageHeader} fixed>
            <Text style={pdfStyles.pageHeaderTitle}>{title}</Text>
            {sub && <Text style={pdfStyles.pageHeaderSub}>{sub}</Text>}
        </View>
    );
}

export function PdfPageFooter({ namaPerusahaan, periodeLabel }) {
    return (
        <View style={pdfStyles.pageFooter} fixed>
            <Text style={pdfStyles.footerText}>Finalysis: {namaPerusahaan} · {periodeLabel}</Text>
            <Text
                style={pdfStyles.footerText}
                render={({ pageNumber, totalPages }) => `Halaman ${pageNumber} dari ${totalPages}`}
            />
        </View>
    );
}

export function NarasiAiBlock({ narasi, label }) {
    if (!narasi) {
        return (
            <View style={pdfStyles.noNarasiBox}>
                <Text style={pdfStyles.noNarasiText}>
                    Narasi analitik belum tersedia untuk bagian ini.
                </Text>
            </View>
        );
    }
    return (
        <View style={pdfStyles.narasiBox}>
            <Text style={pdfStyles.narasiLabel}>Narasi AI: {label}</Text>
            <Text style={pdfStyles.narasiText}>{narasi}</Text>
        </View>
    );
}

export function TabelRasio({ rows }) {
    return (
        <View style={pdfStyles.table}>
            <View style={pdfStyles.tableHeader}>
                <Text style={pdfStyles.tableHeaderCell}>Rasio</Text>
                <Text style={pdfStyles.tableHeaderCellRight}>Nilai</Text>
                <Text style={pdfStyles.tableHeaderCellRight}>Formula</Text>
            </View>
            {rows.map((row, index) => (
                <View key={index} style={pdfStyles.tableRow}>
                    <Text style={pdfStyles.tableCell}>{row.label}</Text>
                    <Text style={pdfStyles.tableCellBold}>{row.value}</Text>
                    <Text style={pdfStyles.tableCellRight}>{row.formula ?? '—'}</Text>
                </View>
            ))}
        </View>
    );
}

export function ChartImageBlock({ judul, chartImageBase64 }) {
    if (!chartImageBase64) return null;
    return (
        <>
            <Text style={pdfStyles.chartLabel}>{judul}</Text>
            <Image src={chartImageBase64} style={pdfStyles.chartImage} />
        </>
    );
}


export function TabelTrendMultiPeriode({ periodeData, rows, formatLabelPeriode }) {
    if (!periodeData || periodeData.length < 2) {
        return (
            <View style={pdfStyles.noNarasiBox}>
                <Text style={pdfStyles.noNarasiText}>
                    Data belum cukup untuk analisis tren (minimal 2 periode).
                </Text>
            </View>
        );
    }

    return (
        <View style={pdfStyles.table}>
            <View style={pdfStyles.tableHeader}>
                <Text style={{ ...pdfStyles.tableHeaderCell, flex: 1.5 }}>Item</Text>
                {periodeData.map((periode, index) => (
                    <Text key={index} style={pdfStyles.tableHeaderCellRight}>
                        {formatLabelPeriode(periode.analisis)}
                    </Text>
                ))}
            </View>
            {rows.map((row, rowIndex) => (
                <View key={rowIndex} style={pdfStyles.tableRow}>
                    <Text style={{ ...pdfStyles.tableCell, flex: 1.5 }}>{row.label}</Text>
                    {periodeData.map((periode, periodeIndex) => (
                        <Text key={periodeIndex} style={pdfStyles.tableCellBold}>
                            {row.get(periode)}
                        </Text>
                    ))}
                </View>
            ))}
        </View>
    );
}
