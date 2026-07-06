import React from 'react';

export default function StatsStrip() {
    return (
        <div className="bg-[#2563EB] py-10 px-6">
            <div className="max-w-[1120px] mx-auto grid grid-cols-4 gap-8 text-center">
                <div><div className="text-3xl font-extrabold text-white mb-1">9</div><div className="text-[13px] text-white/75 font-medium">Rasio finansial dihitung otomatis</div></div>
                <div><div className="text-3xl font-extrabold text-white mb-1">Agent</div><div className="text-[13px] text-white/75 font-medium">Agen spesialis</div></div>
                <div><div className="text-3xl font-extrabold text-white mb-1">3</div><div className="text-[13px] text-white/75 font-medium">Metode analisis mendalam</div></div>
                <div><div className="text-3xl font-extrabold text-white mb-1">PDF</div><div className="text-[13px] text-white/75 font-medium">Sumber dokumen langsung perusahaan</div></div>
            </div>
        </div>
    );
}