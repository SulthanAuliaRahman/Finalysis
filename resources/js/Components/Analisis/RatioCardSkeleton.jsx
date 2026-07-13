import { RefreshCw } from 'lucide-react';

// ini akan di hapus soal nya kan analisis kemungkinan akan menggunakan grafik berbeda jadi Componen akan berhenti modularitas nya di Analisis Card
//Temporary untuk Layouting
export function RatioCardSkeleton({ title, icon, ratioNames, iconBgColor, iconColor, onRegenerate }) {
    return (
        <div className="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
            <div className="flex items-center justify-between mb-4">
                <div className="flex items-center gap-2.5">
                    <div className={`p-2 rounded-lg ${iconBgColor}`}>
                        <span className={iconColor}>{icon}</span>
                    </div>
                    <h3 className="font-semibold text-slate-900">{title}</h3>
                </div>
                <button
                    onClick={onRegenerate}
                    className="flex items-center gap-1.5 px-2.5 py-1 border border-slate-200 rounded-lg text-slate-500 hover:bg-slate-50 transition-colors text-xs"
                >
                    <RefreshCw className="w-3.5 h-3.5" />
                    Regenerasi
                </button>
            </div>

            <div className="space-y-2.5 mb-4">
                {ratioNames.map((name) => (
                    <div key={name} className="flex items-center justify-between py-1.5 border-b border-slate-50 last:border-0">
                        <span className="text-sm text-slate-600">{name}</span>
                        <span className="text-sm font-medium text-slate-300">—</span>
                    </div>
                ))}
            </div>

            <div className="h-32 rounded-lg bg-slate-50 border border-dashed border-slate-200 flex items-center justify-center mb-4">
                <span className="text-xs text-slate-400">Grafik tren akan tampil di sini</span>
            </div>

            <div className="bg-slate-50/70 border border-slate-100 rounded-lg p-3">
                <p className="text-xs text-slate-400 italic">
                    Narasi AI untuk rasio {title.toLowerCase()} akan muncul di sini setelah analisis dijalankan.
                </p>
            </div>
        </div>
    );
}
