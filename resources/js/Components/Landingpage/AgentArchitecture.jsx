import React from 'react';

export default function AgentArchitecture() {
    return (
        <section className="bg-slate-50 border-t border-b border-slate-100 py-24 px-6">
            <div className="max-w-[1120px] mx-auto grid grid-cols-2 gap-[72px] items-center">
                <div>
                    <div className="text-[11px] font-bold tracking-widest uppercase text-[#2563EB] mb-3">Multi-Agent System</div>
                    <h2 className="text-[34px] font-extrabold text-[#0F172A] tracking-tight mb-4 leading-tight">Kolaborasi Agen Cerdas Specialist Keuangan</h2>
                    <p className="text-[15px] text-[#64748B] leading-relaxed mb-6">
                        Sistem kami menggerakkan beberapa sub-agent LLM yang memiliki keahlian spesifik guna meminimalisir halusinasi data dan menghasilkan kualitas narasi rekomendasi yang tajam layaknya analis senior keuangan.
                    </p>
                    <div className="border-l-4 border-[#3B82F6] pl-4 py-1 bg-blue-50/50 rounded-r-[6px] text-[13px] text-[#475569] italic">
                        "Pendekatan RAG memastikan agen selalu mengambil referensi hukum/catatan kaki atas laporan keuangan (CALK) secara riil dari dokumen asli."
                    </div>
                </div>
                <div className="grid grid-cols-2 gap-3">
                    <div className="bg-white border border-slate-200 rounded-[14px] p-5 relative pl-6 before:content-[''] before:absolute before:left-0 before:top-0 before:w-1 before:h-full before:bg-[#2563EB]">
                        <div className="text-[10px] font-mono text-[#2563EB] font-bold mb-1">AGENT 01</div>
                        <h4 className="text-[15px] font-bold text-[#0F172A] mb-1">Rasio Evaluator</h4>
                        <p className="text-xs text-[#64748B]">Fokus pada validasi angka komputasi matematis rumusan rasio keuangan.</p>
                    </div>
                    <div className="bg-white border border-slate-200 rounded-[14px] p-5 relative pl-6 before:content-[''] before:absolute before:left-0 before:top-0 before:w-1 before:h-full before:bg-[#059669]">
                        <div className="text-[10px] font-mono text-[#059669] font-bold mb-1">AGENT 02</div>
                        <h4 className="text-[15px] font-bold text-[#0F172A] mb-1">Contextual Writer</h4>
                        <p className="text-xs text-[#64748B]">Menyusun narasi penjelasan dinamika finansial berdasarkan CALK.</p>
                    </div>
                </div>
            </div>
        </section>
    );
}