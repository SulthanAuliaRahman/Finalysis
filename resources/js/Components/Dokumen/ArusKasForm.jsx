import { Wallet } from "lucide-react";
import { CashFlowInputWithBreakdown } from "./FormInputs";

export default function ArusKasForm({ data, foundAt, onDataChange, onMetadataChange, disabled }) {
    return (
        <div className="border border-slate-100 rounded-xl p-4 bg-slate-50/40 space-y-4">
            <div className="flex items-center gap-1.5 border-b border-slate-200/60 pb-2 text-slate-800 font-bold text-sm">
                <Wallet className="w-4 h-4 text-indigo-600" /> Tabel Arus Kas
            </div>
            <div className="space-y-3">
                <CashFlowInputWithBreakdown
                    label="Kas Masuk (Cash Inflow)" fieldKey="kas_masuk" metadataKey="kas_masuk"
                    value={data.arus_kas.kas_masuk} onChange={onDataChange}
                    metadata={foundAt?.kas_masuk} onMetadataChange={onMetadataChange}
                    foundAt={foundAt} disabled={disabled}
                />
                <CashFlowInputWithBreakdown
                    label="Kas Keluar (Cash Outflow)" fieldKey="kas_keluar" metadataKey="kas_keluar"
                    value={data.arus_kas.kas_keluar} onChange={onDataChange}
                    metadata={foundAt?.kas_keluar} onMetadataChange={onMetadataChange}
                    foundAt={foundAt} disabled={disabled}
                />
            </div>
        </div>
    );
}
