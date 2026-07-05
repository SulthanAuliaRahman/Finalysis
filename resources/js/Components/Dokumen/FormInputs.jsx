// resources/js/Components/Dokumen/FormInputs.jsx

import { MapPin } from "lucide-react";

export function InputFieldWithMetadata({ label, section, fieldKey, metadataKey, value, onChange, metadata, onMetadataChange, disabled }) {
    const safeMetadata = metadata || {};
    const hasMetadata = !!metadata;

    return (
        <div className="space-y-1 bg-white border border-slate-100 rounded-lg p-3 shadow-2xs">
            <label className="text-xs font-semibold text-slate-700 block">{label}</label>

            {/* Input Finansial Utama (Tetap pakai disabled untuk mencegah ketik saat form di-submit) */}
            <input
                type="number"
                value={value}
                onChange={e => onChange(section, fieldKey, e.target.value)}
                className="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 font-mono bg-white text-slate-900"
                disabled={disabled}
            />

            <div className="mt-2 pt-2 border-t border-dashed border-slate-200 flex flex-col gap-1.5 text-[10px] text-slate-600">
                <div className="flex items-center gap-1 font-medium text-blue-600 mb-0.5">
                    <MapPin className="w-2.5 h-2.5" />
                    {hasMetadata ? "Metadata PDF (Terdeteksi):" : "Metadata PDF (Manual Entri):"}
                </div>

                <div className="flex items-center gap-2">
                    <span className="w-16">Halaman:</span>
                    <input
                        type="number"
                        value={safeMetadata.page || ''}
                        onChange={e => onMetadataChange(metadataKey, 'page', e.target.value === '' ? '' : parseInt(e.target.value))}
                        className="px-2 py-1 text-[10px] border border-slate-200 rounded w-16 focus:outline-none focus:border-blue-400"
                        placeholder="Contoh: 3"
                        /* Dihapus: disabled={disabled} */
                    />
                </div>

                <div className="flex items-center gap-2">
                    <span className="w-16">Label PDF:</span>
                    <input
                        type="text"
                        value={safeMetadata.label_in_pdf || ''}
                        onChange={e => onMetadataChange(metadataKey, 'label_in_pdf', e.target.value)}
                        className="px-2 py-1 text-[10px] border border-slate-200 rounded flex-grow focus:outline-none focus:border-blue-400 font-italic"
                        placeholder="Contoh: Total Aset"
                        /* Dihapus: disabled={disabled} */
                    />
                </div>

                {hasMetadata && safeMetadata.all_numbers_on_row && (
                    <div className="truncate mt-0.5">
                        <span className="w-16 inline-block">Angka Asli:</span>
                        <span className="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 font-medium">
                            {safeMetadata.all_numbers_on_row?.join(" | ") || "-"}
                        </span>
                    </div>
                )}
            </div>
        </div>
    );
}

export function CashFlowInputWithBreakdown({ label, fieldKey, metadataKey, value, onChange, metadata, onMetadataChange, foundAt, disabled }) {
    const safeMetadata = metadata || {};
    const hasMetadata = !!metadata;

    const componentKeys = [
        "cash_flow_from_operations",
        "cash_flow_from_investing",
        "cash_flow_from_financing",
    ];

    const relevantComponents = componentKeys
        .map((key) => {
            const componentMetadata = foundAt?.[key];
            if (!componentMetadata || typeof componentMetadata.page !== "number") return null;

            const rawValue = parseFloat(
                String(componentMetadata.raw_number || "0").replace(/[(),]/g, m => (m === "(" ? "-" : ""))
            );
            const isRelevant = fieldKey === "kas_masuk" ? rawValue >= 0 : rawValue < 0;
            if (!isRelevant) return null;

            const componentLabel = componentMetadata.label_in_pdf || key;

            return { key, label: componentLabel, metadata: componentMetadata };
        })
        .filter(Boolean);

    return (
        <div className="space-y-1 bg-white border border-slate-100 rounded-lg p-3 shadow-2xs">
            <label className="text-xs font-semibold text-slate-700 block">{label}</label>

            {/* Input Finansial Utama */}
            <input
                type="number"
                value={value}
                onChange={e => onChange("arus_kas", fieldKey, e.target.value)}
                className="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 font-mono bg-white text-slate-900"
                disabled={disabled}
            />

            <div className="mt-2 pt-2 border-t border-dashed border-slate-200 flex flex-col gap-1.5 text-[10px] text-slate-600">
                <div className="flex items-center gap-1 font-medium text-blue-600 mb-0.5">
                    <MapPin className="w-2.5 h-2.5" />
                    {hasMetadata ? "Metadata PDF (Terdeteksi):" : "Metadata PDF (Manual Entri):"}
                </div>

                <div className="flex items-center gap-2">
                    <span className="w-16">Halaman:</span>
                    <input
                        type="number"
                        value={safeMetadata.page || ''}
                        onChange={e => onMetadataChange(metadataKey, 'page', e.target.value === '' ? '' : parseInt(e.target.value))}
                        className="px-2 py-1 text-[10px] border border-slate-200 rounded w-16 focus:outline-none focus:border-blue-400"
                        placeholder="Contoh: 7"
                    />
                </div>

                <div className="mt-1 bg-slate-50 p-2 rounded text-[9px]">
                    <div className="text-slate-500 font-medium mb-1">Cara Hitung</div>
                    {relevantComponents.length > 0 ? (
                        relevantComponents.map(({ key, label: componentLabel, metadata: cMetadata }) => (
                            <div key={key} className="flex items-center justify-between gap-2">
                                <span className="truncate">{componentLabel} (p.{cMetadata.page}):</span>
                                <span className="font-mono bg-white border border-slate-100 px-1 py-0.5 rounded text-slate-700 font-medium shrink-0">
                                    {cMetadata.raw_number || "-"}
                                </span>
                            </div>
                        ))
                    ) : (
                        <div className="italic text-slate-400">• Tidak ada komponen otomatis terdeteksi</div>
                    )}
                </div>
            </div>
        </div>
    );
}
