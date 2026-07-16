/**
 * Format angka ke format Rupiah (tanpa simbol).
 * Contoh: 1500000 → "1.500.000"
 */
export const formatAngka = (val) => val !== null && val !== undefined? new Intl.NumberFormat('id-ID').format(val) : '—';

/**
 * Format nilai persentase dari DB.
 * Nilai DB sudah dalam bentuk persentase (misal: 15.5 → "15,50%").
 */
export const formatPersentase = (val) =>
    val !== null && val !== undefined ? `${Number(val).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}%` : '—';

/**
 * Format nilai rasio dengan suffix (default "x").
 * Contoh: 1.5 → "1,50x"
 */
export const formatRasio = (val, suffix = 'x') =>
    val !== null && val !== undefined
        ? `${Number(val).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}${suffix}`
        : '—';

/**
 * Format nilai likuiditas & solvabilitas.
 * Nilai DB disimpan dalam persentase×100 (misal: 150 → dibagi 100 → "1,50x").
 */
export const formatLikuiditasDanSolvabilitas = (val) =>
    val !== null && val !== undefined
        ? `${Number(val / 100).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}x`
        : '—';

/**
 * Format label periode dari object analisis.
 * Contoh: { periode_type: 'quarterly', quarter: 3, tahun: 2024 } → "Q3 2024"
 */
export const formatLabelPeriode = (analisis) => {
    if (!analisis) return '—';
    if (analisis.periode_type === 'annual')    return `${analisis.tahun}`;
    if (analisis.periode_type === 'quarterly') return `Q${analisis.quarter} ${analisis.tahun}`;
    return `Bln ${analisis.bulan} ${analisis.tahun}`;
};

/**
 * Tanggal hari ini dalam format panjang Indonesia.
 * Contoh: "11 Juli 2026"
 */
export const getTanggalSekarang = () =>
    new Date().toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric',
    });
