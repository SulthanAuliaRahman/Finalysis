import { Shield } from 'lucide-react';
import { RatioCardBase } from './RatioCardBase';

const formatNum = (val) => new Intl.NumberFormat('id-ID').format(val || 0);

export function AnalisisSolvabilitasCard({ data, neraca, perusahaanId, analisisId }) {
    return (
        <RatioCardBase
            title="Solvabilitas"
            icon={<Shield className="w-5 h-5" />}
            iconBgColor="bg-purple-100"
            iconColor="text-purple-600"
            ratios={[
                {
                    label: 'Debt to Equity (DER)',
                    value: data?.debt_to_equity ?? null, suffix: '%',
                    formula: 'Total Kewajiban / Total Ekuitas',
                    breakdown: neraca ? `${formatNum(neraca.total_liabilities)} / ${formatNum(neraca.total_equity)}` : null
                },
                {
                    label: 'Debt to Asset (DAR)',
                    value: data?.debt_to_asset ?? null, suffix: '%',
                    formula: 'Total Kewajiban / Total Aset',
                    breakdown: neraca ? `${formatNum(neraca.total_liabilities)} / ${formatNum(neraca.total_assets)}` : null
                },
            ]}
            narasi={data?.narasi_solvabilitas_AI}
            section="solvabilitas"
            perusahaanId={perusahaanId}
            analisisId={analisisId}
        />
    );
}
