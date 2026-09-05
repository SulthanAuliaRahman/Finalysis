import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

function adaSectionYangProcessing(sectionStatus) {
    if (!sectionStatus) return false;
    return Object.values(sectionStatus).some(s => s?.status === 'processing');
}

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

    // sinkron ulang kalau Inertia reload props (misal habis navigasi)
    useEffect(() => {
        setProgress(initial);
    }, [initial.status_generate, initial.section_status]);

    useEffect(() => {
        if (!polling) return;

        const interval = setInterval(async () => {
            try {
                const res = await fetch(`/perusahaan/${perusahaanId}/analisis/${analisisId}/status`);
                const data = await res.json();
                setProgress(data);

                const masihProcessing =
                    data.status_generate === 'processing' || adaSectionYangProcessing(data.section_status);

                if (!masihProcessing) {
                    setPolling(false);
                    clearInterval(interval);
                    router.reload({
                        only: [
                            'analisis',
                            'likuiditas', 'profitabilitas', 'solvabilitas', 'aktivitas',
                            'dupont', 'commonsize',
                            'trendRasio', 'trendDupont', 'trendCommonsize', 'trendAkunUtama',
                        ],
                    });
                }
            } catch (e) {
                console.error('Gagal polling status:', e);
            }
        }, 2000);

        return () => clearInterval(interval);
    }, [polling, perusahaanId, analisisId]);

    return [progress, () => setPolling(true)];
}
