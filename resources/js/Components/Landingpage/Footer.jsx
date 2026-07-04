import React from 'react';

export default function Footer() {
    return (
        <footer className="bg-[#0F172A] py-10 px-6 text-center">
            <div className="flex items-center gap-2 justify-center mb-3">
                <div className="w-7 h-7 bg-[#2563EB] rounded-[6px] flex items-center justify-center"><i className="ti ti-chart-line text-sm text-white"></i></div>
                <span className="text-[15px] font-bold text-white">Final<span>ysis</span></span>
            </div>
            <p className="text-xs text-[#475569] leading-relaxed">
                &copy; 2026 Finalysis. Tugas Akhir Pengembangan Aplikasi Analisis Laporan Keuangan Berbasis LLM dengan Pendekatan RAG.
            </p>
        </footer>
    );
}