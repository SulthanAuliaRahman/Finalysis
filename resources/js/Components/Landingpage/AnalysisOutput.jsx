import React from 'react';

export default function AnalysisOutput() {
    return (
        <div id="output" className="bg-slate-50 border-t border-slate-100 py-24 px-6">
            <div className="max-w-[1120px] mx-auto">
                <div className="text-center max-w-[540px] mx-auto mb-14">
                    <div className="text-[11px] font-bold tracking-widest uppercase text-[#2563EB] mb-3">Hasil analisis</div>
                    <div className="text-[34px] font-extrabold text-[#0F172A] tracking-tight leading-tight">Apa yang dihasilkan<br/>sistem ini</div>
                </div>
                <div className="grid grid-cols-3 gap-5">
                    <div className="bg-white border border-slate-200 rounded-[14px] p-6">
                        <div className="w-10 h-10 rounded-[10px] bg-[#EFF6FF] flex items-center justify-center mb-4"><i className="ti ti-calculator text-[20px] text-[#2563EB]"></i></div>
                        <h3 className="text-[14px] font-bold text-[#0F172A] mb-2">9 rasio keuangan terkomputasi</h3>
                        <p className="text-[13px] text-[#64748B] leading-relaxed">Current, Quick, Cash Ratio, NPM, ROA, ROE, DER, DAR, dan TATO — dihitung otomatis dari data ekstraksi PDF, lengkap dengan nilai benchmark referensi.</p>
                    </div>
                    <div className="bg-white border border-slate-200 rounded-[14px] p-6">
                        <div className="w-10 h-10 rounded-[10px] bg-[#EFF6FF] flex items-center justify-center mb-4"><i className="ti ti-text-size text-[20px] text-[#2563EB]"></i></div>
                        <h3 className="text-[14px] font-bold text-[#0F172A] mb-2">Narasi analisis kontekstual</h3>
                        <p className="text-[13px] text-[#64748B] leading-relaxed">Setiap rasio dijelaskan dalam 4 lapis: angka dan cara hitung, perbandingan benchmark, implikasi bagi perusahaan, dan rekomendasi konkret untuk manajemen.</p>
                    </div>
                    <div className="bg-white border border-slate-200 rounded-[14px] p-6">
                        <div className="w-10 h-10 rounded-[10px] bg-[#EFF6FF] flex items-center justify-center mb-4"><i className="ti ti-chart-bar text-[20px] text-[#2563EB]"></i></div>
                        <h3 className="text-[14px] font-bold text-[#0F172A] mb-2">Visualisasi data finansial</h3>
                        <p className="text-[13px] text-[#64748B] leading-relaxed">Grafik komparatif, waterfall chart laba rugi, stacked balance sheet, dan line chart tren rasio — semua tersaji dalam satu dashboard terintegrasi.</p>
                    </div>
                    <div className="bg-white border border-slate-200 rounded-[14px] p-6">
                        <div className="w-10 h-10 rounded-[10px] bg-[#EFF6FF] flex items-center justify-center mb-4"><i className="ti ti-git-compare text-[20px] text-[#2563EB]"></i></div>
                        <h3 className="text-[14px] font-bold text-[#0F172A] mb-2">Analisis tren antar periode</h3>
                        <p className="text-[13px] text-[#64748B] leading-relaxed">Perbandingan rasio YoY dengan tabel arah perubahan (membaik/memburuk/stabil) dan persentase delta — tersedia jika data lebih dari satu periode diunggah.</p>
                    </div>
                    <div className="bg-white border border-slate-200 rounded-[14px] p-6">
                        <div className="w-10 h-10 rounded-[10px] bg-[#EFF6FF] flex items-center justify-center mb-4"><i className="ti ti-hierarchy text-[20px] text-[#2563EB]"></i></div>
                        <h3 className="text-[14px] font-bold text-[#0F172A] mb-2">Ukuran DuPont Decomposition</h3>
                        <p className="text-[13px] text-[#64748B] leading-relaxed">Dekomposisi ROE menjadi tiga komponen pendorong — NPM, Asset Turnover, dan Financial Leverage — dengan narasi faktor mana yang paling dominan.</p>
                    </div>
                    <div className="bg-white border border-slate-200 rounded-[14px] p-6">
                        <div className="w-10 h-10 rounded-[10px] bg-[#EFF6FF] flex items-center justify-center mb-4"><i className="ti ti-file-export text-[20px] text-[#2563EB]"></i></div>
                        <h3 className="text-[14px] font-bold text-[#0F172A] mb-2">Laporan PDF siap unduh</h3>
                        <p className="text-[13px] text-[#64748B] leading-relaxed">Seluruh hasil analisis dikemas dalam laporan PDF terstruktur lengkap dengan tabel data, grafik, insight per bagian, dan kesimpulan eksekutif.</p>
                    </div>
                </div>
            </div>
        </div>
    );
}