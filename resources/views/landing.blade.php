<!DOCTYPE html>
    <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Finalysis — Analisis Laporan Keuangan Berbasis LLM + RAG</title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
            <style>
            *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
            :root{
            --blue-50:#EFF6FF;
            --blue-100:#DBEAFE;
            --blue-200:#BFDBFE;
            --blue-400:#60A5FA;
            --blue-500:#3B82F6;
            --blue-600:#2563EB;
            --blue-700:#1D4ED8;
            --blue-800:#1E40AF;
            --blue-900:#1E3A8A;
            --slate-50:#F8FAFC;
            --slate-100:#F1F5F9;
            --slate-200:#E2E8F0;
            --slate-300:#CBD5E1;
            --slate-400:#94A3B8;
            --slate-500:#64748B;
            --slate-600:#475569;
            --slate-700:#334155;
            --slate-800:#1E293B;
            --slate-900:#0F172A;
            --radius-sm:6px;
            --radius:10px;
            --radius-lg:14px;
            --radius-xl:20px;
            }
            html{scroll-behavior:smooth}
            body{font-family:'Inter',sans-serif;background:#fff;color:var(--slate-800);line-height:1.6;-webkit-font-smoothing:antialiased}

            /* NAV */
            nav{position:sticky;top:0;z-index:100;background:rgba(255,255,255,0.92);backdrop-filter:blur(12px);border-bottom:1px solid var(--slate-100)}
            .nav-inner{max-width:1120px;margin:0 auto;padding:0 24px;height:64px;display:flex;align-items:center;justify-content:space-between}
            .nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none}
            .nav-logo-icon{width:36px;height:36px;background:var(--blue-600);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center}
            .nav-logo-icon i{font-size:18px;color:#fff}
            .nav-logo-text{font-size:17px;font-weight:700;color:var(--slate-900);letter-spacing:-0.3px}
            .nav-logo-text span{color:var(--blue-600)}
            .nav-links{display:flex;gap:32px}
            .nav-links a{font-size:14px;font-weight:500;color:var(--slate-500);text-decoration:none;transition:color .15s}
            .nav-links a:hover{color:var(--blue-600)}
            .nav-cta{background:var(--blue-600);color:#fff;padding:9px 20px;border-radius:var(--radius);font-size:13px;font-weight:600;text-decoration:none;transition:background .15s}
            .nav-cta:hover{background:var(--blue-700)}

            /* HERO */
            .hero{padding:96px 24px 80px;max-width:1120px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center}
            .hero-badge{display:inline-flex;align-items:center;gap:6px;background:var(--blue-50);color:var(--blue-700);padding:6px 14px;border-radius:100px;font-size:12px;font-weight:600;border:1px solid var(--blue-200);margin-bottom:24px}
            .badge-dot{width:6px;height:6px;background:var(--blue-500);border-radius:50%;animation:pulse 2s infinite}
            @keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
            .hero h1{font-size:44px;font-weight:800;color:var(--slate-900);line-height:1.15;letter-spacing:-1px;margin-bottom:20px}
            .hero h1 span{color:var(--blue-600)}
            .hero-desc{font-size:16px;color:var(--slate-500);line-height:1.7;margin-bottom:36px;max-width:480px}
            .hero-actions{display:flex;gap:12px}
            .btn-primary{background:var(--blue-600);color:#fff;padding:12px 24px;border-radius:var(--radius);font-size:14px;font-weight:600;text-decoration:none;transition:background .15s}
            .btn-primary:hover{background:var(--blue-700)}
            .btn-secondary{background:var(--slate-100);color:var(--slate-700);padding:12px 24px;border-radius:var(--radius);font-size:14px;font-weight:600;text-decoration:none;transition:background .15s}
            .btn-secondary:hover{background:var(--slate-200)}

            /* MOCK DASHBOARD */
            .mock-wrap{background:#0F1629;border-radius:var(--radius-xl);overflow:hidden;border:1px solid #1E2A45}
            .mock-topbar{padding:10px 14px;display:flex;align-items:center;justify-content:space-between;background:#0D1526;border-bottom:1px solid #1E2A45}
            .mock-topbar-left{display:flex;flex-direction:column}
            .mock-topbar-title{font-size:12px;font-weight:700;color:#fff}
            .mock-topbar-sub{font-size:9px;color:#4A6080}
            .mock-rag-badge{font-size:9px;color:#34D399;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);padding:3px 8px;border-radius:4px;font-family:monospace}
            .mock-layout{display:grid;grid-template-columns:80px 1fr}
            .mock-sidebar{background:#0D1526;border-right:1px solid #1E2A45;padding:10px 0}
            .mock-sidebar-logo{display:flex;align-items:center;justify-content:center;padding:6px 0 10px;border-bottom:1px solid #1E2A45;margin-bottom:6px}
            .mock-sidebar-logo-icon{width:24px;height:24px;background:var(--blue-600);border-radius:5px;display:flex;align-items:center;justify-content:center}
            .mock-sidebar-logo-icon i{font-size:12px;color:#fff}
            .mock-nav-item{display:flex;flex-direction:column;align-items:center;gap:3px;padding:8px 6px;cursor:default}
            .mock-nav-item i{font-size:16px}
            .mock-nav-item span{font-size:8px}
            .mock-nav-item.active{color:#60A5FA}
            .mock-nav-item.active i{color:#60A5FA}
            .mock-nav-item:not(.active){color:#4A6080}
            .mock-content{padding:10px;display:flex;flex-direction:column;gap:8px}
            .mock-stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:6px}
            .mock-stat{background:#141E33;border:1px solid #1E2A45;border-radius:6px;padding:8px 10px}
            .mock-stat-label{font-size:8px;color:#4A6080;text-transform:uppercase;letter-spacing:.04em;margin-bottom:3px}
            .mock-stat-num{font-size:16px;font-weight:700;color:#fff}
            .mock-stat-sub{font-size:8px;color:#4A6080;margin-top:1px}
            .mock-charts{display:grid;grid-template-columns:1fr .55fr;gap:6px}
            .mock-chart-card{background:#141E33;border:1px solid #1E2A45;border-radius:6px;padding:10px}
            .mock-chart-title{font-size:9px;font-weight:700;color:#fff;margin-bottom:1px}
            .mock-chart-sub{font-size:8px;color:#4A6080;margin-bottom:8px}
            .mock-bars{display:flex;align-items:flex-end;gap:10px;height:52px;padding:0 4px}
            .mock-bar-item{display:flex;flex-direction:column;align-items:center;gap:3px;flex:1}
            .mock-bar-fill{width:100%;border-radius:3px 3px 0 0}
            .mock-bar-label{font-size:7px;color:#4A6080}
            .mock-line-svg{width:100%;height:52px}
            .mock-bottom{display:grid;grid-template-columns:1fr .65fr;gap:6px}
            .mock-list-card{background:#141E33;border:1px solid #1E2A45;border-radius:6px;padding:10px}
            .mock-list-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
            .mock-list-title{font-size:9px;font-weight:700;color:#fff}
            .mock-list-link{font-size:8px;color:#60A5FA}
            .mock-list-item{display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:1px solid #1A2540}
            .mock-list-item:last-child{border-bottom:none}
            .mock-list-item-left{display:flex;flex-direction:column}
            .mock-list-item-name{font-size:9px;font-weight:600;color:#fff}
            .mock-list-item-sub{font-size:8px;color:#4A6080}
            .mock-status{font-size:8px;padding:2px 6px;border-radius:100px;font-weight:600}
            .mock-status.sehat{background:rgba(52,211,153,.1);color:#34D399;border:1px solid rgba(52,211,153,.2)}
            .mock-status.waspada{background:rgba(251,191,36,.1);color:#FCD34D;border:1px solid rgba(251,191,36,.2)}
            .mock-company-item{display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:1px solid #1A2540}
            .mock-company-item:last-child{border-bottom:none}
            .mock-company-name-text{font-size:9px;font-weight:600;color:#fff}
            .mock-company-docs{font-size:8px;color:#4A6080}
            .mock-industry-badge{font-size:7px;padding:2px 6px;border-radius:3px;font-weight:600}
            .mock-industry-badge.mfr{background:rgba(59,130,246,.15);color:#60A5FA}
            .mock-industry-badge.tek{background:rgba(167,139,250,.15);color:#C4B5FD}
            .mock-industry-badge.erg{background:rgba(251,191,36,.12);color:#FCD34D}

            /* STATS STRIP */
            .stats-strip{background:var(--blue-600);padding:40px 24px}
            .stats-inner{max-width:1120px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:32px;text-align:center}
            .stat-num{font-size:32px;font-weight:800;color:#fff;margin-bottom:4px}
            .stat-label{font-size:13px;color:rgba(255,255,255,.75);font-weight:500}

            /* FITUR */
            .section{padding:88px 24px;max-width:1120px;margin:0 auto}
            .section-tag{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--blue-600);margin-bottom:12px}
            .section-title{font-size:34px;font-weight:800;color:var(--slate-900);letter-spacing:-0.5px;margin-bottom:14px;line-height:1.2}
            .section-sub{font-size:15px;color:var(--slate-500);max-width:540px;line-height:1.7}
            .section-head{margin-bottom:56px}

            .feature-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px}
            .feature-card{background:#fff;border:1px solid var(--slate-200);border-radius:var(--radius-lg);padding:24px;transition:border-color .2s,box-shadow .2s}
            .feature-card:hover{border-color:var(--blue-200);box-shadow:0 4px 20px rgba(37,99,235,.08)}
            .feature-icon{width:44px;height:44px;border-radius:var(--radius);display:flex;align-items:center;justify-content:center;margin-bottom:16px}
            .feature-icon i{font-size:22px}
            .fi-blue{background:var(--blue-50)}.fi-blue i{color:var(--blue-600)}
            .fi-green{background:#ECFDF5}.fi-green i{color:#059669}
            .fi-amber{background:#FFFBEB}.fi-amber i{color:#D97706}
            .fi-violet{background:#F5F3FF}.fi-violet i{color:#7C3AED}
            .feature-card h3{font-size:15px;font-weight:700;color:var(--slate-900);margin-bottom:8px}
            .feature-card p{font-size:13px;color:var(--slate-500);line-height:1.65}
            .feature-tags{display:flex;flex-wrap:wrap;gap:6px;margin-top:14px}
            .feature-tag{font-size:10px;font-weight:600;font-family:monospace;background:var(--slate-100);color:var(--slate-500);padding:3px 8px;border-radius:4px}

            /* ARSITEKTUR */
            .arsitektur-wrap{background:var(--slate-50);border-top:1px solid var(--slate-100);border-bottom:1px solid var(--slate-100);padding:88px 24px}
            .arsitektur-inner{max-width:1120px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:72px;align-items:center}
            .arsitektur-desc{font-size:15px;color:var(--slate-500);line-height:1.75;margin-bottom:24px}
            .arsitektur-quote{border-left:3px solid var(--blue-500);padding:12px 16px;background:var(--blue-50);border-radius:0 var(--radius-sm) var(--radius-sm) 0;font-size:13px;color:var(--slate-600);line-height:1.65;font-style:italic}
            .agent-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
            .agent-card{background:#fff;border:1px solid var(--slate-200);border-radius:var(--radius-lg);padding:20px;position:relative;overflow:hidden}
            .agent-card::before{content:'';position:absolute;top:0;left:0;width:3px;height:100%}
            .agent-card.a1::before{background:var(--blue-500)}
            .agent-card.a2::before{background:#059669}
            .agent-card.a3::before{background:#D97706}
            .agent-card.a4::before{background:#7C3AED}
            .agent-num{font-size:10px;font-weight:700;font-family:monospace;margin-bottom:8px}
            .agent-card.a1 .agent-num{color:var(--blue-600)}
            .agent-card.a2 .agent-num{color:#059669}
            .agent-card.a3 .agent-num{color:#D97706}
            .agent-card.a4 .agent-num{color:#7C3AED}
            .agent-name{font-size:15px;font-weight:700;color:var(--slate-900);margin-bottom:6px}
            .agent-desc{font-size:12px;color:var(--slate-500);line-height:1.6}

            /* WORKFLOW */
            .workflow-section{padding:88px 24px;max-width:1120px;margin:0 auto}
            .workflow-steps{display:grid;grid-template-columns:repeat(5,1fr);gap:0;margin-top:56px;position:relative}
            .workflow-steps::before{content:'';position:absolute;top:28px;left:10%;right:10%;height:1px;background:var(--slate-200);z-index:0}
            .workflow-step{text-align:center;position:relative;z-index:1;padding:0 8px}
            .step-num{width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-weight:700;font-size:18px;border:2px solid}
            .step-num.s1,.step-num.s5{background:var(--blue-600);color:#fff;border-color:var(--blue-600)}
            .step-num.s2,.step-num.s3,.step-num.s4{background:#fff;color:var(--blue-600);border-color:var(--blue-200)}
            .step-num i{font-size:22px}
            .step-title{font-size:13px;font-weight:700;color:var(--slate-800);margin-bottom:6px}
            .step-desc{font-size:12px;color:var(--slate-500);line-height:1.6}

            /* OUTPUT */
            .output-section{background:var(--slate-50);border-top:1px solid var(--slate-100);padding:88px 24px}
            .output-inner{max-width:1120px;margin:0 auto}
            .output-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:56px}
            .output-card{background:#fff;border:1px solid var(--slate-200);border-radius:var(--radius-lg);padding:24px}
            .output-icon{width:40px;height:40px;border-radius:var(--radius);background:var(--blue-50);display:flex;align-items:center;justify-content:center;margin-bottom:16px}
            .output-icon i{font-size:20px;color:var(--blue-600)}
            .output-card h3{font-size:14px;font-weight:700;color:var(--slate-900);margin-bottom:8px}
            .output-card p{font-size:13px;color:var(--slate-500);line-height:1.65}

            /* FOOTER */
            footer{background:var(--slate-900);padding:40px 24px;text-align:center}
            .footer-logo{display:flex;align-items:center;gap:8px;justify-content:center;margin-bottom:12px}
            .footer-logo-icon{width:28px;height:28px;background:var(--blue-600);border-radius:6px;display:flex;align-items:center;justify-content:center}
            .footer-logo-icon i{font-size:14px;color:#fff}
            .footer-logo-text{font-size:15px;font-weight:700;color:#fff}
            .footer-logo-text span{color:var(--blue-400)}
            footer p{font-size:12px;color:var(--slate-500);line-height:1.7}
            footer p a{color:var(--slate-400);text-decoration:none}
            </style>
        </head>
    <body>

        <!-- NAV -->
        <nav>
        <div class="nav-inner">
            <a href="#" class="nav-logo">
            <div class="nav-logo-icon"><i class="ti ti-chart-line" aria-hidden="true"></i></div>
            <span class="nav-logo-text">Final<span>ysis</span></span>
            </a>
            <div class="nav-links">
            <a href="#fitur">Fitur</a>
            <a href="#workflow">Alur kerja</a>
            <a href="#output">Hasil analisis</a>
            </div>
            <a href="#demo" class="nav-cta">Mulai analisis</a>
        </div>
        </nav>

        <!-- HERO -->
        <section style="background:#fff;border-bottom:1px solid var(--slate-100)">
        <div class="hero">
            <div>
            <div class="hero-badge">
                <span class="badge-dot"></span>
                Sistem agent berbasis LLM + RAG
            </div>
            <h1>Ubah laporan keuangan menjadi <span>insight strategis</span> secara otomatis.</h1>
            <p class="hero-desc">
                Finalysis mengintegrasikan 9 rasio finansial esensial, analisis Common-Size vertikal, dan DuPont Decomposition — semuanya diperkuat oleh konteks RAG dari dokumen PDF laporan keuangan asli perusahaan.
            </p>
            <div class="hero-actions">
                <a href="#demo" class="btn-primary">Buka dashboard</a>
                <a href="#workflow" class="btn-secondary">Lihat cara kerja</a>
            </div>
            </div>
            <div class="mock-wrap">
            <!-- Topbar -->
            <div class="mock-topbar">
                <div class="mock-topbar-left">
                <span class="mock-topbar-title">Dashboard</span>
                <span class="mock-topbar-sub">Analisis Laporan Keuangan Berbasis LLM + RAG</span>
                </div>
                <span class="mock-rag-badge">● RAG Active</span>
            </div>
            <!-- Layout -->
            <div class="mock-layout">
                <!-- Sidebar -->
                <div class="mock-sidebar">
                <div class="mock-sidebar-logo">
                    <div class="mock-sidebar-logo-icon"><i class="ti ti-chart-line" aria-hidden="true"></i></div>
                </div>
                <div class="mock-nav-item active">
                    <i class="ti ti-layout-dashboard" aria-hidden="true"></i>
                    <span>Dashboard</span>
                </div>
                <div class="mock-nav-item">
                    <i class="ti ti-building" aria-hidden="true"></i>
                    <span>Perusahaan</span>
                </div>
                <div class="mock-nav-item">
                    <i class="ti ti-files" aria-hidden="true"></i>
                    <span>Dokumen</span>
                </div>
                <div class="mock-nav-item">
                    <i class="ti ti-chart-bar" aria-hidden="true"></i>
                    <span>Analisis</span>
                </div>
                <div class="mock-nav-item">
                    <i class="ti ti-settings" aria-hidden="true"></i>
                    <span>Konfigurasi</span>
                </div>
                </div>
                <!-- Main content -->
                <div class="mock-content">
                <!-- Stat cards -->
                <div class="mock-stat-row">
                    <div class="mock-stat">
                    <div class="mock-stat-label">Total perusahaan</div>
                    <div class="mock-stat-num">3</div>
                    <div class="mock-stat-sub">terdaftar</div>
                    </div>
                    <div class="mock-stat">
                    <div class="mock-stat-label">Total dokumen</div>
                    <div class="mock-stat-num">5</div>
                    <div class="mock-stat-sub">4 selesai diproses</div>
                    </div>
                    <div class="mock-stat">
                    <div class="mock-stat-label">Total analisis</div>
                    <div class="mock-stat-num">3</div>
                    <div class="mock-stat-sub">laporan dianalisis</div>
                    </div>
                    <div class="mock-stat">
                    <div class="mock-stat-label">Rata-rata skor</div>
                    <div class="mock-stat-num" style="color:#34D399">81</div>
                    <div class="mock-stat-sub">kesehatan keuangan</div>
                    </div>
                </div>
                <!-- Charts row -->
                <div class="mock-charts">
                    <div class="mock-chart-card">
                    <div class="mock-chart-title">Skor kesehatan keuangan</div>
                    <div class="mock-chart-sub">Perbandingan antar perusahaan</div>
                    <div class="mock-bars">
                        <div class="mock-bar-item">
                        <div class="mock-bar-fill" style="height:42px;background:#1D9E75"></div>
                        <span class="mock-bar-label">Maju Bersama</span>
                        </div>
                        <div class="mock-bar-item">
                        <div class="mock-bar-fill" style="height:38px;background:#1D9E75"></div>
                        <span class="mock-bar-label">Tek. Nusantara</span>
                        </div>
                        <div class="mock-bar-item">
                        <div class="mock-bar-fill" style="height:10px;background:#2A3A55"></div>
                        <span class="mock-bar-label">Energi Mandiri</span>
                        </div>
                    </div>
                    </div>
                    <div class="mock-chart-card">
                    <div class="mock-chart-title">Tren aktivitas</div>
                    <div class="mock-chart-sub">6 bulan terakhir</div>
                    <svg class="mock-line-svg" viewBox="0 0 120 52" preserveAspectRatio="none">
                        <polyline points="0,38 20,32 40,35 60,20 80,16 100,18 120,10" fill="none" stroke="#60A5FA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="0,42 20,40 40,38 60,36 80,30 100,32 120,28" fill="none" stroke="#34D399" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    </div>
                </div>
                <!-- Bottom row -->
                <div class="mock-bottom">
                    <div class="mock-list-card">
                    <div class="mock-list-header">
                        <span class="mock-list-title">Analisis terbaru</span>
                        <span class="mock-list-link">Lihat semua →</span>
                    </div>
                    <div class="mock-list-item">
                        <div class="mock-list-item-left">
                        <span class="mock-list-item-name">PT Teknologi Nusantara</span>
                        <span class="mock-list-item-sub">FY 2024 · 2025-01-21</span>
                        </div>
                        <span class="mock-status sehat">● Sehat 85</span>
                    </div>
                    <div class="mock-list-item">
                        <div class="mock-list-item-left">
                        <span class="mock-list-item-name">PT Maju Bersama Tbk</span>
                        <span class="mock-list-item-sub">Q4 2024 · 2025-01-11</span>
                        </div>
                        <span class="mock-status waspada">● Waspada 78</span>
                    </div>
                    <div class="mock-list-item">
                        <div class="mock-list-item-left">
                        <span class="mock-list-item-name">PT Teknologi Nusantara</span>
                        <span class="mock-list-item-sub">S1 2024 · 2024-07-31</span>
                        </div>
                        <span class="mock-status sehat">● Sehat 81</span>
                    </div>
                    </div>
                    <div class="mock-list-card">
                    <div class="mock-list-header">
                        <span class="mock-list-title">Perusahaan</span>
                        <span class="mock-list-link">Kelola →</span>
                    </div>
                    <div class="mock-company-item">
                        <div>
                        <div class="mock-company-name-text">PT Maju Bersama Tbk</div>
                        <div class="mock-company-docs">2 dokumen</div>
                        </div>
                        <span class="mock-industry-badge mfr">Manufaktur</span>
                    </div>
                    <div class="mock-company-item">
                        <div>
                        <div class="mock-company-name-text">PT Teknologi Nusantara</div>
                        <div class="mock-company-docs">2 dokumen</div>
                        </div>
                        <span class="mock-industry-badge tek">Teknologi</span>
                    </div>
                    <div class="mock-company-item">
                        <div>
                        <div class="mock-company-name-text">PT Energi Mandiri Tbk</div>
                        <div class="mock-company-docs">1 dokumen</div>
                        </div>
                        <span class="mock-industry-badge erg">Energi</span>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>
        </section>

        <!-- STATS -->
        <div class="stats-strip">
        <div class="stats-inner">
            <div><div class="stat-num">9</div><div class="stat-label">Rasio finansial dihitung otomatis</div></div>
            <div><div class="stat-num">Agent</div><div class="stat-label">Agen spesialis </div></div>
            <div><div class="stat-num">3</div><div class="stat-label">Metode analisis mendalam</div></div>
            <div><div class="stat-num">PDF</div><div class="stat-label">Sumber dokumen langsung perusahaan</div></div>
        </div>
        </div>

        <!-- FITUR -->
        <section id="fitur">
        <div class="section">
            <div class="section-head">
            <div class="section-tag">Cakupan sistem</div>
            <div class="section-title">9 metrik utama,<br>3 metode analisis mendalam</div>
            <div class="section-sub">Finalysis menghitung seluruh rasio standar akuntansi korporat secara otomatis dari data yang diekstrak dokumen PDF laporan keuangan perusahaan.</div>
            </div>
            <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon fi-blue"><i class="ti ti-wallet" aria-hidden="true"></i></div>
                <h3>Rasio likuiditas</h3>
                <p>Menilai kesiapan kas jangka pendek perusahaan dalam memenuhi kewajiban yang jatuh tempo.</p>
                <div class="feature-tags">
                <span class="feature-tag">Current</span>
                <span class="feature-tag">Quick</span>
                <span class="feature-tag">Cash</span>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi-green"><i class="ti ti-trending-up" aria-hidden="true"></i></div>
                <h3>Rasio profitabilitas</h3>
                <p>Mengukur efektivitas perusahaan dalam menghasilkan laba dari pendapatan, aset, dan ekuitas.</p>
                <div class="feature-tags">
                <span class="feature-tag">NPM</span>
                <span class="feature-tag">ROA</span>
                <span class="feature-tag">ROE</span>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi-amber"><i class="ti ti-shield" aria-hidden="true"></i></div>
                <h3>Rasio solvabilitas</h3>
                <p>Mendeteksi risiko struktur permodalan jangka panjang dan ketahanan perusahaan terhadap kewajiban.</p>
                <div class="feature-tags">
                <span class="feature-tag">DER</span>
                <span class="feature-tag">DAR</span>
                </div>
            </div>
            <div class="feature-card">
                <div class="feature-icon fi-violet"><i class="ti ti-refresh" aria-hidden="true"></i></div>
                <h3>Rasio aktivitas</h3>
                <p>Mengevaluasi efisiensi pemanfaatan aset perusahaan dalam menghasilkan pendapatan operasional.</p>
                <div class="feature-tags">
                <span class="feature-tag">TATO</span>
                </div>
            </div>
            </div>

            <!-- Metode analisis 3 card -->
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:20px">
            <div class="feature-card" style="border-color:var(--blue-200);background:var(--blue-50)">
                <div class="feature-icon fi-blue"><i class="ti ti-layout-columns" aria-hidden="true"></i></div>
                <h3 style="color:var(--blue-800)">Common-size analysis</h3>
                <p style="color:var(--blue-700)">Menyajikan setiap pos laporan keuangan sebagai persentase terhadap nilai dasar — Revenue untuk laba rugi, Total Aset untuk neraca — agar struktur biaya dan komposisi aset terlihat proporsional lintas periode.</p>
            </div>
            <div class="feature-card" style="border-color:var(--blue-200);background:var(--blue-50)">
                <div class="feature-icon fi-blue"><i class="ti ti-chart-dots" aria-hidden="true"></i></div>
                <h3 style="color:var(--blue-800)">DuPont decomposition</h3>
                <p style="color:var(--blue-700)">Mengurai ROE menjadi tiga komponen (NPM × Asset Turnover × Financial Leverage) untuk mengidentifikasi faktor pendorong utama — apakah margin, efisiensi aset, atau leverage yang mendominasi.</p>
            </div>
            <div class="feature-card" style="border-color:var(--blue-200);background:var(--blue-50)">
                <div class="feature-icon fi-blue"><i class="ti ti-arrow-up-right" aria-hidden="true"></i></div>
                <h3 style="color:var(--blue-800)">Trend analysis</h3>
                <p style="color:var(--blue-700)">Membandingkan nilai rasio antar periode (YoY) untuk mengidentifikasi arah perubahan — membaik, memburuk, atau stabil — sehingga momentum kinerja perusahaan terlihat jelas.</p>
            </div>
            </div>
        </div>
        </section>



        <!-- WORKFLOW -->
        <section id="workflow">
        <div class="workflow-section">
            <div class="section-head" style="text-align:center;max-width:540px;margin:0 auto 0">
            <div class="section-tag">Alur kerja sistem</div>
            <div class="section-title">Dari PDF ke insight,<br>dalam satu alur otomatis</div>
            </div>
            <div class="workflow-steps">
            <div class="workflow-step">
                <div class="step-num s1"><i class="ti ti-upload" aria-hidden="true"></i></div>
                <div class="step-title">Upload PDF</div>
                <p class="step-desc">Tim keuangan mengunggah dokumen laporan keuangan perusahaan</p>
            </div>
            <div class="workflow-step">
                <div class="step-num s2"><i class="ti ti-file-text" aria-hidden="true"></i></div>
                <div class="step-title">Parsing & chunking</div>
                <p class="step-desc">Docling mengekstrak dan memecah dokumen menjadi chunk terstruktur</p>
            </div>
            <div class="workflow-step">
                <div class="step-num s3"><i class="ti ti-database" aria-hidden="true"></i></div>
                <div class="step-title">Embedding & vector store</div>
                <p class="step-desc">Chunk diubah menjadi vektor dan disimpan untuk pencarian semantik</p>
            </div>
            <div class="workflow-step">
                <div class="step-num s4"><i class="ti ti-cpu" aria-hidden="true"></i></div>
                <div class="step-title">Retrieval & generate</div>
                <p class="step-desc">Agen mengambil konteks relevan dan menghasilkan narasi analisis</p>
            </div>
            <div class="workflow-step">
                <div class="step-num s5"><i class="ti ti-report" aria-hidden="true"></i></div>
                <div class="step-title">Laporan PDF</div>
                <p class="step-desc">Hasil analisis laporan keuangan tersedia sebagai laporan yang dapat diunduh</p>
            </div>
            </div>
        </div>
        </section>

        <!-- OUTPUT -->
        <div id="output" class="output-section">
        <div class="output-inner">
            <div class="section-head" style="text-align:center;max-width:540px;margin:0 auto 0">
            <div class="section-tag">Hasil analisis</div>
            <div class="section-title">Apa yang dihasilkan<br>sistem ini</div>
            </div>
            <div class="output-grid">
            <div class="output-card">
                <div class="output-icon"><i class="ti ti-calculator" aria-hidden="true"></i></div>
                <h3>9 rasio keuangan terkomputasi</h3>
                <p>Current, Quick, Cash Ratio, NPM, ROA, ROE, DER, DAR, dan TATO — dihitung otomatis dari data ekstraksi PDF, lengkap dengan nilai benchmark referensi per rasio.</p>
            </div>
            <div class="output-card">
                <div class="output-icon"><i class="ti ti-text-size" aria-hidden="true"></i></div>
                <h3>Narasi analisis kontekstual</h3>
                <p>Setiap rasio dijelaskan dalam 4 lapis: angka dan cara hitung, perbandingan benchmark, implikasi bagi perusahaan, dan rekomendasi konkret untuk manajemen.</p>
            </div>
            <div class="output-card">
                <div class="output-icon"><i class="ti ti-chart-bar" aria-hidden="true"></i></div>
                <h3>Visualisasi data finansial</h3>
                <p>Grafik komparatif, waterfall chart laba rugi, stacked balance sheet, dan line chart tren rasio — semua tersaji dalam satu dashboard terintegrasi.</p>
            </div>
            <div class="output-card">
                <div class="output-icon"><i class="ti ti-git-compare" aria-hidden="true"></i></div>
                <h3>Analisis tren antar periode</h3>
                <p>Perbandingan rasio YoY dengan tabel arah perubahan (membaik/memburuk/stabil) dan persentase delta — tersedia jika data lebih dari satu periode diunggah.</p>
            </div>
            <div class="output-card">
                <div class="output-icon"><i class="ti ti-hierarchy" aria-hidden="true"></i></div>
                <h3>DuPont decomposition</h3>
                <p>Dekomposisi ROE menjadi tiga komponen pendorong — NPM, Asset Turnover, dan Financial Leverage — dengan narasi faktor mana yang paling dominan.</p>
            </div>
            <div class="output-card">
                <div class="output-icon"><i class="ti ti-file-export" aria-hidden="true"></i></div>
                <h3>Laporan PDF siap unduh</h3>
                <p>Seluruh hasil analisis dikemas dalam laporan PDF terstruktur lengkap dengan tabel data, grafik, insight per bagian, dan kesimpulan eksekutif.</p>
            </div>
            </div>
        </div>
        </div>

        <!-- FOOTER -->
        <footer>
        <div class="footer-logo">
            <div class="footer-logo-icon"><i class="ti ti-chart-line" aria-hidden="true"></i></div>
            <span class="footer-logo-text">Final<span>ysis</span></span>
        </div>
        </footer>

    </body>
</html>