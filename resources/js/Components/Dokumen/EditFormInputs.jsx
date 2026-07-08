const formatRibuan = (value) => {
    if (value === null || value === undefined || value === '') return '';

    // Parse ke number dulu untuk buang desimal trailing dari DB (misal 13583330.00)
    const num = parseFloat(value);
    if (isNaN(num)) return '';

    // Ambil bagian integer saja (bulatkan), lalu format ribuan
    const intVal = Math.round(num);
    const isNegative = intVal < 0;
    const formatted = Math.abs(intVal).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");

    return isNegative ? `-${formatted}` : formatted;
};

const parseRibuan = (value) => {
    if (!value) return '';
    return value.toString().replace(/\./g, '');
};

export function EditInputField({ label, section, fieldKey, value, onChange, disabled }) {
    return (
        <div className="bg-white border border-slate-100 rounded-lg p-3 shadow-sm hover:border-slate-200 transition-colors flex items-center gap-3">
            <label className="text-xs font-semibold text-slate-600 w-44 shrink-0">{label}</label>
            <div className="relative flex-1">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span className="text-slate-400 text-sm font-semibold">$</span>
                </div>
                <input
                    type="text"
                    value={formatRibuan(value)}
                    onChange={e => {
                        const raw = parseRibuan(e.target.value);
                        onChange(section, fieldKey, raw === '' ? 0 : parseFloat(raw));
                    }}
                    disabled={disabled}
                    placeholder="0"
                    className="w-full pl-8 pr-3 py-1.5 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono bg-white text-slate-900 transition-all disabled:bg-slate-50 disabled:text-slate-500"
                />
            </div>
        </div>
    );
}

export function EditCashFlowComponentInput({ label, fieldKey, value, onChange, disabled }) {
    return (
        <div className="bg-white border border-slate-100 rounded-lg p-3 shadow-sm hover:border-slate-200 transition-colors flex items-center gap-3">
            <label className="text-xs font-semibold text-slate-600 w-44 shrink-0">{label}</label>
            <div className="relative flex-1">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span className="text-slate-400 text-sm font-semibold">$</span>
                </div>
                <input
                    type="text"
                    value={value === null ? '' : formatRibuan(value)}
                    onChange={e => {
                        const raw = parseRibuan(e.target.value);
                        onChange(fieldKey, raw);
                    }}
                    disabled={disabled}
                    placeholder="0 (negatif: -1.000.000)"
                    className="w-full pl-8 pr-3 py-1.5 text-sm border border-slate-200 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono bg-white text-slate-900 transition-all disabled:bg-slate-50 disabled:text-slate-500"
                />
            </div>
            {value !== null && value !== '' && !isNaN(parseFloat(value)) && (
                <span className={`text-[10px] font-semibold px-2 py-0.5 rounded shrink-0
                    ${parseFloat(value) >= 0
                        ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                        : 'bg-red-50 text-red-700 border border-red-200'
                    }`}>
                    {parseFloat(value) >= 0 ? '▲ Masuk' : '▼ Keluar'}
                </span>
            )}
        </div>
    );
}
