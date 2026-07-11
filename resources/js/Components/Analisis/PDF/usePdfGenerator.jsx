import { useState, useCallback } from 'react';
import { pdf } from '@react-pdf/renderer';
import { AnalisisPdfDocument } from './AnalisisPdfDocument';

async function captureElement(ref) {
    if (!ref?.current) return null;
    try {
        const html2canvas = (await import('html2canvas')).default;
        const canvas = await html2canvas(ref.current, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false,
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
            const [rasioImg, dupontImg, commonsizeImg, akunUtamaImg, arusKasImg] = await Promise.all([
                captureElement(chartRefs.rasio),
                captureElement(chartRefs.dupont),
                captureElement(chartRefs.commonsize),
                captureElement(chartRefs.akunUtama),
                captureElement(chartRefs.arusKas),
            ]);

            const chartImages = {
                rasio:      rasioImg,
                dupont:     dupontImg,
                commonsize: commonsizeImg,
                akunUtama:  akunUtamaImg,
                arusKas:    arusKasImg,
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
