import React from 'react';
import { Head } from '@inertiajs/react';

// Import sub-komponen dari folder Components/Landingpage
import Navbar from '@/Components/Landingpage/Navbar';
import Hero from '@/Components/Landingpage/Hero';
import StatsStrip from '@/Components/Landingpage/StatsStrip';
import Features from '@/Components/Landingpage/Features';
import AgentArchitecture from '@/Components/Landingpage/AgentArchitecture';
import Workflow from '@/Components/Landingpage/Workflow';
import AnalysisOutput from '@/Components/Landingpage/AnalysisOutput';
import Footer from '@/Components/Landingpage/Footer';

export default function LandingPage() {
    return (
        <div className="bg-white text-slate-800 font-sans antialiased" style={{ lineHeight: '1.6', WebkitFontSmoothing: 'antialiased' }}>
            <Head title="Finalysis — Analisis Laporan Keuangan Berbasis LLM + RAG" />

            {/* Asset CDN Preconnect */}
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="true" />
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />

            {/* Global Theme & Animation Injections */}
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

            {/* Susunan Komponen Utama */}
            <Navbar />
            <Hero />
            <StatsStrip />
            <Features />

            <Workflow />
            <AnalysisOutput />
            <Footer />
        </div>
    );
}