import { Document }                 from '@react-pdf/renderer';
import { CoverPage }                from './PDF/CoverPage';
import { ExecutiveSummaryPage }     from './PDF/ExecutiveSummaryPage';
import { DataKeuanganDasarPage }    from './PDF/DataKeuanganDasarPage';
import { RasioLikuiditasPage }      from './PDF/RasioLikuiditasPage';
import { RasioProfitabilitasPage }  from './PDF/RasioProfitabilitasPage';
import { RasioSolvabilitasPage }    from './PDF/RasioSolvabilitasPage';
import { RasioAktivitasPage }       from './PDF/RasioAktivitasPage';
import { AnalisisDupontPage }       from './PDF/AnalisisDupontPage';
import { AnalisisCommonsizePage }   from './PDF/AnalisisCommonsizePage';
import { TrendAkunUtamaPage }       from './PDF/TrendAkunUtamaPage';
import { TrendRasioPage }           from './PDF/TrendRasioPage';
import { TrendDupontPage }          from './PDF/TrendDupontPage';
import { TrendCommonsizePage }      from './PDF/TrendCommonsizePage';
import { TrendArusKasPage }         from './PDF/TrendArusKasPage';

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
            <CoverPage
                perusahaan={perusahaan}
                analisis={analisis}
            />

            {/* 2. Executive Summary */}
            {analisis?.ai_summary_insight && (
                <ExecutiveSummaryPage
                    perusahaan={perusahaan}
                    analisis={analisis}
                />
            )}

            {/* 3. Data Keuangan Dasar */}
            {(neraca || labaRugi) && (
                <DataKeuanganDasarPage
                    perusahaan={perusahaan}
                    analisis={analisis}
                    neraca={neraca}
                    labaRugi={labaRugi}
                />
            )}

            {/* 4. Rasio Likuiditas */}
            {likuiditas && (
                <RasioLikuiditasPage
                    perusahaan={perusahaan}
                    analisis={analisis}
                    likuiditas={likuiditas}
                    chartImageBase64={chartImages.likuiditas}
                />
            )}

            {/* 5. Rasio Profitabilitas */}
            {profitabilitas && (
                <RasioProfitabilitasPage
                    perusahaan={perusahaan}
                    analisis={analisis}
                    profitabilitas={profitabilitas}
                    chartImageBase64={chartImages.profitabilitas}
                />
            )}

            {/* 6. Rasio Solvabilitas */}
            {solvabilitas && (
                <RasioSolvabilitasPage
                    perusahaan={perusahaan}
                    analisis={analisis}
                    solvabilitas={solvabilitas}
                    chartImageBase64={chartImages.solvabilitas}
                />
            )}

            {/* 7. Rasio Aktivitas */}
            {aktivitas && (
                <RasioAktivitasPage
                    perusahaan={perusahaan}
                    analisis={analisis}
                    aktivitas={aktivitas}
                    chartImageBase64={chartImages.aktivitas}
                />
            )}

            {/* 8. Analisis DuPont */}
            {dupont && (
                <AnalisisDupontPage
                    perusahaan={perusahaan}
                    analisis={analisis}
                    dupont={dupont}
                />
            )}

            {/* 9. Analisis Common-Size */}
            {commonsize && (
                <AnalisisCommonsizePage
                    perusahaan={perusahaan}
                    analisis={analisis}
                    commonsize={commonsize}
                />
            )}

            {/* 10. Tren Akun Utama */}
            {trendAkunUtama && (
                <TrendAkunUtamaPage
                    perusahaan={perusahaan}
                    analisis={analisis}
                    trendAkunUtama={trendAkunUtama}
                    chartImageBase64={chartImages.akunUtama}
                />
            )}

            {/* 11. Tren Rasio Keuangan */}
            {trendRasio && (
                <TrendRasioPage
                    perusahaan={perusahaan}
                    analisis={analisis}
                    trendRasio={trendRasio}
                    chartImageBase64={chartImages.rasio}
                />
            )}

            {/* 12. Tren DuPont */}
            {trendDupont && (
                <TrendDupontPage
                    perusahaan={perusahaan}
                    analisis={analisis}
                    trendDupont={trendDupont}
                    chartImageBase64={chartImages.dupont}
                />
            )}

            {/* 13. Tren Common-Size */}
            {trendCommonsize && (
                <TrendCommonsizePage
                    perusahaan={perusahaan}
                    analisis={analisis}
                    trendCommonsize={trendCommonsize}
                    chartImageBase64={chartImages.commonsize}
                />
            )}

            {/* 14. Tren Arus Kas */}
            {trendArusKas && (
                <TrendArusKasPage
                    perusahaan={perusahaan}
                    analisis={analisis}
                    trendArusKas={trendArusKas}
                    chartImageBase64={chartImages.arusKas}
                />
            )}
        </Document>
    );
}
