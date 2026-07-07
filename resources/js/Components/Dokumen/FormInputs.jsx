import { MapPin } from "lucide-react";

const formatRibuan = (value) => {
    if (value === null || value === undefined || value === '') return '';

    // Hapus semua karakter selain angka dan tanda minus
    const stringValue = value.toString().replace(/[^\d-]/g, '');
    if (stringValue === '' || stringValue === '-') return stringValue;

    // Format dengan pemisah ribuan titik
    const isNegative = stringValue.startsWith('-');
    const absoluteValue = stringValue.replace('-', '');
    const formatted = absoluteValue.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

    return isNegative ? `-${formatted}` : formatted;
};

const parseRibuan = (value) => {
    if (!value) return '';
    // Kembalikan ke angka murni (tanpa titik) biar bisa masuk db juga
    return value.toString().replace(/\./g, '');
};

export function InputFieldWithMetadata({ label, section, fieldKey, metadataKey, value, onChange, metadata, onMetadataChange, disabled }) {
    const safeMetadata = metadata || {};
    const hasMetadata = !!metadata;

    return (
        <div className="space-y-1.5 bg-white border border-slate-100 rounded-lg p-3.5 shadow-sm hover:border-slate-200 transition-colors">
            <label className="text-xs font-bold text-slate-700 block tracking-wide">{label}</label>

            {/* Input Finansial Utama dengan Prefix "Rp" */}
            <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span className="text-slate-400 text-sm font-semibold">$</span>
                </div>
                <input
                    type="text" // Diubah ke text agar bisa menampilkan titik
                    value={formatRibuan(value)}
                    onChange={e => {
                        const rawValue = parseRibuan(e.target.value);
                        onChange(section, fieldKey, rawValue);
                    }}
                    className="w-full pl-9 pr-3 py-1.5 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono bg-white text-slate-900 transition-all disabled:bg-slate-50 disabled:text-slate-500"
                    disabled={disabled}
                    placeholder="0"
                />
            </div>

            <div className="mt-2 pt-2 border-t border-dashed border-slate-200 flex flex-col gap-2 text-[10px] text-slate-600">
                <div className="flex items-center gap-1 font-semibold text-blue-600 mb-0.5">
                    <MapPin className="w-3 h-3" />
                    Metadata PDF
                </div>

                <div className="grid grid-cols-2 gap-2">
                    <div className="flex items-center gap-2">
                        <span className="w-16 font-medium text-slate-500">Halaman:</span>
                        <input
                            type="number"
                            value={safeMetadata.page || ''}
                            onChange={e => onMetadataChange(metadataKey, 'page', e.target.value === '' ? '' : parseInt(e.target.value))}
                            className="px-2 py-1 text-[10px] border border-slate-200 rounded w-full max-w-[4rem] focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
                            placeholder="Ex: 3"
                        />
                    </div>

                    <div className="flex items-center gap-2">
                        <span className="w-auto font-medium text-slate-500">Label:</span>
                        <input
                            type="text"
                            value={safeMetadata.label_in_pdf || ''}
                            onChange={e => onMetadataChange(metadataKey, 'label_in_pdf', e.target.value)}
                            className="px-2 py-1 text-[10px] border border-slate-200 rounded w-full focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 italic"
                            placeholder="Total Aset"
                        />
                    </div>
                </div>

                {hasMetadata && safeMetadata.all_numbers_on_row && (
                    <div className="truncate mt-1 bg-slate-50 p-1.5 rounded flex items-center gap-2 border border-slate-100">
                        <span className="w-16 inline-block font-medium text-slate-500">Angka Asli:</span>
                        <span className="font-mono text-slate-700 font-semibold tracking-tight">
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
        <div className="space-y-1.5 bg-white border border-slate-100 rounded-lg p-3.5 shadow-sm hover:border-slate-200 transition-colors">
            <label className="text-xs font-bold text-slate-700 block tracking-wide">{label}</label>

            <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span className="text-slate-400 text-sm font-semibold">$</span>
                </div>
                <input
                    type="text"
                    value={formatRibuan(value)}
                    onChange={e => {
                        const rawValue = parseRibuan(e.target.value);
                        onChange("arus_kas", fieldKey, rawValue);
                    }}
                    className="w-full pl-9 pr-3 py-1.5 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono bg-white text-slate-900 transition-all disabled:bg-slate-50 disabled:text-slate-500"
                    disabled={disabled}
                    placeholder="0"
                />
            </div>

            <div className="mt-2 pt-2 border-t border-dashed border-slate-200 flex flex-col gap-2 text-[10px] text-slate-600">
                <div className="flex items-center gap-1 font-semibold text-blue-600 mb-0.5">
                    <MapPin className="w-3 h-3" />
                    Metadata PDF
                </div>

                <div className="flex items-center gap-2">
                    <span className="w-16 font-medium text-slate-500">Halaman:</span>
                    <input
                        type="number"
                        value={safeMetadata.page || ''}
                        onChange={e => onMetadataChange(metadataKey, 'page', e.target.value === '' ? '' : parseInt(e.target.value))}
                        className="px-2 py-1 text-[10px] border border-slate-200 rounded w-16 focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
                        placeholder="Ex: 7"
                    />
                </div>

                <div className="mt-1 bg-slate-50 p-2.5 rounded-md border border-slate-100 text-[10px]">
                    <div className="text-slate-500 font-semibold mb-1.5 border-b border-slate-200 pb-1">Cara Hitung</div>
                    {relevantComponents.length > 0 ? (
                        <div className="space-y-1.5">
                            {relevantComponents.map(({ key, label: componentLabel, metadata: cMetadata }) => (
                                <div key={key} className="flex items-center justify-between gap-2">
                                    <span className="truncate flex-1 text-slate-600">{componentLabel} (Hal.{cMetadata.page})</span>
                                    <span className="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded text-slate-800 font-semibold shrink-0 shadow-sm">
                                        {cMetadata.raw_number || "-"}
                                    </span>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="italic text-slate-400 flex items-center gap-1">
                            <span className="w-1 h-1 bg-slate-300 rounded-full inline-block"></span>
                            Tidak ada komponen otomatis terdeteksi
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
