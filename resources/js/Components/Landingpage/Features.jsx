import React from 'react';

export default function Features() {
    return (
        <section id="fitur" className="py-24 px-6 max-w-[1120px] mx-auto">
            <div className="mb-14">
                <div className="text-[11px] font-bold tracking-widest uppercase text-[#2563EB] mb-3">Cakupan sistem</div>
                <div className="text-[34px] font-extrabold text-[#0F172A] tracking-tight mb-3.5 leading-tight">9 metrik utama,<br/>3 metode analisis mendalam</div>
                <div className="text-[15px] text-[#64748B] max-w-[540px] leading-relaxed">Finalysis menghitung seluruh rasio standar akuntansi korporat secara otomatis dari data yang diekstrak dokumen PDF laporan keuangan perusahaan.</div>
            </div>
            <div className="grid grid-cols-4 gap-5">
                <div className="bg-white border border-slate-200 rounded-[14px] p-6 transition hover:border-[#BFDBFE] hover:shadow-[0_4px_20px_rgba(37,99,235,0.08)]">
                    <div className="w-11 h-11 bg-[#EFF6FF] rounded-[10px] flex items-center justify-center mb-4"><i className="ti ti-wallet text-[22px] text-[#2563EB]"></i></div>
                    <h3 className="text-[15px] font-bold text-[#0F172A] mb-2">Rasio likuiditas</h3>
                    <p className="text-[13px] text-[#64748B] leading-relaxed">Menilai kesiapan kas jangka pendek perusahaan dalam memenuhi kewajiban yang jatuh tempo.</p>
                    <div className="flex flex-wrap gap-1.5 mt-3.5">
                        <span className="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748B] px-2 py-0.5 rounded">Current</span>
                        <span className="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748B] px-2 py-0.5 rounded">Quick</span>
                        <span className="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748B] px-2 py-0.5 rounded">Cash</span>
                    </div>
                </div>
                <div className="bg-white border border-slate-200 rounded-[14px] p-6 transition hover:border-[#BFDBFE] hover:shadow-[0_4px_20px_rgba(37,99,235,0.08)]">
                    <div className="w-11 h-11 bg-[#ECFDF5] rounded-[10px] flex items-center justify-center mb-4"><i className="ti ti-trending-up text-[22px] text-[#059669]"></i></div>
                    <h3 className="text-[15px] font-bold text-[#0F172A] mb-2">Rasio profitabilitas</h3>
                    <p className="text-[13px] text-[#64748B] leading-relaxed">Mengukur efektivitas perusahaan dalam menghasilkan laba dari pendapatan, aset, dan ekuitas.</p>
                    <div className="flex flex-wrap gap-1.5 mt-3.5">
                        <span className="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748B] px-2 py-0.5 rounded">NPM</span>
                        <span className="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748B] px-2 py-0.5 rounded">ROA</span>
                        <span className="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748B] px-2 py-0.5 rounded">ROE</span>
                    </div>
                </div>
                <div className="bg-white border border-slate-200 rounded-[14px] p-6 transition hover:border-[#BFDBFE] hover:shadow-[0_4px_20px_rgba(37,99,235,0.08)]">
                    <div className="w-11 h-11 bg-[#FFFBEB] rounded-[10px] flex items-center justify-center mb-4"><i className="ti ti-shield text-[22px] text-[#D97706]"></i></div>
                    <h3 className="text-[15px] font-bold text-[#0F172A] mb-2">Rasio solvabilitas</h3>
                    <p className="text-[13px] text-[#64748B] leading-relaxed">Mendeteksi risiko struktur permodalan jangka panjang dan ketahanan perusahaan terhadap kewajiban.</p>
                    <div className="flex flex-wrap gap-1.5 mt-3.5">
                        <span className="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748B] px-2 py-0.5 rounded">DER</span>
                        <span className="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748B] px-2 py-0.5 rounded">DAR</span>
                    </div>
                </div>
                <div className="bg-white border border-slate-200 rounded-[14px] p-6 transition hover:border-[#BFDBFE] hover:shadow-[0_4px_20px_rgba(37,99,235,0.08)]">
                    <div className="w-11 h-11 bg-[#F5F3FF] rounded-[10px] flex items-center justify-center mb-4"><i className="ti ti-refresh text-[22px] text-[#7C3AED]"></i></div>
                    <h3 className="text-[15px] font-bold text-[#0F172A] mb-2">Rasio aktivitas</h3>
                    <p className="text-[13px] text-[#64748B] leading-relaxed">Mengevaluasi efisiensi pemanfaatan aset perusahaan dalam menghasilkan pendapatan operasional.</p>
                    <div className="flex flex-wrap gap-1.5 mt-3.5">
                        <span className="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748B] px-2 py-0.5 rounded">TATO</span>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-3 gap-5 mt-5">
                <div className="bg-[#EFF6FF] border border-[#BFDBFE] rounded-[14px] p-6">
                    <div className="w-10 h-10 bg-[#EFF6FF] rounded-[10px] flex items-center justify-center mb-4"><i className="ti ti-layout-columns text-[20px] text-[#2563EB]"></i></div>
                    <h3 className="text-[15px] font-bold text-[#1E3A8A] mb-2">Common-size analysis</h3>
                    <p className="text-[13px] text-[#1D4ED8] leading-relaxed">Menyajikan setiap pos laporan keuangan sebagai persentase terhadap nilai dasar — Revenue untuk laba rugi, Total Aset untuk neraca — agar struktur biaya dan komposisi aset terlihat proporsional lintas periode.</p>
                </div>
                <div className="bg-[#EFF6FF] border border-[#BFDBFE] rounded-[14px] p-6">
                    <div className="w-10 h-10 bg-[#EFF6FF] rounded-[10px] flex items-center justify-center mb-4"><i className="ti ti-chart-dots text-[20px] text-[#2563EB]"></i></div>
                    <h3 className="text-[15px] font-bold text-[#1E3A8A] mb-2">DuPont decomposition</h3>
                    <p className="text-[13px] text-[#1D4ED8] leading-relaxed">Mengurai ROE menjadi tiga komponen (NPM × Asset Turnover × Financial Leverage) untuk mengidentifikasi faktor pendorong utama — apakah margin, efisiensi aset, atau leverage yang mendominasi.</p>
                </div>
                <div className="bg-[#EFF6FF] border border-[#BFDBFE] rounded-[14px] p-6">
                    <div className="w-10 h-10 bg-[#EFF6FF] rounded-[10px] flex items-center justify-center mb-4"><i className="ti ti-arrow-up-right text-[20px] text-[#2563EB]"></i></div>
                    <h3 className="text-[15px] font-bold text-[#1E3A8A] mb-2">Trend analysis</h3>
                    <p className="text-[13px] text-[#1D4ED8] leading-relaxed">Membandingkan nilai rasio antar periode (YoY) untuk mengidentifikasi arah perubahan — membaik, memburuk, atau stabil — sehingga momentum kinerja perusahaan terlihat jelas.</p>
                </div>
            </div>
        </section>
    );
}