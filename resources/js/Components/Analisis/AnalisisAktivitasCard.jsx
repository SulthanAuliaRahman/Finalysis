import { Activity } from 'lucide-react';
import { RatioCardSkeleton } from './RatioCardSkeleton';

export function AnalisisAktivitasCard({ onRegenerate }) {
    return (
        <RatioCardSkeleton
            title="Aktivitas"
            icon={<Activity className="w-5 h-5" />}
            ratioNames={['Asset Turnover', 'Inventory Turnover', 'Receivable Turnover']}
            iconBgColor="bg-orange-100"
            iconColor="text-orange-600"
            onRegenerate={onRegenerate}
        />
    );
}
