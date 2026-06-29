import { Scale, TrendingUp, Wallet, MapPin } from "lucide-react";

// Sub-Komponen Internal untuk Input Field beserta data Found At (Sekarang Editable)
function InputFieldWithMeta({ label, section, fieldKey, metaKey, value, onChange, meta, onMetaChange, disabled }) {
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

            {/* Panel Metadata Found At (Editable) */}
            {meta ? (
                <div className="mt-2 pt-2 border-t border-dashed border-slate-200 flex flex-col gap-1.5 text-[10px] text-slate-600">
                    <div className="flex items-center gap-1 font-medium text-blue-600 mb-0.5">
                        <MapPin className="w-2.5 h-2.5" /> Metadata PDF (Dapat Diedit):
                    </div>

                    <div className="flex items-center gap-2">
                        <span className="w-16">Halaman:</span>
                        <input
                            type="number"
                            value={meta.page || ''}
                            onChange={e => onMetaChange(metaKey, 'page', e.target.value)}
                            className="px-2 py-1 text-[10px] border border-slate-200 rounded w-16 focus:outline-none focus:border-blue-400"
                            disabled={disabled}
                        />
                    </div>

                    <div className="flex items-center gap-2">
                        <span className="w-16">Label PDF:</span>
                        <input
                            type="text"
                            value={meta.label_in_pdf || ''}
                            onChange={e => onMetaChange(metaKey, 'label_in_pdf', e.target.value)}
                            className="px-2 py-1 text-[10px] border border-slate-200 rounded flex-grow focus:outline-none focus:border-blue-400 font-italic"
                            disabled={disabled}
                        />
                    </div>

                    <div className="truncate mt-0.5">
                        <span className="w-16 inline-block">Angka Asli:</span>
                        <span className="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 font-medium">
                            {meta.all_numbers_on_row?.join(" | ") || "-"}
                        </span>
                    </div>
                </div>
            ) : (
                <div className="text-[10px] text-slate-400 italic mt-1">• Posisi teks tidak terdeteksi</div>
            )}
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
                    <InputFieldWithMeta
                        label="Kas Masuk (Cash Inflow)" section="arus_kas" fieldKey="kas_masuk" metaKey="cfo"
                        value={data.arus_kas.kas_masuk} onChange={onDataChange}
                        meta={foundAt?.cfo} onMetaChange={onMetaChange} disabled={disabled}
                    />
                    <InputFieldWithMeta
                        label="Kas Keluar (Cash Outflow)" section="arus_kas" fieldKey="kas_keluar" metaKey="cff"
                        value={data.arus_kas.kas_keluar} onChange={onDataChange}
                        meta={foundAt?.cff} onMetaChange={onMetaChange} disabled={disabled}
                    />
                </div>
            </div>
        </div>
    );
}
