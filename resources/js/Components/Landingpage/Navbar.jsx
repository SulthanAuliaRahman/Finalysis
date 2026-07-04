import React from 'react';
import { Link } from '@inertiajs/react';

// PASTIKAN ADA KATA 'export default' SEBELUM 'function Navbar()'
export default function Navbar() {
    return (
        <nav className="sticky top-0 z-50 border-b border-slate-100" style={{ backgroundColor: 'rgba(255,255,255,0.92)', backdropFilter: 'blur(12px)' }}>
            <div className="max-w-[1120px] mx-auto px-6 h-16 flex items-center justify-between">
                <a href="#" className="flex items-center gap-[10px] no-underline">
                    <div className="w-9 h-9 bg-[#2563EB] rounded-[6px] flex items-center justify-center">
                        <i className="ti ti-chart-line text-lg text-white"></i>
                    </div>
                    <span className="text-[17px] font-bold text-[#0F172A] tracking-tight">Final<span className="text-[#2563EB]">ysis</span></span>
                </a>
                <div className="flex gap-8">
                    <a href="#fitur" className="text-[14px] font-medium text-[#64748B] no-underline transition hover:text-[#2563EB]">Fitur</a>
                    <a href="#workflow" className="text-[14px] font-medium text-[#64748B] no-underline transition hover:text-[#2563EB]">Alur kerja</a>
                    <a href="#output" className="text-[14px] font-medium text-[#64748B] no-underline transition hover:text-[#2563EB]">Hasil analisis</a>
                </div>
                <Link href={route('perusahaan.index')} className="bg-[#2563EB] text-white px-5 py-[9px] rounded-[10px] text-[13px] font-semibold no-underline transition hover:bg-[#1D4ED8]">
                    Mulai analisis
                </Link>
            </div>
        </nav>
    );
}