import { Shield } from 'lucide-react';
import { RatioCardSkeleton } from './RatioCardSkeleton';

export function AnalisisSolvabilitasCard({ onRegenerate }) {
    return (
        <RatioCardSkeleton
            title="Solvabilitas"
            icon={<Shield className="w-5 h-5" />}
            ratioNames={['Debt to Equity', 'Debt to Asset', 'Debt Ratio']}
            iconBgColor="bg-purple-100"
            iconColor="text-purple-600"
            onRegenerate={onRegenerate}
        />
    );
}
