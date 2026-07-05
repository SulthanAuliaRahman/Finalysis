import React from 'react';
import { Head, Link } from '@inertiajs/react';

export default function LandingPage() {
    return (
        <div className="bg-white text-slate-800 font-sans antialiased" style={{ lineHeight: '1.6', WebkitFontSmoothing: 'antialiased' }}>
            <Head title="Finalysis — Analisis Laporan Keuangan Berbasis LLM + RAG" />

            {/* Google Fonts & Tabler Icons di-load secara dinamis */}
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="true" />
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />

            {/* Custom Styles Injection */}
            <style>{`
                :root {
                    --blue-50: #EFF6FF; --blue-100: #DBEAFE; --blue-200: #BFDBFE; --blue-400: #60A5FA;
                    --blue-500: #3B82F6; --blue-600: #2563EB; --blue-700: #1D4ED8; --blue-800: #1E40AF; --blue-900: #1E3A8A;
                    --slate-50: #F8FAFC; --slate-100: #F1F5F9; --slate-200: #E2E8F0; --slate-300: #CBD5E1;
                    --slate-400: #94A3B8; --slate-500: #64748B; --slate-600: #475569; --slate-700: #334155;
                    --slate-800: #1E293B; --slate-900: #0F172A; --radius-sm: 6px; --radius: 10px;
                    --radius-lg: 14px; --radius-xl: 20px;
                }
                html { scroll-behavior: smooth; }
                body { font-family: 'Inter', sans-serif !important; }
                @keyframes pulse { 0%, 100% { opacity: 1 } 50% { opacity: .4 } }
            `}</style>

            {/* NAV */}
            <nav className="sticky top-0 z-50 bg-white/92 backdrop-blur-md border-b border-slate-100" style={{ backgroundColor: 'rgba(255,255,255,0.92)', backdropFilter: 'blur(12px)' }}>
                <div class="max-w-[1120px] mx-auto px-6 h-16 flex items-center justify-between">
                    <a href="#" class="flex items-center gap-[10px] no-underline">
                        <div class="w-9 h-36 bg-[#2563EB] rounded-[6px] flex items-center justify-center"><i class="ti ti-chart-line text-lg text-white"></i></div>
                        <span class="text-[17px] font-bold text-[#0F172A] tracking-tight">Final<span class="text-[#2563EB]">ysis</span></span>
                    </a>
                    <div class="flex gap-8">
                        <a href="#fitur" class="text-[14px] font-medium text-[#64748B] no-underline transition hover:text-[#2563EB]">Fitur</a>
                        <a href="#workflow" class="text-[14px] font-medium text-[#64748B] no-underline transition hover:text-[#2563EB]">Alur kerja</a>
                        <a href="#output" class="text-[14px] font-medium text-[#64748B] no-underline transition hover:text-[#2563EB]">Hasil analisis</a>
                    </div>
                    {/* Menggunakan Link bawaan Inertia untuk navigasi SPA tanpa reload */}
                    <Link href="/perusahaan" class="bg-[#2563EB] text-white px-5 py-[9px] rounded-[10px] text-[13px] font-semibold no-underline transition hover:bg-[#1D4ED8]">
                        Mulai analisis
                    </Link>
                </div>
            </nav>

            {/* HERO */}
            <section class="bg-white border-b border-slate-100">
                <div class="py-24 px-6 max-w-[1120px] mx-auto grid grid-cols-2 gap-16 items-center">
                    <div>
                        <div class="inline-flex items-center gap-[6px] bg-[#EFF6FF] text-[#1D4ED8] px-3.5 py-1.5 rounded-[100px] text-xs font-semibold border border-[#BFDBFE] mb-6">
                            <span class="w-1.5 h-1.5 bg-[#3B82F6] rounded-full" style={{ animation: 'pulse 2s infinite' }}></span>
                            Sistem agent berbasis LLM + RAG
                        </div>
                        <h1 class="text-[44px] font-extrabold text-[#0F172A] leading-[1.15] tracking-tight mb-5">Ubah laporan keuangan menjadi <span class="text-[#2563EB]">insight strategis</span> secara otomatis.</h1>
                        <p class="text-base text-[#64748B] leading-relaxed mb-9 max-w-[480px]">
                            Finalysis mengintegrasikan 9 rasio finansial esensial, analisis Common-Size vertikal, dan DuPont Decomposition — semuanya diperkuat oleh konteks RAG dari dokumen PDF laporan keuangan asli perusahaan.
                        </p>
                        <div class="flex gap-3">
                            <Link href="/perusahaan" class="bg-[#2563EB] text-white px-6 py-3 rounded-[10px] text-[14px] font-semibold no-underline transition hover:bg-[#1D4ED8]">Buka dashboard</Link>
                            <a href="#workflow" class="bg-[#F1F5F9] text-[#334155] px-6 py-3 rounded-[10px] text-[14px] font-semibold no-underline transition hover:bg-[#E2E8F0]">Lihat cara kerja</a>
                        </div>
                    </div>

                    {/* MOCK DASHBOARD INTERFACES */}
                    <div class="bg-[#0F1629] rounded-[20px] overflow-hidden border border-[#1E2A45]">
                        <div class="p-3.5 flex items-center justify-between bg-[#0D1526] border-b border-[#1E2A45]">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-white">Dashboard</span>
                                <span class="text-[9px] text-[#4A6080]">Analisis Laporan Keuangan Berbasis LLM + RAG</span>
                            </div>
                            <span class="text-[9px] text-[#34D399] bg-[rgba(52,211,153,0.1)] border border-[rgba(52,211,153,0.25)] px-2 py-0.5 rounded font-mono">● RAG Active</span>
                        </div>
                        <div class="grid grid-cols-[80px_1fr]">
                            <div class="bg-[#0D1526] border-r border-[#1E2A45] padding-y-2.5">
                                <div class="flex items-center justify-center pt-1.5 pb-2.5 border-b border-[#1E2A45] mb-1.5">
                                    <div class="w-6 h-6 bg-[#2563EB] rounded-[5px] flex items-center justify-center"><i class="ti ti-chart-line text-xs text-white"></i></div>
                                </div>
                                <div class="flex flex-col items-center gap-[3px] px-1.5 py-2 text-[#60A5FA]"><i class="ti ti-layout-dashboard text-base"></i><span class="text-[8px]">Dashboard</span></div>
                                <div class="flex flex-col items-center gap-[3px] px-1.5 py-2 text-[#4A6080]"><i class="ti ti-building text-base"></i><span class="text-[8px]">Perusahaan</span></div>
                                <div class="flex flex-col items-center gap-[3px] px-1.5 py-2 text-[#4A6080]"><i class="ti ti-files text-base"></i><span class="text-[8px]">Dokumen</span></div>
                                <div class="flex flex-col items-center gap-[3px] px-1.5 py-2 text-[#4A6080]"><i class="ti ti-chart-bar text-base"></i><span class="text-[8px]">Analisis</span></div>
                            </div>
                            <div class="p-2.5 flex flex-col gap-2">
                                <div class="grid grid-cols-4 gap-1.5">
                                    <div class="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2"><div class="text-[8px] text-[#4A6080] uppercase tracking-wider mb-1">Total Perusahaan</div><div class="text-xl font-bold text-white">3</div></div>
                                    <div class="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2"><div class="text-[8px] text-[#4A6080] uppercase tracking-wider mb-1">Total Dokumen</div><div class="text-xl font-bold text-white">5</div></div>
                                    <div class="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2"><div class="text-[8px] text-[#4A6080] uppercase tracking-wider mb-1">Total Analisis</div><div class="text-xl font-bold text-white">3</div></div>
                                    <div class="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2"><div class="text-[8px] text-[#4A6080] uppercase tracking-wider mb-1">Rata-Rata Skor</div><div class="text-xl font-bold text-[#34D399]">81</div></div>
                                </div>
                                <div class="grid grid-cols-[1fr_0.55fr] gap-1.5">
                                    <div class="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2.5">
                                        <div class="text-[9px] font-bold text-white">Skor Kesehatan Keuangan</div>
                                        <div class="flex items-end gap-2.5 h-[52px] px-1">
                                            <div class="flex flex-col items-center gap-[3px] flex-1"><div class="w-full rounded-t-[3px] h-[42px] bg-[#1D9E75]"></div><span class="text-[7px] text-[#4A6080]">Maju Bersama</span></div>
                                            <div class="flex flex-col items-center gap-[3px] flex-1"><div class="w-full rounded-t-[3px] h-[38px] bg-[#1D9E75]"></div><span class="text-[7px] text-[#4A6080]">Tek. Nusantara</span></div>
                                        </div>
                                    </div>
                                    <div class="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2.5">
                                        <div class="text-[9px] font-bold text-white">Tren Aktivitas</div>
                                        <svg class="w-full h-[52px]" viewBox="0 0 120 52" preserveAspectRatio="none">
                                            <polyline points="0,38 20,32 40,35 60,20 80,16 100,18 120,10" fill="none" stroke="#60A5FA" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="grid grid-cols-[1fr_0.65fr] gap-1.5">
                                    <div class="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2.5">
                                        <div class="flex items-center justify-between mb-2"><span class="text-[9px] font-bold text-white">Analisis Terbaru</span></div>
                                        <div class="flex items-center justify-between py-1 border-b border-[#1A2540]"><div class="flex flex-col"><span class="text-[9px] font-semibold text-white">PT Teknologi Nusantara</span><span class="text-[8px] text-[#4A6080]">FY 2024</span></div><span class="text-[8px] px-1.5 py-0.5 rounded-full bg-[rgba(52,211,153,0.1)] text-[#34D399] border border-[rgba(52,211,153,0.2)]">● Sehat 85</span></div>
                                    </div>
                                    <div class="bg-[#141E33] border border-[#1E2A45] rounded-[6px] p-2.5">
                                        <div class="flex items-center justify-between mb-2"><span class="text-[9px] font-bold text-white">Perusahaan</span></div>
                                        <div class="flex items-center justify-between py-1 border-b border-[#1A2540]"><div><div class="text-[9px] font-semibold text-white">PT Maju Bersama Tbk</div></div><span class="text-[7px] px-1.5 py-0.5 rounded-[3px] bg-[rgba(59,130,246,0.15)] text-[#60A5FA]">Manufaktur</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* STRIP */}
            <div class="bg-[#2563EB] py-10 px-6">
                <div class="max-w-[1120px] mx-auto grid grid-cols-4 gap-8 text-center">
                    <div><div class="text-3xl font-extrabold text-white mb-1">9</div><div class="text-[13px] text-white/75 font-medium">Rasio finansial dihitung otomatis</div></div>
                    <div><div class="text-3xl font-extrabold text-white mb-1">Agent</div><div class="text-[13px] text-white/75 font-medium">Agen spesialis</div></div>
                    <div><div class="text-3xl font-extrabold text-white mb-1">3</div><div class="text-[13px] text-white/75 font-medium">Metode analisis mendalam</div></div>
                    <div><div class="text-3xl font-extrabold text-white mb-1">PDF</div><div class="text-[13px] text-white/75 font-medium">Sumber dokumen langsung perusahaan</div></div>
                </div>
            </div>

            {/* FITUR */}
            <section id="fitur" class="py-22 px-6 max-w-[1120px] mx-auto">
                <div class="mb-14">
                    <div class="text-[11px] font-bold tracking-widest uppercase text-[#2563EB] mb-3">Cakupan sistem</div>
                    <div class="text-[34px] font-extrabold text-[#0F172A] tracking-tight mb-3.5 leading-tight">9 metrik utama,<br/>3 metode analisis mendalam</div>
                    <div class="text-[15px] text-[#64748b] max-w-[540px] leading-relaxed">Finalysis menghitung seluruh rasio standar akuntansi korporat secara otomatis dari data yang diekstrak dokumen PDF laporan keuangan perusahaan.</div>
                </div>
                <div class="grid grid-cols-4 gap-5">
                    <div class="bg-white border border-slate-200 rounded-[14px] p-6 transition hover:border-[#BFDBFE] hover:shadow-[0_4px_20px_rgba(37,99,235,0.08)]">
                        <div class="w-11 h-11 bg-[#EFF6FF] rounded-[10px] flex items-center justify-center mb-4"><i class="ti ti-wallet text-[22px] text-[#2563EB]"></i></div>
                        <h3 class="text-[15px] font-bold text-[#0F172A] mb-2">Rasio likuiditas</h3>
                        <p class="text-[13px] text-[#64748b] Ax-1.65">Menilai kesiapan kas jangka pendek perusahaan dalam memenuhi kewajiban yang jatuh tempo.</p>
                        <div class="flex flex-wrap gap-1.5 mt-3.5">
                            <span class="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748b] px-2 py-0.5 rounded">Current</span>
                            <span class="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748b] px-2 py-0.5 rounded">Quick</span>
                            <span class="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748b] px-2 py-0.5 rounded">Cash</span>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-[14px] p-6 transition hover:border-[#BFDBFE] hover:shadow-[0_4px_20px_rgba(37,99,235,0.08)]">
                        <div class="w-11 h-11 bg-[#ECFDF5] rounded-[10px] flex items-center justify-center mb-4"><i class="ti ti-trending-up text-[22px] text-[#059669]"></i></div>
                        <h3 class="text-[15px] font-bold text-[#0F172A] mb-2">Rasio profitabilitas</h3>
                        <p class="text-[13px] text-[#64748b] Ax-1.65">Mengukur efektivitas perusahaan dalam menghasilkan laba dari pendapatan, aset, dan ekuitas.</p>
                        <div class="flex flex-wrap gap-1.5 mt-3.5">
                            <span class="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748b] px-2 py-0.5 rounded">NPM</span>
                            <span class="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748b] px-2 py-0.5 rounded">ROA</span>
                            <span class="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748b] px-2 py-0.5 rounded">ROE</span>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-[14px] p-6 transition hover:border-[#BFDBFE] hover:shadow-[0_4px_20px_rgba(37,99,235,0.08)]">
                        <div class="w-11 h-11 bg-[#FFFBEB] rounded-[10px] flex items-center justify-center mb-4"><i class="ti ti-shield text-[22px] text-[#D97706]"></i></div>
                        <h3 class="text-[15px] font-bold text-[#0F172A] mb-2">Rasio solvabilitas</h3>
                        <p class="text-[13px] text-[#64748b] Ax-1.65">Mendeteksi risiko struktur permodalan jangka panjang dan ketahanan perusahaan terhadap kewajiban.</p>
                        <div class="flex flex-wrap gap-1.5 mt-3.5">
                            <span class="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748b] px-2 py-0.5 rounded">DER</span>
                            <span class="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748b] px-2 py-0.5 rounded">DAR</span>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-[14px] p-6 transition hover:border-[#BFDBFE] hover:shadow-[0_4px_20px_rgba(37,99,235,0.08)]">
                        <div class="w-11 h-11 bg-[#F5F3FF] rounded-[10px] flex items-center justify-center mb-4"><i class="ti ti-refresh text-[22px] text-[#7C3AED]"></i></div>
                        <h3 class="text-[15px] font-bold text-[#0F172A] mb-2">Rasio aktivitas</h3>
                        <p class="text-[13px] text-[#64748b] Ax-1.65">Mengevaluasi efisiensi pemanfaatan aset perusahaan dalam menghasilkan pendapatan operasional.</p>
                        <div class="flex flex-wrap gap-1.5 mt-3.5">
                            <span class="text-[10px] font-semibold font-mono bg-[#F1F5F9] text-[#64748b] px-2 py-0.5 rounded">TATO</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-5 mt-5">
                    <div class="bg-[#EFF6FF] border border-[#BFDBFE] rounded-[14px] p-6">
                        <div class="w-10 h-10 bg-[#EFF6FF] rounded-[10px] flex items-center justify-center mb-4"><i class="ti ti-layout-columns text-[20px] text-[#2563EB]"></i></div>
                        <h3 class="text-[15px] font-bold text-[#1E3A8A] mb-2">Common-size analysis</h3>
                        <p class="text-[13px] text-[#1D4ED8] leading-relaxed">Menyajikan setiap pos laporan keuangan sebagai persentase terhadap nilai dasar — Revenue untuk laba rugi, Total Aset untuk neraca — agar struktur biaya dan komposisi aset terlihat proporsional lintas periode.[cite: 6]</p>
                    </div>
                    <div class="bg-[#EFF6FF] border border-[#BFDBFE] rounded-[14px] p-6">
                        <div class="w-10 h-10 bg-[#EFF6FF] rounded-[10px] flex items-center justify-center mb-4"><i class="ti ti-chart-dots text-[20px] text-[#2563EB]"></i></div>
                        <h3 class="text-[15px] font-bold text-[#1E3A8A] mb-2">DuPont decomposition</h3>
                        <p class="text-[13px] text-[#1D4ED8] leading-relaxed">Mengurai ROE menjadi tiga komponen (NPM × Asset Turnover × Financial Leverage) untuk mengidentifikasi faktor pendorong utama — apakah margin, efisiensi aset, atau leverage yang mendominasi.[cite: 6]</p>
                    </div>
                    <div class="bg-[#EFF6FF] border border-[#BFDBFE] rounded-[14px] p-6">
                        <div className="w-10 h-10 bg-[#EFF6FF] rounded-[10px] flex items-center justify-center mb-4">
                            <i className="ti ti-arrow-up-right text-[20px] text-[#2563EB]" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-[15px] font-bold text-[#1E3A8A] mb-2">Trend analysis</h3>
                        <p class="text-[13px] text-[#1D4ED8] leading-relaxed">Membandingkan nilai rasio antar periode (YoY) untuk mengidentifikasi arah perubahan — membaik, memburuk, atau stabil — sehingga momentum kinerja perusahaan terlihat jelas.[cite: 6]</p>
                    </div>
                </div>
            </section>

            {/* ALUR KERJA */}
            <section id="workflow" class="max-w-[1120px] mx-auto py-22 px-6">
                <div class="text-center max-w-[540px] mx-auto mb-14">
                    <div class="text-[11px] font-bold tracking-widest uppercase text-[#2563EB] mb-3">Alur kerja sistem</div>
                    <div class="text-[34px] font-extrabold text-[#0F172A] tracking-tight leading-tight">Dari PDF ke insight,<br/>dalam satu alur otomatis</div>
                </div>
                <div class="grid grid-cols-5 gap-0 relative before:content-[''] before:absolute before:top-7 before:left-[10%] before:right-[10%] before:h-px before:bg-slate-200 before:z-0">
                    <div class="text-center relative z-10 px-2">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-lg bg-[#2563EB] text-white border-2 border-[#2563EB]"><i class="ti ti-upload text-[22px]"></i></div>
                        <div class="text-[13px] font-bold text-[#1E293B] mb-1.5">Upload PDF</div>
                        <p class="text-xs text-[#64748b] leading-normal">Tim keuangan mengunggah dokumen laporan keuangan perusahaan</p>
                    </div>
                    <div class="text-center relative z-10 px-2">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-lg bg-white text-[#2563EB] border-2 border-[#BFDBFE]"><i class="ti ti-file-text text-[22px]"></i></div>
                        <div class="text-[13px] font-bold text-[#1E293B] mb-1.5">Parsing & chunking</div>
                        <p class="text-xs text-[#64748b] leading-normal">Docling mengekstrak dan memecah dokumen menjadi chunk terstruktur</p>
                    </div>
                    <div class="text-center relative z-10 px-2">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-lg bg-white text-[#2563EB] border-2 border-[#BFDBFE]"><i class="ti ti-database text-[22px]"></i></div>
                        <div class="text-[13px] font-bold text-[#1E293B] mb-1.5">Embedding & vector store</div>
                        <p class="text-xs text-[#64748b] leading-normal">Chunk diubah menjadi vektor dan disimpan untuk pencarian semantik</p>
                    </div>
                    <div class="text-center relative z-10 px-2">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-lg bg-white text-[#2563EB] border-2 border-[#BFDBFE]"><i class="ti ti-cpu text-[22px]"></i></div>
                        <div class="text-[13px] font-bold text-[#1E293B] mb-1.5">Retrieval & generate</div>
                        <p class="text-xs text-[#64748b] leading-normal">Agen mengambil konteks relevan dan menghasilkan narasi analisis</p>
                    </div>
                    <div class="text-center relative z-10 px-2">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-lg bg-[#2563EB] text-white border-2 border-[#2563EB]"><i class="ti ti-report text-[22px]"></i></div>
                        <div class="text-[13px] font-bold text-[#1E293B] mb-1.5">Laporan PDF</div>
                        <p class="text-xs text-[#64748b] leading-normal">Hasil analisis laporan keuangan tersedia sebagai laporan yang dapat diunduh</p>
                    </div>
                </div>
            </section>

            {/* HASIL ANALISIS */}
            <div id="output" class="bg-slate-50 border-t border-slate-100 py-22 px-6">
                <div class="max-w-[1120px] mx-auto">
                    <div class="text-center max-w-[540px] mx-auto mb-14">
                        <div class="text-[11px] font-bold tracking-widest uppercase text-[#2563EB] mb-3">Hasil analisis</div>
                        <div class="text-[34px] font-extrabold text-[#0F172A] tracking-tight leading-tight">Apa yang dihasilkan<br/>sistem ini</div>
                    </div>
                    <div class="grid grid-cols-3 gap-5">
                        <div class="bg-white border border-slate-200 rounded-[14px] p-6">
                            <div class="w-10 h-10 rounded-[10px] bg-[#EFF6FF] flex items-center justify-center mb-4"><i class="ti ti-calculator text-[20px] text-[#2563EB]"></i></div>
                            <h3 class="text-[14px] font-bold text-[#0F172A] mb-2">9 rasio keuangan terkomputasi</h3>
                            <p class="text-[13px] text-[#64748b] leading-[1.65]">Current, Quick, Cash Ratio, NPM, ROA, ROE, DER, DAR, dan TATO — dihitung otomatis dari data ekstraksi PDF, lengkap dengan nilai benchmark referensi per rasio.[cite: 6]</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-[14px] p-6">
                            <div class="w-10 h-10 rounded-[10px] bg-[#EFF6FF] flex items-center justify-center mb-4"><i class="ti ti-text-size text-[20px] text-[#2563EB]"></i></div>
                            <h3 class="text-[14px] font-bold text-[#0F172A] mb-2">Narasi analisis kontekstual</h3>
                            <p class="text-[13px] text-[#64748b] leading-[1.65]">Setiap rasio dijelaskan dalam 4 lapis: angka dan cara hitung, perbandingan benchmark, implikasi bagi perusahaan, dan rekomendasi konkret untuk manajemen.[cite: 6]</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-[14px] p-6">
                            <div class="w-10 h-10 rounded-[10px] bg-[#EFF6FF] flex items-center justify-center mb-4"><i class="ti ti-chart-bar text-[20px] text-[#2563EB]"></i></div>
                            <h3 class="text-[14px] font-bold text-[#0F172A] mb-2">Visualisasi data finansial</h3>
                            <p class="text-[13px] text-[#64748b] leading-[1.65]">Grafik komparatif, waterfall chart laba rugi, stacked balance sheet, dan line chart tren rasio — semua tersaji dalam satu dashboard terintegrasi.[cite: 6]</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-[14px] p-6">
                            <div class="w-10 h-10 rounded-[10px] bg-[#EFF6FF] flex items-center justify-center mb-4"><i class="ti ti-git-compare text-[20px] text-[#2563EB]"></i></div>
                            <h3 class="text-[14px] font-bold text-[#0F172A] mb-2">Analisis tren antar periode</h3>
                            <p class="text-[13px] text-[#64748b] leading-[1.65]">Perbandingan rasio YoY dengan tabel arah perubahan (membaik/memburuk/stabil) dan persentase delta — tersedia jika data lebih dari satu periode diunggah.[cite: 6]</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-[14px] p-6">
                            <div class="w-10 h-10 rounded-[10px] bg-[#EFF6FF] flex items-center justify-center mb-4"><i class="ti ti-hierarchy text-[20px] text-[#2563EB]"></i></div>
                            <h3 class="text-[14px] font-bold text-[#0F172A] mb-2">Ukuran DuPont Decomposition</h3>
                            <p class="text-[13px] text-[#64748b] leading-[1.65]">Dekomposisi ROE menjadi tiga komponen pendorong — NPM, Asset Turnover, dan Financial Leverage — dengan narasi faktor mana yang paling dominan.[cite: 6]</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-[14px] p-6">
                            <div class="w-10 h-10 rounded-[10px] bg-[#EFF6FF] flex items-center justify-center mb-4"><i class="ti ti-file-export text-[20px] text-[#2563EB]"></i></div>
                            <h3 class="text-[14px] font-bold text-[#0F172A] mb-2">Laporan PDF siap unduh</h3>
                            <p class="text-[13px] text-[#64748b] leading-[1.65]">Seluruh hasil analisis dikemas dalam laporan PDF terstruktur lengkap dengan tabel data, grafik, insight per bagian, dan kesimpulan eksekutif.[cite: 6]</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* FOOTER */}
            <footer class="bg-[#0F172A] py-10 px-6 text-center">
                <div class="flex items-center gap-2 justify-center mb-3">
                    <div class="w-7 h-7 bg-[#2563EB] rounded-[6px] flex items-center justify-center"><i class="ti ti-chart-line text-sm text-white"></i></div>
                    <span class="text-[15px] font-bold text-white">Final<span>ysis</span></span>
                </div>
                <p class="text-xs text-[#475569] leading-relaxed">
                    &copy; 2026 Finalysis. Tugas Akhir Pengembangan Aplikasi Analisis Laporan Keuangan Berbasis LLM dengan Pendekatan RAG.
                </p>
            </footer>
        </div>
    );
}