import { StyleSheet } from '@react-pdf/renderer';

export const pdfStyles = StyleSheet.create({

    // Halaman standar (semua halaman kecuali cover)
    page: {
        fontFamily: 'Helvetica',
        fontSize: 10,
        color: '#000000',
        paddingTop: 40,
        paddingBottom: 50,
        paddingHorizontal: 40,
        backgroundColor: '#ffffff',
    },

    // Cover
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

    // Header per halaman
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

    // Section title
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

    // Tabel
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
        borderBottomColor: '#eeeeee',
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

    // Narasi AI
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

    // Chart image
    chartLabel: {
        fontSize: 9,
        color: '#666666',
        marginBottom: 4,
        fontStyle: 'italic',
    },
    chartImage: {
        width: '80%',
        alignSelf: 'center',
        marginTop: 4,
        marginBottom: 16,
    },

    // Footer
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

    // Executive Summary box
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
