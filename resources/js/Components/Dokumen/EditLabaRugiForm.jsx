import { TrendingUp } from "lucide-react";
import { EditInputField } from "./EditFormInputs";

export default function EditLabaRugiForm({ data, onDataChange, disabled }) {
    return (
        <div className="border border-slate-100 rounded-xl p-4 bg-slate-50/40 space-y-4">
            <div className="flex items-center gap-1.5 border-b border-slate-200/60 pb-2 text-slate-800 font-bold text-sm">
                <TrendingUp className="w-4 h-4 text-emerald-600" /> Laba Rugi
            </div>
            <div className="grid grid-cols-1 gap-3">
                <EditInputField label="Pendapatan" section="laba_rugi" fieldKey="pendapatan" value={data.laba_rugi.pendapatan} onChange={onDataChange} disabled={disabled} />
                <EditInputField label="Laba Kotor" section="laba_rugi" fieldKey="laba_kotor" value={data.laba_rugi.laba_kotor} onChange={onDataChange} disabled={disabled} />
                <EditInputField label="Laba Bersih" section="laba_rugi" fieldKey="laba_bersih" value={data.laba_rugi.laba_bersih} onChange={onDataChange} disabled={disabled} />
            </div>
        </div>
    );
}
