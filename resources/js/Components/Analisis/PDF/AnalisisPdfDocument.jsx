import {
    Document, Page, Text, View, Image, StyleSheet, Font,
} from '@react-pdf/renderer';

// Helpers

const fmt = (val) =>
    val !== null && val !== undefined
        ? new Intl.NumberFormat('id-ID').format(val)
        : '—';

const fmtPct = (val) =>
    val !== null && val !== undefined
        ? `${Number(val * 100).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}%`
        : '—';

const fmtRatio = (val, suffix = 'x') =>
    val !== null && val !== undefined
        ? `${Number(val).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}${suffix}`
        : '—';

const fmtLikuiditas = (val) =>
    val !== null && val !== undefined
        ? `${Number(val / 100).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}x`
        : '—';

const labelPeriode = (a) => {
    if (!a) return '—';
    if (a.periode_type === 'annual') return `${a.tahun}`;
    if (a.periode_type === 'quarterly') return `Q${a.quarter} ${a.tahun}`;
    return `Bln ${a.bulan} ${a.tahun}`;
};

const tanggalSekarang = () => {
    return new Date().toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric',
    });
};

// Styles
// Styles
const S = StyleSheet.create({
    page: {
        fontFamily: 'Helvetica',
        fontSize: 10,
        color: '#000000',
        paddingTop: 40,
        paddingBottom: 50,
        paddingHorizontal: 40,
        backgroundColor: '#ffffff',
    },

    // ── Cover (Minimalis & Elegan)
    coverPage: {
        fontFamily: 'Helvetica',
        backgroundColor: '#ffffff',
        paddingHorizontal: 50,
        paddingVertical: 80,
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'center',
        height: '100%',
    },
    coverLabel: {
        fontSize: 10,
        color: '#666666',
        letterSpacing: 2,
        textTransform: 'uppercase',
        marginBottom: 12,
    },
    coverTitle: {
        fontSize: 28,
        fontFamily: 'Helvetica-Bold',
        color: '#000000',
        marginBottom: 8,
        lineHeight: 1.2,
    },
    coverPeriode: {
        fontSize: 14,
        color: '#333333',
        marginBottom: 40,
    },
    coverDivider: {
        borderBottomWidth: 1.5,
        borderBottomColor: '#000000',
        marginVertical: 24,
    },
    coverMeta: {
        fontSize: 10,
        color: '#666666',
        marginBottom: 4,
    },
    coverMetaValue: {
        fontSize: 10,
        color: '#000000',
        fontFamily: 'Helvetica-Bold',
    },
    coverFooter: {
        position: 'absolute',
        bottom: 50,
        left: 50,
        right: 50,
        fontSize: 9,
        color: '#999999',
        textAlign: 'center',
        borderTopWidth: 1,
        borderTopColor: '#eeeeee',
        paddingTop: 10,
    },

    // ── Header per halaman
    pageHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 20,
        paddingBottom: 8,
        borderBottomWidth: 1,
        borderBottomColor: '#000000',
    },
    pageHeaderTitle: {
        fontSize: 12,
        fontFamily: 'Helvetica-Bold',
        color: '#000000',
    },
    pageHeaderSub: {
        fontSize: 10,
        color: '#666666',
    },

    // ── Section
    sectionTitle: {
        fontSize: 11,
        fontFamily: 'Helvetica-Bold',
        color: '#000000',
        marginBottom: 10,
        marginTop: 20,
        textTransform: 'uppercase',
    },
    sectionFirst: {
        marginTop: 0,
    },

    // ── Tabel (Clean Lines, No Background Colors)
    table: {
        width: '100%',
        marginBottom: 16,
        borderTopWidth: 1,
        borderTopColor: '#000000',
    },
    tableHeader: {
        flexDirection: 'row',
        paddingVertical: 6,
        paddingHorizontal: 4,
        borderBottomWidth: 1,
        borderBottomColor: '#000000',
    },
    tableHeaderCell: {
        fontSize: 9,
        fontFamily: 'Helvetica-Bold',
        color: '#000000',
        flex: 1,
    },
    tableHeaderCellRight: {
        fontSize: 9,
        fontFamily: 'Helvetica-Bold',
        color: '#000000',
        flex: 1,
        textAlign: 'right',
    },
    tableRow: {
        flexDirection: 'row',
        paddingVertical: 6,
        paddingHorizontal: 4,
        borderBottomWidth: 1,
        borderBottomColor: '#eeeeee', // Garis pemisah yang halus
    },
    tableCell: {
        fontSize: 9,
        color: '#333333',
        flex: 1,
    },
    tableCellRight: {
        fontSize: 9,
        color: '#333333',
        flex: 1,
        textAlign: 'right',
    },
    tableCellBold: {
        fontSize: 9,
        fontFamily: 'Helvetica-Bold',
        color: '#000000',
        flex: 1,
        textAlign: 'right',
    },

    // ── Narasi AI (Left border minimalist)
    narasiBox: {
        borderLeftWidth: 2,
        borderLeftColor: '#000000',
        paddingLeft: 12,
        paddingVertical: 4,
        marginTop: 8,
        marginBottom: 16,
    },
    narasiLabel: {
        fontSize: 9,
        fontFamily: 'Helvetica-Bold',
        color: '#000000',
        marginBottom: 4,
        textTransform: 'uppercase',
    },
    narasiText: {
        fontSize: 9,
        color: '#333333',
        lineHeight: 1.5,
        textAlign: 'justify',
    },
    noNarasiBox: {
        borderWidth: 1,
        borderColor: '#dddddd',
        padding: 10,
        marginTop: 8,
        marginBottom: 16,
    },
    noNarasiText: {
        fontSize: 9,
        color: '#999999',
        textAlign: 'center',
        fontStyle: 'italic',
    },

    // ── Chart image
    chartImage: {
        width: '100%',
        marginTop: 12,
        marginBottom: 16,
    },

    // ── Footer
    pageFooter: {
        position: 'absolute',
        bottom: 25,
        left: 40,
        right: 40,
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        borderTopWidth: 1,
        borderTopColor: '#dddddd',
        paddingTop: 8,
    },
    footerText: {
        fontSize: 8,
        color: '#666666',
    },

    // ── Summary box
    summaryBox: {
        borderWidth: 1,
        borderColor: '#000000',
        padding: 16,
        marginTop: 8,
    },
    summaryText: {
        fontSize: 10,
        color: '#000000',
        lineHeight: 1.6,
        textAlign: 'justify',
    },
});

//  Sub-components

function PageFooter({ perusahaan, periode }) {
    return (
        <View style={S.pageFooter} fixed>
            <Text style={S.footerText}>Finalysis — {perusahaan} · {periode}</Text>
            <Text style={S.footerText} render={({ pageNumber, totalPages }) =>
                `Halaman ${pageNumber} dari ${totalPages}`
            } />
        </View>
    );
}

function PageHeader({ title, sub }) {
    return (
        <View style={S.pageHeader} fixed>
            <Text style={S.pageHeaderTitle}>{title}</Text>
            {sub && <Text style={S.pageHeaderSub}>{sub}</Text>}
        </View>
    );
}

function NarasiBlock({ narasi, label }) {
    if (!narasi) {
        return (
            <View style={S.noNarasiBox}>
                <Text style={S.noNarasiText}>Narasi analitik belum tersedia untuk bagian ini.</Text>
            </View>
        );
    }
    return (
        <View style={S.narasiBox}>
            <Text style={S.narasiLabel}>Insight AI: {label}</Text>
            <Text style={S.narasiText}>{narasi}</Text>
        </View>
    );
}

function TabelRasio({ rows }) {
    return (
        <View style={S.table}>
            <View style={S.tableHeader}>
                <Text style={S.tableHeaderCell}>Rasio</Text>
                <Text style={S.tableHeaderCellRight}>Nilai</Text>
                <Text style={S.tableHeaderCellRight}>Formula</Text>
            </View>
            {rows.map((row, i) => (
                <View key={i} style={S.tableRow}>
                    <Text style={S.tableCell}>{row.label}</Text>
                    <Text style={S.tableCellBold}>{row.value}</Text>
                    <Text style={S.tableCellRight}>{row.formula ?? '—'}</Text>
                </View>
            ))}
        </View>
    );
}

// Halaman: Cover

function CoverPage({ perusahaan, analisis }) {
    return (
        <Page size="A4" style={S.coverPage}>
            <View>
                <Text style={S.coverLabel}>Analisis Laporan Keuangan</Text>
                <Text style={S.coverTitle}>{perusahaan.nama}</Text>
                <Text style={S.coverPeriode}>Periode {analisis.periode_label}</Text>

                <View style={S.coverDivider} />

                <View style={{ flexDirection: 'row', gap: 40, marginTop: 10 }}>
                    <View>
                        <Text style={S.coverMeta}>Sektor</Text>
                        <Text style={S.coverMetaValue}>{perusahaan.sektor ?? 'Tidak diketahui'}</Text>
                    </View>
                    <View>
                        <Text style={S.coverMeta}>Tanggal Dibuat</Text>
                        <Text style={S.coverMetaValue}>{tanggalSekarang()}</Text>
                    </View>
                </View>
            </View>

            <Text style={S.coverFooter}>
                Dokumen ini digenerate otomatis oleh Finalysis. Seluruh analisis bersifat indikatif dan tidak menggantikan opini profesional keuangan.
            </Text>
        </Page>
    );
}

// Halaman: Executive Summary

function SummaryPage({ analisis, perusahaan }) {
    return (
        <Page size="A4" style={S.page}>
            <PageHeader
                title="Executive Summary"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />
            <Text style={[S.sectionTitle, S.sectionFirst]}>Ringkasan Eksekutif</Text>
            <View style={S.summaryBox}>
                <Text style={S.summaryText}>{analisis.ai_summary_insight}</Text>
            </View>
            <PageFooter perusahaan={perusahaan.nama} periode={analisis.periode_label} />
        </Page>
    );
}

// Halaman: Data Keuangan Dasar

function DataKeuanganPage({ neraca, labaRugi, perusahaan, analisis }) {
    const neracaRows = [
        { label: 'Kas & Setara Kas',      value: fmt(neraca?.cash_equivalent) },
        { label: 'Persediaan',             value: fmt(neraca?.inventory) },
        { label: 'Aset Lancar',            value: fmt(neraca?.current_assets) },
        { label: 'Total Aset',             value: fmt(neraca?.total_assets) },
        { label: 'Liabilitas Lancar',      value: fmt(neraca?.current_liabilities) },
        { label: 'Total Liabilitas',       value: fmt(neraca?.total_liabilities) },
        { label: 'Total Ekuitas',          value: fmt(neraca?.total_equity) },
    ];

    const labaRugiRows = [
        { label: 'Pendapatan',   value: fmt(labaRugi?.pendapatan) },
        { label: 'Laba Kotor',   value: fmt(labaRugi?.laba_kotor) },
        { label: 'Laba Bersih',  value: fmt(labaRugi?.laba_bersih) },
    ];

    return (
        <Page size="A4" style={S.page}>
            <PageHeader
                title="Data Keuangan Dasar"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[S.sectionTitle, S.sectionFirst]}>Neraca (Balance Sheet)</Text>
            <View style={S.table}>
                <View style={S.tableHeader}>
                    <Text style={S.tableHeaderCell}>Akun</Text>
                    <Text style={S.tableHeaderCellRight}>Nilai (Rp)</Text>
                </View>
                {neracaRows.map((row, i) => (
                    <View key={i} style={[S.tableRow, i % 2 === 1 && S.tableRowAlt]}>
                        <Text style={S.tableCell}>{row.label}</Text>
                        <Text style={S.tableCellBold}>{row.value}</Text>
                    </View>
                ))}
            </View>

            <Text style={S.sectionTitle}>Laporan Laba Rugi (Income Statement)</Text>
            <View style={S.table}>
                <View style={S.tableHeader}>
                    <Text style={S.tableHeaderCell}>Akun</Text>
                    <Text style={S.tableHeaderCellRight}>Nilai (Rp)</Text>
                </View>
                {labaRugiRows.map((row, i) => (
                    <View key={i} style={[S.tableRow, i % 2 === 1 && S.tableRowAlt]}>
                        <Text style={S.tableCell}>{row.label}</Text>
                        <Text style={S.tableCellBold}>{row.value}</Text>
                    </View>
                ))}
            </View>

            <PageFooter perusahaan={perusahaan.nama} periode={analisis.periode_label} />
        </Page>
    );
}

// Halaman: Rasio Keuangan

function RasioPage({ likuiditas, profitabilitas, solvabilitas, aktivitas, dupont, commonsize, perusahaan, analisis }) {
    const likuiditasRows = [
        { label: 'Current Ratio', value: fmtLikuiditas(likuiditas?.current_ratio), formula: 'Aset Lancar / Liabilitas Lancar' },
        { label: 'Quick Ratio',   value: fmtLikuiditas(likuiditas?.quick_ratio),   formula: '(Aset Lancar - Persediaan) / Liabilitas Lancar' },
        { label: 'Cash Ratio',    value: fmtLikuiditas(likuiditas?.cash_ratio),    formula: 'Kas / Liabilitas Lancar' },
    ];

    const profitabilitasRows = [
        { label: 'Net Profit Margin (NPM)', value: fmtPct(profitabilitas?.net_profit_margin), formula: 'Laba Bersih / Pendapatan' },
        { label: 'Return on Asset (ROA)',   value: fmtPct(profitabilitas?.ROA),               formula: 'Laba Bersih / Total Aset' },
        { label: 'Return on Equity (ROE)',  value: fmtPct(profitabilitas?.ROE),               formula: 'Laba Bersih / Total Ekuitas' },
    ];

    const solvabilitasRows = [
        { label: 'Debt to Equity (DER)', value: fmtPct(solvabilitas?.debt_to_equity), formula: 'Total Liabilitas / Total Ekuitas' },
        { label: 'Debt to Asset (DAR)',  value: fmtPct(solvabilitas?.debt_to_asset),  formula: 'Total Liabilitas / Total Aset' },
    ];

    const aktivitasRows = [
        { label: 'Total Asset Turnover (TATO)', value: fmtRatio(aktivitas?.total_asset_turnover, 'x'), formula: 'Pendapatan / Total Aset' },
    ];

    const dupontRows = [
        { label: 'Net Profit Margin',     value: fmtPct(dupont?.net_profit_margin),    formula: 'Laba Bersih / Pendapatan' },
        { label: 'Total Asset Turnover',  value: fmtRatio(dupont?.total_asset_turnover, 'x'), formula: 'Pendapatan / Total Aset' },
        { label: 'Leverage Multiplier',   value: fmtRatio(dupont?.leverage_multiplier, 'x'), formula: 'Total Aset / Total Ekuitas' },
        { label: 'ROE (DuPont)',          value: fmtPct(dupont?.roe),                  formula: 'NPM × TATO × Leverage' },
    ];

    const commonsizeRows = [
        { label: 'HPP (%)',                   value: fmtPct(commonsize?.hpp_persen) },
        { label: 'Laba Kotor (%)',            value: fmtPct(commonsize?.laba_kotor_persen) },
        { label: 'Beban Lain & Pajak (%)',    value: fmtPct(commonsize?.beban_lain_pajak_persen) },
        { label: 'Laba Bersih (%)',           value: fmtPct(commonsize?.laba_bersih_persen) },
        { label: 'Aset Lancar (%)',           value: fmtPct(commonsize?.aset_lancar_persen) },
        { label: 'Aset Tetap (%)',            value: fmtPct(commonsize?.aset_tetap_persen) },
        { label: 'Liabilitas Lancar (%)',     value: fmtPct(commonsize?.liabilitas_lancar_persen) },
        { label: 'Liabilitas Panjang (%)',    value: fmtPct(commonsize?.liabilitas_panjang_persen) },
        { label: 'Ekuitas (%)',               value: fmtPct(commonsize?.ekuitas_persen) },
    ];

    return (
        <Page size="A4" style={S.page}>
            <PageHeader
                title="Rasio Keuangan"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            {/* Likuiditas */}
            <Text style={[S.sectionTitle, S.sectionFirst]}>Rasio Likuiditas</Text>
            <TabelRasio rows={likuiditasRows} />
            <NarasiBlock narasi={likuiditas?.narasi_likuiditas_AI} label="Likuiditas" />

            {/* Profitabilitas */}
            <Text style={S.sectionTitle}>Rasio Profitabilitas</Text>
            <TabelRasio rows={profitabilitasRows} />
            <NarasiBlock narasi={profitabilitas?.narasi_profitabilitas_AI} label="Profitabilitas" />

            <PageFooter perusahaan={perusahaan.nama} periode={analisis.periode_label} />
        </Page>
    );
}

function RasioPage2({ solvabilitas, aktivitas, dupont, commonsize, perusahaan, analisis }) {
    const solvabilitasRows = [
        { label: 'Debt to Equity (DER)', value: fmtPct(solvabilitas?.debt_to_equity), formula: 'Total Liabilitas / Total Ekuitas' },
        { label: 'Debt to Asset (DAR)',  value: fmtPct(solvabilitas?.debt_to_asset),  formula: 'Total Liabilitas / Total Aset' },
    ];

    const aktivitasRows = [
        { label: 'Total Asset Turnover (TATO)', value: fmtRatio(aktivitas?.total_asset_turnover, 'x'), formula: 'Pendapatan / Total Aset' },
    ];

    const dupontRows = [
        { label: 'Net Profit Margin',    value: fmtPct(dupont?.net_profit_margin),         formula: 'Laba Bersih / Pendapatan' },
        { label: 'Total Asset Turnover', value: fmtRatio(dupont?.total_asset_turnover, 'x'), formula: 'Pendapatan / Total Aset' },
        { label: 'Leverage Multiplier',  value: fmtRatio(dupont?.leverage_multiplier, 'x'), formula: 'Total Aset / Total Ekuitas' },
        { label: 'ROE (DuPont)',         value: fmtPct(dupont?.roe),                        formula: 'NPM × TATO × Leverage' },
    ];

    const commonsizeRows = [
        { label: 'HPP (%)',                value: fmtPct(commonsize?.hpp_persen) },
        { label: 'Laba Kotor (%)',         value: fmtPct(commonsize?.laba_kotor_persen) },
        { label: 'Beban Lain & Pajak (%)', value: fmtPct(commonsize?.beban_lain_pajak_persen) },
        { label: 'Laba Bersih (%)',        value: fmtPct(commonsize?.laba_bersih_persen) },
        { label: 'Aset Lancar (%)',        value: fmtPct(commonsize?.aset_lancar_persen) },
        { label: 'Aset Tetap (%)',         value: fmtPct(commonsize?.aset_tetap_persen) },
        { label: 'Liabilitas Lancar (%)',  value: fmtPct(commonsize?.liabilitas_lancar_persen) },
        { label: 'Liabilitas Panjang (%)', value: fmtPct(commonsize?.liabilitas_panjang_persen) },
        { label: 'Ekuitas (%)',            value: fmtPct(commonsize?.ekuitas_persen) },
    ];

    return (
        <Page size="A4" style={S.page}>
            <PageHeader
                title="Rasio Keuangan (Lanjutan)"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[S.sectionTitle, S.sectionFirst]}>Rasio Solvabilitas</Text>
            <TabelRasio rows={solvabilitasRows} />
            <NarasiBlock narasi={solvabilitas?.narasi_solvabilitas_AI} label="Solvabilitas" />

            <Text style={S.sectionTitle}>Rasio Aktivitas</Text>
            <TabelRasio rows={aktivitasRows} />
            <NarasiBlock narasi={aktivitas?.narasi_aktivitas_AI} label="Aktivitas" />

            <Text style={S.sectionTitle}>Analisis DuPont</Text>
            <TabelRasio rows={dupontRows} />
            <NarasiBlock narasi={dupont?.narasi_dupont_AI} label="DuPont" />

            <Text style={S.sectionTitle}>Common-Size Analysis</Text>
            <View style={S.table}>
                <View style={S.tableHeader}>
                    <Text style={S.tableHeaderCell}>Komponen</Text>
                    <Text style={S.tableHeaderCellRight}>Persentase</Text>
                </View>
                {commonsizeRows.map((row, i) => (
                    <View key={i} style={[S.tableRow, i % 2 === 1 && S.tableRowAlt]}>
                        <Text style={S.tableCell}>{row.label}</Text>
                        <Text style={S.tableCellBold}>{row.value}</Text>
                    </View>
                ))}
            </View>
            <NarasiBlock narasi={commonsize?.narasi_commonsize_AI} label="Common-Size" />

            <PageFooter perusahaan={perusahaan.nama} periode={analisis.periode_label} />
        </Page>
    );
}

// Halaman: Analisis Trend

function TrendTableContent({ periodeData, rows }) {
    if (!periodeData || periodeData.length < 2) return null;

    return (
        <View style={S.table}>
            <View style={S.tableHeader}>
                <Text style={{ ...S.tableHeaderCell, flex: 1.5 }}>Item</Text>
                {periodeData.map((p, i) => (
                    <Text key={i} style={S.tableHeaderCellRight}>{labelPeriode(p.analisis)}</Text>
                ))}
            </View>
            {rows.map((row, ri) => (
                <View key={ri} style={[S.tableRow, ri % 2 === 1 && S.tableRowAlt]}>
                    <Text style={{ ...S.tableCell, flex: 1.5 }}>{row.label}</Text>
                    {periodeData.map((p, pi) => (
                        <Text key={pi} style={S.tableCellBold}>{row.get(p)}</Text>
                    ))}
                </View>
            ))}
        </View>
    );
}

function TrendPage({ trendAkunUtama, trendRasio, chartImages, perusahaan, analisis }) {
    const periodeAkun  = trendAkunUtama?.periode_data ?? [];
    const periodeRasio = trendRasio?.periode_data ?? [];

    const akunRows = [
        { label: 'Total Aset',     get: (p) => fmt(p.analisis?.neraca?.total_assets) },
        { label: 'Total Ekuitas',  get: (p) => fmt(p.analisis?.neraca?.total_equity) },
        { label: 'Pendapatan',     get: (p) => fmt(p.analisis?.laba_rugi?.pendapatan) },
        { label: 'Laba Bersih',    get: (p) => fmt(p.analisis?.laba_rugi?.laba_bersih) },
    ];

    const rasioRows = [
        { label: 'Current Ratio', get: (p) => fmtLikuiditas(p.analisis?.likuiditas?.current_ratio) },
        { label: 'Quick Ratio',   get: (p) => fmtLikuiditas(p.analisis?.likuiditas?.quick_ratio) },
        { label: 'NPM',           get: (p) => fmtPct(p.analisis?.profitabilitas?.net_profit_margin) },
        { label: 'ROA',           get: (p) => fmtPct(p.analisis?.profitabilitas?.ROA) },
        { label: 'ROE',           get: (p) => fmtPct(p.analisis?.profitabilitas?.ROE) },
        { label: 'DER',           get: (p) => fmtPct(p.analisis?.solvabilitas?.debt_to_equity) },
        { label: 'TATO',          get: (p) => fmtRatio(p.analisis?.aktivitas?.total_asset_turnover, 'x') },
    ];

    return (
        <Page size="A4" style={S.page}>
            <PageHeader
                title="Analisis Tren"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[S.sectionTitle, S.sectionFirst]}>Tren Akun Utama</Text>
            {periodeAkun.length < 2 ? (
                <View style={S.noNarasiBox}>
                    <Text style={S.noNarasiText}>Data belum cukup (min. 2 periode).</Text>
                </View>
            ) : (
                <TrendTableContent periodeData={periodeAkun} rows={akunRows} />
            )}
            <NarasiBlock narasi={trendAkunUtama?.narasi_trend_akun_utama_AI} label="Tren Akun Utama" />

            {chartImages?.akunUtama && (
                <>
                    <Text style={S.chartLabel}>Grafik Tren Akun Utama</Text>
                    <Image src={chartImages.akunUtama} style={S.chartImage} />
                </>
            )}

            <Text style={S.sectionTitle}>Tren Rasio Keuangan</Text>
            {periodeRasio.length < 2 ? (
                <View style={S.noNarasiBox}>
                    <Text style={S.noNarasiText}>Data belum cukup (min. 2 periode).</Text>
                </View>
            ) : (
                <TrendTableContent periodeData={periodeRasio} rows={rasioRows} />
            )}
            <NarasiBlock narasi={trendRasio?.narasi_trend_rasio_AI} label="Tren Rasio" />

            {chartImages?.rasio && (
                <>
                    <Text style={S.chartLabel}>Grafik Tren Rasio Keuangan</Text>
                    <Image src={chartImages.rasio} style={S.chartImage} />
                </>
            )}

            <PageFooter perusahaan={perusahaan.nama} periode={analisis.periode_label} />
        </Page>
    );
}

function TrendPage2({ trendDupont, trendCommonsize, trendArusKas, chartImages, perusahaan, analisis }) {
    const periodeDupont     = trendDupont?.periode_data ?? [];
    const periodeCommonsize = trendCommonsize?.periode_data ?? [];
    const periodeArusKas    = trendArusKas?.periode_data ?? [];

    const dupontRows = [
        { label: 'NPM',       get: (p) => fmtPct(p.analisis?.dupont?.net_profit_margin) },
        { label: 'TATO',      get: (p) => fmtRatio(p.analisis?.dupont?.total_asset_turnover, 'x') },
        { label: 'Leverage',  get: (p) => fmtRatio(p.analisis?.dupont?.leverage_multiplier, 'x') },
        { label: 'ROE',       get: (p) => fmtPct(p.analisis?.dupont?.roe) },
    ];

    const commonsizeRows = [
        { label: 'Laba Kotor (%)',  get: (p) => fmtPct(p.analisis?.commonsize?.laba_kotor_persen) },
        { label: 'Laba Bersih (%)', get: (p) => fmtPct(p.analisis?.commonsize?.laba_bersih_persen) },
        { label: 'Ekuitas (%)',     get: (p) => fmtPct(p.analisis?.commonsize?.ekuitas_persen) },
    ];

    const arusKasRows = [
        { label: 'CFO', get: (p) => fmt(p.analisis?.arus_kas?.cash_flow_from_operations) },
        { label: 'CFI', get: (p) => fmt(p.analisis?.arus_kas?.cash_flow_from_investing) },
        { label: 'CFF', get: (p) => fmt(p.analisis?.arus_kas?.cash_flow_from_financing) },
    ];

    return (
        <Page size="A4" style={S.page}>
            <PageHeader
                title="Analisis Tren (Lanjutan)"
                sub={`${perusahaan.nama} · ${analisis.periode_label}`}
            />

            <Text style={[S.sectionTitle, S.sectionFirst]}>Tren DuPont</Text>
            {periodeDupont.length < 2 ? (
                <View style={S.noNarasiBox}><Text style={S.noNarasiText}>Data belum cukup.</Text></View>
            ) : (
                <TrendTableContent periodeData={periodeDupont} rows={dupontRows} />
            )}
            <NarasiBlock narasi={trendDupont?.narasi_trend_dupont_AI} label="Tren DuPont" />
            {chartImages?.dupont && (
                <>
                    <Text style={S.chartLabel}>Grafik Tren DuPont</Text>
                    <Image src={chartImages.dupont} style={S.chartImage} />
                </>
            )}

            <Text style={S.sectionTitle}>Tren Common-Size</Text>
            {periodeCommonsize.length < 2 ? (
                <View style={S.noNarasiBox}><Text style={S.noNarasiText}>Data belum cukup.</Text></View>
            ) : (
                <TrendTableContent periodeData={periodeCommonsize} rows={commonsizeRows} />
            )}
            <NarasiBlock narasi={trendCommonsize?.narasi_trend_commonsize_AI} label="Tren Common-Size" />
            {chartImages?.commonsize && (
                <>
                    <Text style={S.chartLabel}>Grafik Tren Common-Size</Text>
                    <Image src={chartImages.commonsize} style={S.chartImage} />
                </>
            )}

            <Text style={S.sectionTitle}>Tren Arus Kas</Text>
            {periodeArusKas.length < 2 ? (
                <View style={S.noNarasiBox}><Text style={S.noNarasiText}>Data belum cukup.</Text></View>
            ) : (
                <TrendTableContent periodeData={periodeArusKas} rows={arusKasRows} />
            )}
            <NarasiBlock narasi={trendArusKas?.narasi_trend_arus_kas_AI} label="Tren Arus Kas" />
            {chartImages?.arusKas && (
                <>
                    <Text style={S.chartLabel}>Grafik Tren Arus Kas</Text>
                    <Image src={chartImages.arusKas} style={S.chartImage} />
                </>
            )}

            <PageFooter perusahaan={perusahaan.nama} periode={analisis.periode_label} />
        </Page>
    );
}

export function AnalisisPdfDocument({
    perusahaan,
    analisis,
    neraca,
    labaRugi,
    likuiditas,
    profitabilitas,
    solvabilitas,
    aktivitas,
    dupont,
    commonsize,
    trendAkunUtama,
    trendRasio,
    trendDupont,
    trendCommonsize,
    trendArusKas,
    chartImages = {},
}) {
    return (
        <Document
            title={`Analisis Keuangan ${perusahaan?.nama} — ${analisis?.periode_label}`}
            author="Finalysis"
            subject="Laporan Analisis Keuangan"
        >
            {/* 1. Cover */}
            <CoverPage perusahaan={perusahaan} analisis={analisis} />

            {/* 2. Executive Summary — hanya jika ada */}
            {analisis?.ai_summary_insight && (
                <SummaryPage analisis={analisis} perusahaan={perusahaan} />
            )}

            {/* 3. Data Keuangan Dasar */}
            {(neraca || labaRugi) && (
                <DataKeuanganPage
                    neraca={neraca}
                    labaRugi={labaRugi}
                    perusahaan={perusahaan}
                    analisis={analisis}
                />
            )}

            {/* 4. Rasio Keuangan — Hal. 1: Likuiditas + Profitabilitas */}
            {(likuiditas || profitabilitas) && (
                <RasioPage
                    likuiditas={likuiditas}
                    profitabilitas={profitabilitas}
                    perusahaan={perusahaan}
                    analisis={analisis}
                />
            )}

            {/* 4. Rasio Keuangan Hal. 2: Solvabilitas, Aktivitas, DuPont, Commonsize */}
            {(solvabilitas || aktivitas || dupont || commonsize) && (
                <RasioPage2
                    solvabilitas={solvabilitas}
                    aktivitas={aktivitas}
                    dupont={dupont}
                    commonsize={commonsize}
                    perusahaan={perusahaan}
                    analisis={analisis}
                />
            )}

            {/* 5. Analisis Trend — Hal. 1: Akun Utama + Rasio */}
            {(trendAkunUtama || trendRasio) && (
                <TrendPage
                    trendAkunUtama={trendAkunUtama}
                    trendRasio={trendRasio}
                    chartImages={chartImages}
                    perusahaan={perusahaan}
                    analisis={analisis}
                />
            )}

            {/* 5. Analisis Trend — Hal. 2: DuPont, Commonsize, Arus Kas */}
            {(trendDupont || trendCommonsize || trendArusKas) && (
                <TrendPage2
                    trendDupont={trendDupont}
                    trendCommonsize={trendCommonsize}
                    trendArusKas={trendArusKas}
                    chartImages={chartImages}
                    perusahaan={perusahaan}
                    analisis={analisis}
                />
            )}
        </Document>
    );
}
