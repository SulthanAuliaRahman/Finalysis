// resources/js/Components/Dokumen/EditArusKasForm.jsx
import { useState } from "react";
import { Wallet, ChevronDown, ChevronUp } from "lucide-react";
import { EditInputField, EditCashFlowComponentInput } from "./EditFormInputs";

export default function EditArusKasForm({ data, onDataChange, onCashFlowComponentChange, disabled }) {
    const hasExistingComponents =
        data.arus_kas.cash_flow_from_operations !== null ||
        data.arus_kas.cash_flow_from_investing  !== null ||
        data.arus_kas.cash_flow_from_financing  !== null;

    const [showComponents, setShowComponents] = useState(hasExistingComponents);

    return (
        <div className="border border-slate-100 rounded-xl p-4 bg-slate-50/40 space-y-4">
            <div className="flex items-center justify-between border-b border-slate-200/60 pb-2">
                <div className="flex items-center gap-1.5 text-slate-800 font-bold text-sm">
                    <Wallet className="w-4 h-4 text-indigo-600" /> Arus Kas
                </div>
                <button
                    type="button"
                    onClick={() => setShowComponents(prev => !prev)}
                    disabled={disabled}
                    className="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-md border transition-colors
                        border-indigo-200 text-indigo-700 bg-indigo-50 hover:bg-indigo-100 disabled:opacity-50"
                >
                    {showComponents ? <ChevronUp className="w-3.5 h-3.5" /> : <ChevronDown className="w-3.5 h-3.5" />}
                    {showComponents ? "Sembunyikan Komponen" : "Gunakan Komponen Detail (CFO/CFI/CFF)"}
                </button>
            </div>

            {showComponents && (
                <div className="space-y-3 pb-3 border-b border-dashed border-slate-200">
                    <p className="text-[10px] text-slate-500 italic">
                        Kas masuk & keluar dikalkulasi otomatis dari komponen di bawah. Anda tetap bisa ubah manual di bawah.
                    </p>
                    <EditCashFlowComponentInput
                        label="Arus Kas Operasi (CFO)"
                        fieldKey="cash_flow_from_operations"
                        value={data.arus_kas.cash_flow_from_operations}
                        onChange={onCashFlowComponentChange}
                        disabled={disabled}
                    />
                    <EditCashFlowComponentInput
                        label="Arus Kas Investasi (CFI)"
                        fieldKey="cash_flow_from_investing"
                        value={data.arus_kas.cash_flow_from_investing}
                        onChange={onCashFlowComponentChange}
                        disabled={disabled}
                    />
                    <EditCashFlowComponentInput
                        label="Arus Kas Pendanaan (CFF)"
                        fieldKey="cash_flow_from_financing"
                        value={data.arus_kas.cash_flow_from_financing}
                        onChange={onCashFlowComponentChange}
                        disabled={disabled}
                    />
                </div>
            )}

            <div className="grid grid-cols-1 gap-3">
                <EditInputField label="Kas Masuk (Cash Inflow)" section="arus_kas" fieldKey="kas_masuk" value={data.arus_kas.kas_masuk} onChange={onDataChange} disabled={disabled} />
                <EditInputField label="Kas Keluar (Cash Outflow)" section="arus_kas" fieldKey="kas_keluar" value={data.arus_kas.kas_keluar} onChange={onDataChange} disabled={disabled} />
            </div>
        </div>
    );
}
