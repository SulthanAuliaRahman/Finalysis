import { Scale } from "lucide-react";
import { InputFieldWithMetadata } from "./FormInputs";

export default function NeracaForm({ data, foundAt, onDataChange, onMetadataChange, disabled }) {
    return (
        <div className="border border-slate-100 rounded-xl p-4 bg-slate-50/40 space-y-4">
            <div className="flex items-center gap-1.5 border-b border-slate-200/60 pb-2 text-slate-800 font-bold text-sm">
                <Scale className="w-4 h-4 text-blue-600" /> Tabel Neraca
            </div>
            <div className="space-y-3">
                <InputFieldWithMetadata
                    label="Kas (Cash Equivalent)" section="neraca" fieldKey="cash_equivalent" metadataKey="cash_equivalent"
                    value={data.neraca.cash_equivalent} onChange={onDataChange}
                    metadata={foundAt?.cash_equivalent} onMetadataChange={onMetadataChange} disabled={disabled}
                />
                <InputFieldWithMetadata
                    label="Persediaan (inventory)" section="neraca" fieldKey="inventory" metadataKey="inventory"
                    value={data.neraca.inventory} onChange={onDataChange}
                    metadata={foundAt?.inventory} onMetadataChange={onMetadataChange} disabled={disabled}
                />
                <InputFieldWithMetadata
                    label="Aset Lancar (Current Assets)" section="neraca" fieldKey="current_assets" metadataKey="current_assets"
                    value={data.neraca.current_assets} onChange={onDataChange}
                    metadata={foundAt?.current_assets} onMetadataChange={onMetadataChange} disabled={disabled}
                />
                <InputFieldWithMetadata
                    label="Total Aset (Total Assets)" section="neraca" fieldKey="total_assets" metadataKey="total_assets"
                    value={data.neraca.total_assets} onChange={onDataChange}
                    metadata={foundAt?.total_assets} onMetadataChange={onMetadataChange} disabled={disabled}
                />
                <InputFieldWithMetadata
                    label="Liabilitas Pendek (Current Liabilities)" section="neraca" fieldKey="current_liabilities" metadataKey="current_liabilities"
                    value={data.neraca.current_liabilities} onChange={onDataChange}
                    metadata={foundAt?.current_liabilities} onMetadataChange={onMetadataChange} disabled={disabled}
                />
                <InputFieldWithMetadata
                    label="Total Liabilitas (Total Liabilities)" section="neraca" fieldKey="total_liabilities" metadataKey="total_liabilities"
                    value={data.neraca.total_liabilities} onChange={onDataChange}
                    metadata={foundAt?.total_liabilities} onMetadataChange={onMetadataChange} disabled={disabled}
                />
                <InputFieldWithMetadata
                    label="Total Ekuitas (Total Equity)" section="neraca" fieldKey="total_equity" metadataKey="total_equity"
                    value={data.neraca.total_equity} onChange={onDataChange}
                    metadata={foundAt?.total_equity} onMetadataChange={onMetadataChange} disabled={disabled}
                />
            </div>
        </div>
    );
}
