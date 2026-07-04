import React from 'react';

export default function Workflow() {
    return (
        <section id="workflow" className="max-w-[1120px] mx-auto py-24 px-6">
            <div className="text-center max-w-[540px] mx-auto mb-14">
                <div className="text-[11px] font-bold tracking-widest uppercase text-[#2563EB] mb-3">Alur kerja sistem</div>
                <div className="text-[34px] font-extrabold text-[#0F172A] tracking-tight leading-tight">Dari PDF ke insight,<br/>dalam satu alur otomatis</div>
            </div>
            <div className="grid grid-cols-5 gap-0 relative before:content-[''] before:absolute before:top-7 before:left-[10%] before:right-[10%] before:h-px before:bg-slate-200 before:z-0">
                <div className="text-center relative z-10 px-2">
                    <div className="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-lg bg-[#2563EB] text-white border-2 border-[#2563EB]"><i className="ti ti-upload text-[22px]"></i></div>
                    <div className="text-[13px] font-bold text-[#1E293B] mb-1.5">Upload PDF</div>
                    <p className="text-xs text-[#64748B] leading-normal">Tim keuangan mengunggah dokumen laporan keuangan perusahaan</p>
                </div>
                <div className="text-center relative z-10 px-2">
                    <div className="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-lg bg-white text-[#2563EB] border-2 border-[#BFDBFE]"><i className="ti ti-file-text text-[22px]"></i></div>
                    <div className="text-[13px] font-bold text-[#1E293B] mb-1.5">Parsing & chunking</div>
                    <p className="text-xs text-[#64748B] leading-normal">Docling mengekstrak dan memecah dokumen menjadi chunk terstruktur</p>
                </div>
                <div className="text-center relative z-10 px-2">
                    <div className="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-lg bg-white text-[#2563EB] border-2 border-[#BFDBFE]"><i className="ti ti-database text-[22px]"></i></div>
                    <div className="text-[13px] font-bold text-[#1E293B] mb-1.5">Embedding Store</div>
                    <p className="text-xs text-[#64748B] leading-normal">Chunk diubah menjadi vektor dan disimpan untuk pencarian semantik</p>
                </div>
                <div className="text-center relative z-10 px-2">
                    <div className="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-lg bg-white text-[#2563EB] border-2 border-[#BFDBFE]"><i className="ti ti-cpu text-[22px]"></i></div>
                    <div className="text-[13px] font-bold text-[#1E293B] mb-1.5">Retrieval & Gen</div>
                    <p className="text-xs text-[#64748B] leading-normal">Agen mengambil konteks relevan dan menghasilkan narasi analisis</p>
                </div>
                <div className="text-center relative z-10 px-2">
                    <div className="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 font-bold text-lg bg-[#2563EB] text-white border-2 border-[#2563EB]"><i className="ti ti-report text-[22px]"></i></div>
                    <div className="text-[13px] font-bold text-[#1E293B] mb-1.5">Laporan PDF</div>
                    <p className="text-xs text-[#64748B] leading-normal">Hasil analisis laporan keuangan tersedia sebagai laporan yang dapat diunduh</p>
                </div>
            </div>
        </section>
    );
}