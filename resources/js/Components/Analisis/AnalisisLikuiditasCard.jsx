import { Droplet } from 'lucide-react';
import { RatioCardSkeleton } from './RatioCardSkeleton';

export function AnalisisLikuiditasCard({ onRegenerate }) {
    return (
        <RatioCardSkeleton
            title="Likuiditas"
            icon={<Droplet className="w-5 h-5" />}
            ratioNames={['Current Ratio', 'Quick Ratio', 'Cash Ratio']}
            iconBgColor="bg-blue-100"
            iconColor="text-blue-600"
            onRegenerate={onRegenerate}
        />
    );
}
