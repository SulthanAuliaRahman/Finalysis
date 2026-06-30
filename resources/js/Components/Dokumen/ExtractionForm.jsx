import { Scale, TrendingUp, Wallet, MapPin } from "lucide-react";

// Sub-Komponen Internal untuk Input Field beserta data Found At (Selalu Terbuka untuk Manual Entri)
function InputFieldWithMeta({ label, section, fieldKey, metaKey, value, onChange, meta, onMetaChange, disabled }) {
    const safeMeta = meta || {};
    const hasMeta = !!meta;

    return (
        <div className="space-y-1 bg-white border border-slate-100 rounded-lg p-3 shadow-2xs">
            <label className="text-xs font-semibold text-slate-700 block">{label}</label>

            {/* Input Angka Finansial */}
            <input
                type="number"
                value={value}
                onChange={e => onChange(section, fieldKey, e.target.value)}
                className="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 font-mono bg-white text-slate-900"
                disabled={disabled}
            />

            {/* Panel Metadata Found At (Selalu Muncul untuk Verifikasi & Manual Entri) */}
            <div className="mt-2 pt-2 border-t border-dashed border-slate-200 flex flex-col gap-1.5 text-[10px] text-slate-600">
                <div className="flex items-center gap-1 font-medium text-blue-600 mb-0.5">
                    <MapPin className="w-2.5 h-2.5" />
                    {hasMeta ? "Metadata PDF (Terdeteksi):" : "Metadata PDF (Manual Entri):"}
                </div>

                <div className="flex items-center gap-2">
                    <span className="w-16">Halaman:</span>
                    <input
                        type="number"
                        value={safeMeta.page || ''}
                        onChange={e => onMetaChange(metaKey, 'page', e.target.value === '' ? '' : parseInt(e.target.value))}
                        className="px-2 py-1 text-[10px] border border-slate-200 rounded w-16 focus:outline-none focus:border-blue-400"
                        disabled={disabled}
                        placeholder="Contoh: 3"
                    />
                </div>

                <div className="flex items-center gap-2">
                    <span className="w-16">Label PDF:</span>
                    <input
                        type="text"
                        value={safeMeta.label_in_pdf || ''}
                        onChange={e => onMetaChange(metaKey, 'label_in_pdf', e.target.value)}
                        className="px-2 py-1 text-[10px] border border-slate-200 rounded flex-grow focus:outline-none focus:border-blue-400 font-italic"
                        disabled={disabled}
                        placeholder="Contoh: Total Aset"
                    />
                </div>

                {hasMeta && safeMeta.all_numbers_on_row && (
                    <div className="truncate mt-0.5">
                        <span className="w-16 inline-block">Angka Asli:</span>
                        <span className="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 font-medium">
                            {safeMeta.all_numbers_on_row?.join(" | ") || "-"}
                        </span>
                    </div>
                )}
            </div>
        </div>
    );
}

// Sub-Komponen Arus Kas.
// Tidak ada input "Label PDF" terpisah di sini — label sudah otomatis
// ditampilkan per komponen pada breakdown "Cara Hitung" di bawah,
// diambil langsung dari label_in_pdf hasil ekstraksi masing-masing
// komponen (operasi/investasi/pendanaan), bukan teks hardcode.
function CashFlowInputWithBreakdown({ label, fieldKey, metaKey, value, onChange, meta, onMetaChange, breakdown, disabled }) {
    const safeMeta = meta || {};
    const hasMeta = !!meta;

    const componentKeys = [
        "cash_flow_from_operations",
        "cash_flow_from_investing",
        "cash_flow_from_financing",
    ];

    const relevantComponents = componentKeys
        .map((key) => {
            const componentMeta = breakdown?.[key];
            if (!componentMeta || typeof componentMeta.page !== "number") return null;

            const rawValue = parseFloat(
                String(componentMeta.raw_number || "0").replace(/[(),]/g, m => (m === "(" ? "-" : ""))
            );
            const isRelevant = fieldKey === "kas_masuk" ? rawValue >= 0 : rawValue < 0;
            if (!isRelevant) return null;

            // Label diambil dari hasil ekstraksi (label_in_pdf), bukan hardcode.
            // Fallback ke key field hanya jika label_in_pdf tidak tersedia.
            const componentLabel = componentMeta.label_in_pdf || key;

            return { key, label: componentLabel, meta: componentMeta };
        })
        .filter(Boolean);

    return (
        <div className="space-y-1 bg-white border border-slate-100 rounded-lg p-3 shadow-2xs">
            <label className="text-xs font-semibold text-slate-700 block">{label}</label>

            <input
                type="number"
                value={value}
                onChange={e => onChange("arus_kas", fieldKey, e.target.value)}
                className="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-md focus:outline-none focus:border-blue-500 font-mono bg-white text-slate-900"
                disabled={disabled}
            />

            {/* Panel Metadata Arus Kas (Manual Entri — tanpa Label PDF terpisah) */}
            <div className="mt-2 pt-2 border-t border-dashed border-slate-200 flex flex-col gap-1.5 text-[10px] text-slate-600">
                <div className="flex items-center gap-1 font-medium text-blue-600 mb-0.5">
                    <MapPin className="w-2.5 h-2.5" />
                    {hasMeta ? "Metadata PDF (Terdeteksi):" : "Metadata PDF (Manual Entri):"}
                </div>

                <div className="flex items-center gap-2">
                    <span className="w-16">Halaman:</span>
                    <input
                        type="number"
                        value={safeMeta.page || ''}
                        onChange={e => onMetaChange(metaKey, 'page', e.target.value === '' ? '' : parseInt(e.target.value))}
                        className="px-2 py-1 text-[10px] border border-slate-200 rounded w-16 focus:outline-none focus:border-blue-400"
                        disabled={disabled}
                        placeholder="Contoh: 7"
                    />
                </div>

                {/* Komponen Otomatis Pendukung — label dari hasil ekstraksi */}
                <div className="mt-1 bg-slate-50 p-2 rounded text-[9px]">
                    <div className="text-slate-500 font-medium mb-1">Cara Hitung</div>
                    {relevantComponents.length > 0 ? (
                        relevantComponents.map(({ key, label: componentLabel, meta: cMeta }) => (
                            <div key={key} className="flex items-center justify-between gap-2">
                                <span className="truncate">{componentLabel} (p.{cMeta.page}):</span>
                                <span className="font-mono bg-white border border-slate-100 px-1 py-0.5 rounded text-slate-700 font-medium shrink-0">
                                    {cMeta.raw_number || "-"}
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

export default function ExtractionForm({ data, foundAt, onDataChange, onMetaChange, disabled }) {
    return (
        <div className="grid grid-cols-1 gap-5">
            {/* 1. SEKSI TABEL NERACA */}
            <div className="border border-slate-100 rounded-xl p-4 bg-slate-50/40 space-y-4">
                <div className="flex items-center gap-1.5 border-b border-slate-200/60 pb-2 text-slate-800 font-bold text-sm">
                    <Scale className="w-4 h-4 text-blue-600" /> Tabel Neraca
                </div>
                <div className="space-y-3">
                    <InputFieldWithMeta
                        label="Aset Lancar (Current Assets)" section="neraca" fieldKey="current_assets" metaKey="current_assets"
                        value={data.neraca.current_assets} onChange={onDataChange}
                        meta={foundAt?.current_assets} onMetaChange={onMetaChange} disabled={disabled}
                    />
                    <InputFieldWithMeta
                        label="Total Aset (Total Assets)" section="neraca" fieldKey="total_assets" metaKey="total_assets"
                        value={data.neraca.total_assets} onChange={onDataChange}
                        meta={foundAt?.total_assets} onMetaChange={onMetaChange} disabled={disabled}
                    />
                    <InputFieldWithMeta
                        label="Liabilitas Pendek (Current Liabilities)" section="neraca" fieldKey="current_liabilities" metaKey="current_liabilities"
                        value={data.neraca.current_liabilities} onChange={onDataChange}
                        meta={foundAt?.current_liabilities} onMetaChange={onMetaChange} disabled={disabled}
                    />
                    <InputFieldWithMeta
                        label="Total Liabilitas (Total Liabilities)" section="neraca" fieldKey="total_liabilities" metaKey="total_liabilities"
                        value={data.neraca.total_liabilities} onChange={onDataChange}
                        meta={foundAt?.total_liabilities} onMetaChange={onMetaChange} disabled={disabled}
                    />
                    <InputFieldWithMeta
                        label="Total Ekuitas (Total Equity)" section="neraca" fieldKey="total_equity" metaKey="total_equity"
                        value={data.neraca.total_equity} onChange={onDataChange}
                        meta={foundAt?.total_equity} onMetaChange={onMetaChange} disabled={disabled}
                    />
                </div>
            </div>

            {/* 2. SEKSI TABEL LABA RUGI */}
            <div className="border border-slate-100 rounded-xl p-4 bg-slate-50/40 space-y-4">
                <div className="flex items-center gap-1.5 border-b border-slate-200/60 pb-2 text-slate-800 font-bold text-sm">
                    <TrendingUp className="w-4 h-4 text-emerald-600" /> Tabel Laba Rugi
                </div>
                <div className="space-y-3">
                    <InputFieldWithMeta
                        label="Pendapatan Usaha (Revenue)" section="laba_rugi" fieldKey="pendapatan" metaKey="revenue"
                        value={data.laba_rugi.pendapatan} onChange={onDataChange}
                        meta={foundAt?.revenue} onMetaChange={onMetaChange} disabled={disabled}
                    />
                    <InputFieldWithMeta
                        label="Laba Kotor (Gross Profit)" section="laba_rugi" fieldKey="laba_kotor" metaKey="gross_profit"
                        value={data.laba_rugi.laba_kotor} onChange={onDataChange}
                        meta={foundAt?.gross_profit} onMetaChange={onMetaChange} disabled={disabled}
                    />
                    <InputFieldWithMeta
                        label="Laba Bersih (Net Profit)" section="laba_rugi" fieldKey="laba_bersih" metaKey="net_profit"
                        value={data.laba_rugi.laba_bersih} onChange={onDataChange}
                        meta={foundAt?.net_profit} onMetaChange={onMetaChange} disabled={disabled}
                    />
                </div>
            </div>

            {/* 3. SEKSI TABEL ARUS KAS */}
            <div className="border border-slate-100 rounded-xl p-4 bg-slate-50/40 space-y-4">
                <div className="flex items-center gap-1.5 border-b border-slate-200/60 pb-2 text-slate-800 font-bold text-sm">
                    <Wallet className="w-4 h-4 text-indigo-600" /> Tabel Arus Kas
                </div>
                <div className="space-y-3">
                    <CashFlowInputWithBreakdown
                        label="Kas Masuk (Cash Inflow)" fieldKey="kas_masuk" metaKey="kas_masuk"
                        value={data.arus_kas.kas_masuk} onChange={onDataChange}
                        meta={foundAt?.kas_masuk} onMetaChange={onMetaChange}
                        breakdown={foundAt} disabled={disabled}
                    />
                    <CashFlowInputWithBreakdown
                        label="Kas Keluar (Cash Outflow)" fieldKey="kas_keluar" metaKey="kas_keluar"
                        value={data.arus_kas.kas_keluar} onChange={onDataChange}
                        meta={foundAt?.kas_keluar} onMetaChange={onMetaChange}
                        breakdown={foundAt} disabled={disabled}
                    />
                </div>
            </div>
        </div>
    );
}
