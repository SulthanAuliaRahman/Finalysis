import { Sparkles, RefreshCw } from 'lucide-react';

export function AIInsightCard({ onRegenerate }) {
    return (
        <div className="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-xl p-6">
            <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2.5">
                    <div className="p-2 bg-white rounded-lg shadow-xs">
                        <Sparkles className="w-5 h-5 text-blue-600" />
                    </div>
                    <h3 className="font-semibold text-slate-900">AI Summary & Insight</h3>
                </div>
                <button
                    onClick={onRegenerate}
                    className="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-blue-200 rounded-lg text-blue-700 hover:bg-blue-50 transition-colors text-xs font-medium"
                >
                    <RefreshCw className="w-3.5 h-3.5" />
                    Regenerasi
                </button>
            </div>

            <div className="bg-white/60 border border-dashed border-blue-200 rounded-lg p-5 text-center">
                <p className="text-sm text-slate-400 italic">
                    Ringkasan dan insight AI berdasarkan seluruh rasio keuangan akan tampil di sini
                    setelah analisis dijalankan.
                </p>
            </div>
        </div>
    );
}
