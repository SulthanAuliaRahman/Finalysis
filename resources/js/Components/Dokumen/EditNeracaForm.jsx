import { Scale } from "lucide-react";
import { EditInputField } from "./EditFormInputs";

export default function EditNeracaForm({ data, onDataChange, disabled }) {
    return (
        <div className="border border-slate-100 rounded-xl p-4 bg-slate-50/40 space-y-4">
            <div className="flex items-center gap-1.5 border-b border-slate-200/60 pb-2 text-slate-800 font-bold text-sm">
                <Scale className="w-4 h-4 text-blue-600" /> Neraca
            </div>
            <div className="grid grid-cols-1 gap-3">
                <EditInputField label="Kas & Setara Kas" section="neraca" fieldKey="cash_equivalent" value={data.neraca.cash_equivalent} onChange={onDataChange} disabled={disabled} />
                <EditInputField label="Persediaan (Inventory)" section="neraca" fieldKey="inventory" value={data.neraca.inventory} onChange={onDataChange} disabled={disabled} />
                <EditInputField label="Aset Lancar" section="neraca" fieldKey="current_assets" value={data.neraca.current_assets} onChange={onDataChange} disabled={disabled} />
                <EditInputField label="Total Aset" section="neraca" fieldKey="total_assets" value={data.neraca.total_assets} onChange={onDataChange} disabled={disabled} />
                <EditInputField label="Liabilitas Jangka Pendek" section="neraca" fieldKey="current_liabilities" value={data.neraca.current_liabilities} onChange={onDataChange} disabled={disabled} />
                <EditInputField label="Total Liabilitas" section="neraca" fieldKey="total_liabilities" value={data.neraca.total_liabilities} onChange={onDataChange} disabled={disabled} />
                <EditInputField label="Total Ekuitas" section="neraca" fieldKey="total_equity" value={data.neraca.total_equity} onChange={onDataChange} disabled={disabled} />
            </div>
        </div>
    );
}
