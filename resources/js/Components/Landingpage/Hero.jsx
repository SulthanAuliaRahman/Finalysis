import React from 'react';
import { Link } from '@inertiajs/react';

export default function Hero() {
    return (
        <section className="bg-white border-b border-slate-100">
            <div className="py-24 px-6 max-w-[1120px] mx-auto grid grid-cols-2 gap-16 items-center">
                <div>
                    <div className="inline-flex items-center gap-[6px] bg-[#EFF6FF] text-[#1D4ED8] px-3.5 py-1.5 rounded-[100px] text-xs font-semibold border border-[#BFDBFE] mb-6">
                        <span className="w-1.5 h-1.5 bg-[#3B82F6] rounded-full" style={{ animation: 'pulse 2s infinite' }}></span>
                        Sistem agent berbasis LLM + RAG
                    </div>
                    <h1 className="text-[44px] font-extrabold text-[#0F172A] leading-[1.15] tracking-tight mb-5">Ubah laporan keuangan menjadi <span className="text-[#2563EB]">insight strategis</span> secara otomatis.</h1>
                    <p className="text-base text-[#64748B] leading-relaxed mb-9 max-w-[480px]">
                        Finalysis mengintegrasikan 9 rasio finansial esensial, analisis Common-Size vertikal, dan DuPont Decomposition — semuanya diperkuat oleh konteks RAG dari dokumen PDF laporan keuangan asli perusahaan.
                    </p>
                    <div className="flex gap-3">
                        <Link href={route('perusahaan.index')} className="bg-[#2563EB] text-white px-6 py-3 rounded-[10px] text-[14px] font-semibold no-underline transition hover:bg-[#1D4ED8]">Buka dashboard</Link>
                        <a href="#workflow" className="bg-[#F1F5F9] text-[#334155] px-6 py-3 rounded-[10px] text-[14px] font-semibold no-underline transition hover:bg-[#E2E8F0]">Lihat cara kerja</a>
                    </div>
                </div>

                {/* MOCK DASHBOARD INTERFACES */}
                <div className="bg-[#0F1629] rounded-[20px] overflow-hidden border border-[#1E2A45]">
                    <div className="p-3.5 flex items-center justify-between bg-[#0D1526] border-b border-[#1E2A45]">
                        <div className="flex flex-col">
                            <span className="text-xs font-bold text-white">Dashboard</span>
                            <span className="text-[9px] text-[#4A6080]">Analisis Laporan Keuangan Berbasis LLM + RAG</span>
                        </div>
                        <span className="text-[9px] text-[#34D399] bg-[rgba(52,211,153,0.1)] border border-[rgba(52,211,153,0.25)] px-2 py-0.5 rounded font-mono">● RAG Active</span>
                    </div>
                    <div className="grid grid-cols-[80px_1fr]">
                        <div className="bg-[#0D1526] border-r border-[#1E2A45] py-2.5">
                            <div className="flex items-center justify-center pt-1.5 pb-2.5 border-b border-[#1E2A45] mb-1.5">
                                <div className="w-6 h-6 bg-[#2563EB] rounded-[5px] flex items-center justify-center"><i className="ti ti-chart-line text-xs text-white"></i></div>
                            </div>
                            <div className="flex flex-col items-center gap-[3px] px-1.5 py-2 text-[#60A5FA]"><i className="ti ti-layout-dashboard text-base"></i><span className="text-[8px]">Dashboard</span></div>
                            <div className="flex flex-col items-center gap-[3px] px-1.5 py-2 text-[#4A6080]"><i className="ti ti-building text-base"></i><span className="text-[8px]">Perusahaan</span></div>
                            <div className="flex flex-col items-center gap-[3px] px-1.5 py-2 text-[#4A6080]"><i className="ti ti-files text-base"></i><span className="text-[8px]">Dokumen</span></div>
                            <div className="flex flex-col items-center gap-[3px] px-1.5 py-2 text-[#4A6080]"><i className="ti ti-chart-bar text-base"></i><span className="text-[8px]">Analisis</span></div>
                        </div>
                        <div className="p-2.5 flex flex-col gap-2">
                            <div className="grid grid-cols-4 gap-1.5">
                                <div className="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2"><div className="text-[8px] text-[#4A6080] uppercase tracking-wider mb-1">Total Perusahaan</div><div className="text-xl font-bold text-white">3</div></div>
                                <div className="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2"><div className="text-[8px] text-[#4A6080] uppercase tracking-wider mb-1">Total Dokumen</div><div className="text-xl font-bold text-white">5</div></div>
                                <div className="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2"><div className="text-[8px] text-[#4A6080] uppercase tracking-wider mb-1">Total Analisis</div><div className="text-xl font-bold text-white">3</div></div>
                                <div className="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2"><div className="text-[8px] text-[#4A6080] uppercase tracking-wider mb-1">Rata-Rata Skor</div><div className="text-xl font-bold text-[#34D399]">81</div></div>
                            </div>
                            <div className="grid grid-cols-[1fr_0.55fr] gap-1.5">
                                <div className="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2.5">
                                    <div className="text-[9px] font-bold text-white">Skor Kesehatan Keuangan</div>
                                    <div className="flex items-end gap-2.5 h-[52px] px-1 mt-1">
                                        <div className="flex flex-col items-center gap-[3px] flex-1"><div className="w-full rounded-t-[3px] h-[42px] bg-[#1D9E75]"></div><span className="text-[7px] text-[#4A6080] truncate">Maju Bersama</span></div>
                                        <div className="flex flex-col items-center gap-[3px] flex-1"><div className="w-full rounded-t-[3px] h-[38px] bg-[#1D9E75]"></div><span className="text-[7px] text-[#4A6080] truncate">Tek. Nusantara</span></div>
                                    </div>
                                </div>
                                <div className="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2.5">
                                    <div className="text-[9px] font-bold text-white">Tren Aktivitas</div>
                                    <svg className="w-full h-[52px] mt-1" viewBox="0 0 120 52" preserveAspectRatio="none">
                                        <polyline points="0,38 20,32 40,35 60,20 80,16 100,18 120,10" fill="none" stroke="#60A5FA" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                            <div className="grid grid-cols-[1fr_0.65fr] gap-1.5">
                                <div className="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2.5">
                                    <div className="flex items-center justify-between mb-2"><span className="text-[9px] font-bold text-white">Analisis Terbaru</span></div>
                                    <div className="flex items-center justify-between py-1 border-b border-[#1A2540]"><div className="flex flex-col"><span className="text-[9px] font-semibold text-white">PT Tek. Nusantara</span><span className="text-[8px] text-[#4A6080]">FY 2024</span></div><span className="text-[7px] px-1 py-0.5 rounded-full bg-[rgba(52,211,153,0.1)] text-[#34D399] border border-[rgba(52,211,153,0.2)]">● Sehat 85</span></div>
                                </div>
                                <div className="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2.5">
                                    <div className="flex items-center justify-between mb-2"><span className="text-[9px] font-bold text-white">Perusahaan</span></div>
                                    <div className="flex items-center justify-between py-1 border-b border-[#1A2540]"><div><div className="text-[9px] font-semibold text-white truncate">PT Maju Bersama</div></div><span className="text-[7px] px-1 py-0.5 rounded-[3px] bg-[rgba(59,130,246,0.15)] text-[#60A5FA]">Mfr</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}