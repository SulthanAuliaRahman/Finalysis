import { TrendingUp } from "lucide-react";
import { InputFieldWithMetadata } from "./FormInputs";

export default function LabaRugiForm({ data, foundAt, onDataChange, onMetadataChange, disabled }) {
    return (
        <div className="border border-slate-100 rounded-xl p-4 bg-slate-50/40 space-y-4">
            <div className="flex items-center gap-1.5 border-b border-slate-200/60 pb-2 text-slate-800 font-bold text-sm">
                <TrendingUp className="w-4 h-4 text-emerald-600" /> Tabel Laba Rugi
            </div>
            <div className="space-y-3">
                <InputFieldWithMetadata
                    label="Pendapatan Usaha (Revenue)" section="laba_rugi" fieldKey="pendapatan" metadataKey="revenue"
                    value={data.laba_rugi.pendapatan} onChange={onDataChange}
                    metadata={foundAt?.revenue} onMetadataChange={onMetadataChange} disabled={disabled}
                />
                <InputFieldWithMetadata
                    label="Laba Kotor (Gross Profit)" section="laba_rugi" fieldKey="laba_kotor" metadataKey="gross_profit"
                    value={data.laba_rugi.laba_kotor} onChange={onDataChange}
                    metadata={foundAt?.gross_profit} onMetadataChange={onMetadataChange} disabled={disabled}
                />
                <InputFieldWithMetadata
                    label="Laba Bersih (Net Profit)" section="laba_rugi" fieldKey="laba_bersih" metadataKey="net_profit"
                    value={data.laba_rugi.laba_bersih} onChange={onDataChange}
                    metadata={foundAt?.net_profit} onMetadataChange={onMetadataChange} disabled={disabled}
                />
            </div>
        </div>
    );
}
