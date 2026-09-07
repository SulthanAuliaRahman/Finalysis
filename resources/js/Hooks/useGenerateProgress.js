import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

function adaSectionYangProcessing(sectionStatus) {
    if (!sectionStatus) return false;
    return Object.values(sectionStatus).some(s => s?.status === 'processing');
}

// section key (dipakai di cardProps) -> nama prop Inertia yang perlu di-reload saat section itu selesai
const SECTION_PROP_MAP = {
    likuiditas: ['likuiditas'],
    profitabilitas: ['profitabilitas'],
    solvabilitas: ['solvabilitas'],
    aktivitas: ['aktivitas'],
    dupont: ['dupont'],
    commonsize: ['commonsize'],
    trend_akun_utama: ['trendAkunUtama'],
    trend_rasio: ['trendRasio'],
    trend_dupont: ['trendDupont'],
    trend_commonsize: ['trendCommonsize'],
    summary: [], // narasi ai_summary_insight ada di dalam prop `analisis`, sudah selalu ikut di-reload
};

const ALL_PROPS = [
    'analisis',
    'likuiditas', 'profitabilitas', 'solvabilitas', 'aktivitas',
    'dupont', 'commonsize',
    'trendRasio', 'trendDupont', 'trendCommonsize', 'trendAkunUtama',
];

/**
 * Polling status generate (keseluruhan ATAU per-section) tiap 2 detik.
 * `startPolling()` dipanggil manual setelah dispatch regenerasi 1 section,
 * supaya polling langsung mulai walau server belum sempat update section_status.
 */
export function useGenerateProgress(perusahaanId, analisisId, initial) {
    const [progress, setProgress] = useState(initial);
    const [polling, setPolling] = useState(
        initial.status_generate === 'processing' || adaSectionYangProcessing(initial.section_status)
    );

    // dipakai untuk mendeteksi section mana yang BARU SAJA selesai di antara dua poll
    const prevSectionStatusRef = useRef(initial.section_status);

    // sinkron ulang kalau Inertia reload props (misal habis navigasi)
    useEffect(() => {
        setProgress(initial);
        prevSectionStatusRef.current = initial.section_status;
    }, [initial.status_generate, initial.section_status]);

    useEffect(() => {
        if (!polling) return;

        const interval = setInterval(async () => {
            try {
                const res = await fetch(`/perusahaan/${perusahaanId}/analisis/${analisisId}/status`);
                const data = await res.json();

                const prevStatus = prevSectionStatusRef.current;
                const masihProcessing =
                    data.status_generate === 'processing' || adaSectionYangProcessing(data.section_status);

                if (!masihProcessing) {
                    // semua selesai -> satu kali full reload, cukup sebagai safety net
                    prevSectionStatusRef.current = data.section_status;
                    setProgress(data);
                    setPolling(false);
                    clearInterval(interval);
                    router.reload({ only: ALL_PROPS });
                    return;
                }

                // masih ada yang processing -> cek section mana yang BARU SAJA pindah
                // dari processing ke status akhir (selesai/gagal/tidak_berlaku), lalu
                // partial-reload prop-nya saja supaya narasinya langsung muncul.
                const propsToReload = new Set();
                Object.entries(data.section_status || {}).forEach(([section, s]) => {
                    const prevWasProcessing = prevStatus?.[section]?.status === 'processing';
                    const nowDone = s?.status && s.status !== 'processing';
                    if (prevWasProcessing && nowDone) {
                        (SECTION_PROP_MAP[section] || []).forEach(p => propsToReload.add(p));
                        propsToReload.add('analisis'); // section_status & progress ikut prop `analisis`
                    }
                });

                prevSectionStatusRef.current = data.section_status;
                setProgress(data);

                if (propsToReload.size > 0) {
                    router.reload({ only: Array.from(propsToReload) });
                }
            } catch (e) {
                console.error('Gagal polling status:', e);
            }
        }, 2000);

        return () => clearInterval(interval);
    }, [polling, perusahaanId, analisisId]);

    return [progress, () => setPolling(true)];
}
