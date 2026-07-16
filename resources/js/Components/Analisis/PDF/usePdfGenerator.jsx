import { useState, useCallback } from 'react';
import { pdf } from '@react-pdf/renderer';
import { AnalisisPdfDocument } from '../AnalisisPdfDocument.jsx';

async function captureElement(ref) {
    if (!ref?.current) return null;
    try {
        const html2canvas = (await import('html2canvas')).default;
        const canvas = await html2canvas(ref.current, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false,
            x: -8,
            y: -8,
            width:  ref.current.offsetWidth  + 16,
            height: ref.current.offsetHeight + 16,
        });
        return canvas.toDataURL('image/png');
    } catch (e) {
        console.error('html2canvas capture gagal:', e);
        return null;
    }
}

export function usePdfGenerator({ pdfProps, chartRefs }) {
    const [isGenerating, setIsGenerating] = useState(false);

    const generatePdf = useCallback(async () => {
        setIsGenerating(true);
        try {
            const [
                likuiditasImg,
                profitabilitasImg,
                solvabilitasImg,
                aktivitasImg,
                dupontImg,
                commonsizeImg,
                trendRasioImg,
                trendDupontImg,
                trendCommonsizeImg,
                trendArusKasImg,
            ] = await Promise.all([
                captureElement(chartRefs.likuiditas),
                captureElement(chartRefs.profitabilitas),
                captureElement(chartRefs.solvabilitas),
                captureElement(chartRefs.aktivitas),
                captureElement(chartRefs.dupont),
                captureElement(chartRefs.commonsize),
                captureElement(chartRefs.trendRasio),
                captureElement(chartRefs.trendDupont),
                captureElement(chartRefs.trendCommonsize),
                captureElement(chartRefs.trendArusKas),
            ]);

            // Mapping yang konsisten dengan kebutuhan AnalisisPdfDocument
            const chartImages = {
                likuiditas:      likuiditasImg,
                profitabilitas:  profitabilitasImg,
                solvabilitas:    solvabilitasImg,
                aktivitas:       aktivitasImg,
                dupont:          dupontImg,
                commonsize:      commonsizeImg,
                trendRasio:      trendRasioImg,
                trendDupont:     trendDupontImg,
                trendCommonsize: trendCommonsizeImg,
                trendArusKas:    trendArusKasImg,
            };

            const doc = <AnalisisPdfDocument {...pdfProps} chartImages={chartImages} />;
            const blob = await pdf(doc).toBlob();

            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = pdfProps.fileName;
            link.click();

            setTimeout(() => URL.revokeObjectURL(url), 5000);
        } catch (err) {
            console.error('Generate PDF gagal:', err);
        } finally {
            setIsGenerating(false);
        }
    }, [chartRefs, pdfProps]);

    return { isGenerating, generatePdf };
}
