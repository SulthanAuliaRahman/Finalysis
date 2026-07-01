import { TrendingUp } from 'lucide-react';
import { RatioCardSkeleton } from './RatioCardSkeleton';

export function AnalisisProfitabilitasCard({ onRegenerate }) {
    return (
        <RatioCardSkeleton
            title="Profitabilitas"
            icon={<TrendingUp className="w-5 h-5" />}
            ratioNames={['ROE', 'ROA', 'Net Profit Margin', 'Gross Profit Margin']}
            iconBgColor="bg-green-100"
            iconColor="text-green-600"
            onRegenerate={onRegenerate}
        />
    );
}
