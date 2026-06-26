<!DOCTYPE html>
<html lang="zh-TW" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>智齡藥箱 AI MedBox - 智慧高齡照護的首選</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', 'Noto Sans TC', sans-serif;
            background: #04090F;
            color: #E8EEF5;
            overflow-x: hidden;
        }

        /* ── NAV ── */
        .glass-nav {
            background: rgba(4, 9, 15, 0.55);
            backdrop-filter: blur(20px) saturate(1.4);
            -webkit-backdrop-filter: blur(20px) saturate(1.4);
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }

        /* ── HERO ── */
        .hero-bg {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            overflow: hidden;
        }
        .hero-photo {
            position: absolute;
            inset: 0;
            background-image: url('https://images.unsplash.com/photo-1631549916768-4119b2e5f926?auto=format&fit=crop&w=1800&q=80');
            background-size: cover;
            background-position: center 30%;
            filter: brightness(0.35) saturate(0.7);
            z-index: 0;
        }
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                160deg,
                rgba(4, 9, 15, 0.55) 0%,
                rgba(10, 25, 60, 0.45) 50%,
                rgba(4, 9, 15, 0.80) 100%
            );
            z-index: 1;
        }
        .hero-glow {
            position: absolute;
            width: 700px; height: 700px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(56, 139, 253, 0.18) 0%, transparent 70%);
            top: -100px; right: -150px;
            z-index: 2;
            pointer-events: none;
        }
        .hero-content { position: relative; z-index: 3; }

        /* ── PILL BADGE ── */
        .pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 999px;
            border: 1px solid rgba(56, 139, 253, 0.35);
            background: rgba(56, 139, 253, 0.10);
            backdrop-filter: blur(8px);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.06em;
            color: #93C5FD;
            text-transform: uppercase;
        }

        /* ── HEADLINE GRADIENT ── */
        .headline-accent {
            background: linear-gradient(95deg, #60A5FA 10%, #A78BFA 60%, #38BDF8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── BUTTONS ── */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 32px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #2563EB, #4F46E5);
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.01em;
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 8px 32px rgba(37, 99, 235, 0.35);
            transition: all 0.22s ease;
            text-decoration: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 40px rgba(37, 99, 235, 0.50);
            background: linear-gradient(135deg, #3B82F6, #6D28D9);
        }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 32px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255,255,255,0.07);
            backdrop-filter: blur(12px);
            color: #CBD5E1;
            font-weight: 500;
            font-size: 15px;
            border: 1px solid rgba(255,255,255,0.12);
            transition: all 0.22s ease;
            text-decoration: none;
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.12);
            color: #fff;
            border-color: rgba(255,255,255,0.22);
        }

        /* ── HERO CARD / DASHBOARD FRAME ── */
        .hero-frame {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 24px;
            padding: 12px;
            backdrop-filter: blur(8px);
            box-shadow:
                0 0 0 1px rgba(56, 139, 253, 0.08),
                0 32px 80px rgba(0,0,0,0.5);
        }
        .hero-frame img {
            border-radius: 16px;
            display: block;
            width: 100%;
            object-fit: cover;
            height: 360px;
        }
        @media (min-width: 640px) {
            .hero-frame img { height: 480px; }
        }

        /* ── STATS STRIP ── */
        .stats-strip {
            background: rgba(255,255,255,0.03);
            border-top: 1px solid rgba(255,255,255,0.07);
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            padding: 28px 24px;
        }
        .stat-number {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(90deg, #60A5FA, #A78BFA);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stat-label { font-size: 13px; color: #64748B; font-weight: 500; }

        /* ── FEATURES ── */
        .features-bg {
            position: relative;
            overflow: hidden;
        }
        .features-photo {
            position: absolute;
            inset: 0;
            background-image: url('https://images.unsplash.com/photo-1585421514738-01798e348b17?auto=format&fit=crop&w=1800&q=80');
            background-size: cover;
            background-position: center;
            filter: brightness(0.12) saturate(0.5);
            z-index: 0;
        }
        .features-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, #04090F 0%, rgba(4,9,15,0.72) 40%, rgba(4,9,15,0.72) 60%, #04090F 100%);
            z-index: 1;
        }
        .features-content { position: relative; z-index: 2; }

        /* ── FEATURE CARDS ── */
        .feature-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 20px;
            padding: 36px 32px;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .feature-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255,255,255,0.06) 0%, transparent 60%);
            pointer-events: none;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.07);
            box-shadow: 0 24px 60px rgba(0,0,0,0.4);
        }
        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 24px;
            position: relative;
        }
        .icon-blue  { background: rgba(37,99,235,0.18);  color: #60A5FA; border: 1px solid rgba(37,99,235,0.25); }
        .icon-green { background: rgba(16,185,129,0.15); color: #34D399; border: 1px solid rgba(16,185,129,0.25); }
        .icon-violet{ background: rgba(124,58,237,0.15); color: #A78BFA; border: 1px solid rgba(124,58,237,0.25); }

        .feature-title {
            font-size: 18px;
            font-weight: 700;
            color: #F1F5F9;
            margin-bottom: 12px;
        }
        .feature-desc {
            font-size: 14px;
            line-height: 1.75;
            color: #64748B;
        }
        .feature-desc .accent { color: #60A5FA; font-weight: 600; }

        /* ── SECTION EYEBROW ── */
        .eyebrow {
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 700;
            color: #60A5FA;
            margin-bottom: 16px;
        }

        /* ── FOOTER ── */
        .footer-bar {
            border-top: 1px solid rgba(255,255,255,0.07);
            background: rgba(4,9,15,0.9);
        }

        /* ── PING ANIMATION ── */
        @keyframes ping {
            75%, 100% { transform: scale(2); opacity: 0; }
        }
        .animate-ping { animation: ping 1.5s cubic-bezier(0,0,0.2,1) infinite; }

        /* ── DIVIDER LINE ── */
        .divider { height: 1px; background: rgba(255,255,255,0.07); }
    </style>
</head>
<body>

    <!-- NAV -->
    <nav class="glass-nav fixed top-0 left-0 right-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white"
                     style="background: linear-gradient(135deg, #2563EB, #4F46E5); box-shadow: 0 4px 16px rgba(37,99,235,0.40);">
                    <i class="ti ti-pill" style="font-size:20px;"></i>
                </div>
                <div>
                    <span class="text-base font-bold tracking-tight text-white block leading-tight">智齡藥箱</span>
                    <span class="text-xs font-semibold tracking-widest uppercase block" style="color: #60A5FA;">AI MedBox</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="login.php" class="btn-primary" style="height: 42px; padding: 0 22px; font-size: 14px; border-radius: 10px;">
                    <i class="ti ti-login" style="font-size: 16px;"></i>
                    進入系統
                </a>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero-bg">
        <div class="hero-photo"></div>
        <div class="hero-overlay"></div>
        <div class="hero-glow"></div>

        <div class="hero-content w-full pt-40 pb-28 px-6">
            <div class="max-w-5xl mx-auto text-center">

                <div class="pill-badge mb-8 mx-auto" style="width: fit-content;">
                    <span style="position:relative; display:inline-flex; width:8px; height:8px;">
                        <span class="animate-ping" style="position:absolute;inset:0;border-radius:50%;background:#60A5FA;opacity:0.7;"></span>
                        <span style="position:relative;width:8px;height:8px;border-radius:50%;background:#60A5FA;display:inline-block;"></span>
                    </span>
                    2026 智慧醫療全新世代
                </div>

                <h1 style="font-size: clamp(2.2rem, 6vw, 4rem); font-weight: 800; line-height: 1.18; letter-spacing: -0.02em; color: #F8FAFC; margin-bottom: 24px;">
                    讓 AI 成為長輩的<br>
                    <span class="headline-accent">專屬用藥守護大腦</span>
                </h1>

                <p style="font-size: 17px; line-height: 1.8; color: #94A3B8; max-width: 580px; margin: 0 auto 40px;">
                    結合最新 Gemini 2.5 視覺辨識與家屬端即時連動，打造全方位的智慧服藥生態系。拍照即辨識，雙重確認扣庫存，讓遠端照護再也沒有距離。
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
                    <a href="login.php" class="btn-primary w-full sm:w-auto">
                        立即開始體驗
                        <i class="ti ti-arrow-right" style="font-size:17px;"></i>
                    </a>
                    <a href="#features" class="btn-secondary w-full sm:w-auto">
                        了解系統功能
                    </a>
                </div>

                <div class="hero-frame max-w-4xl mx-auto">
                    <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&q=80"
                         alt="Smart Healthcare Dashboard">
                </div>

            </div>
        </div>
    </section>

    <!-- STATS STRIP -->
    <div class="stats-strip">
        <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 divide-x divide-white/5">
            <div class="stat-item">
                <span class="stat-number">99.2%</span>
                <span class="stat-label">辨識準確率</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">gemini-2.5</span>
                <span class="stat-label">視覺模型驅動</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">即時</span>
                <span class="stat-label">家屬連動通報</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">零</span>
                <span class="stat-label">安裝門檻</span>
            </div>
        </div>
    </div>

    <!-- FEATURES -->
    <section id="features" class="features-bg py-28 px-6">
        <div class="features-photo"></div>
        <div class="features-overlay"></div>

        <div class="features-content max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <p class="eyebrow">系統核心優勢</p>
                <h2 style="font-size: clamp(1.8rem, 4vw, 2.6rem); font-weight: 800; color: #F1F5F9; letter-spacing: -0.02em; margin-bottom: 14px;">
                    三大模組，守護每一顆藥丸
                </h2>
                <p style="font-size: 15px; color: #64748B; max-width: 440px; margin: 0 auto; line-height: 1.7;">
                    基於真實高齡用藥痛點設計，前後端流暢對接，安全防呆面面俱到。
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="feature-card">
                    <div class="feature-icon icon-blue">
                        <i class="ti ti-scan"></i>
                    </div>
                    <h3 class="feature-title">Gemini 視覺大腦</h3>
                    <p class="feature-desc">
                        串接最新世代 <span class="accent">gemini-2.5-flash</span> 模型，長輩只需隨手拍下藥盒或鋁箔排裝，系統自動跨資料庫精準比對，去除所有文字雜質，精準辨識。
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-green">
                        <i class="ti ti-shield-check"></i>
                    </div>
                    <h3 class="feature-title">用藥安全阻斷與扣減</h3>
                    <p class="feature-desc">
                        堅持「安全審核制」。AI 負責對比並提示藥名，長輩親手吞服並點擊「我吃藥了」，方能觸發資料庫資料鏈結，自動扣減剩餘庫存並寫入歷史日誌。
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-violet">
                        <i class="ti ti-device-analytics"></i>
                    </div>
                    <h3 class="feature-title">家屬端管理面板</h3>
                    <p class="feature-desc">
                        提供直覺的時間流歷史日誌。具備低庫存紅色高亮預警、超時未服藥主動郵件通報，更支援一鍵免套件導出標準 Excel 報表，便於就醫時提供醫師參考。
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- BOTTOM CTA PHOTO STRIP -->
    <section style="position:relative; overflow:hidden; padding: 100px 24px;">
        <div style="
            position: absolute; inset: 0;
            background-image: url('https://images.unsplash.com/photo-1559757148-5c350d0d3c56?auto=format&fit=crop&w=1800&q=80');
            background-size: cover;
            background-position: center 40%;
            filter: brightness(0.18) saturate(0.6);
            z-index: 0;
        "></div>
        <div style="
            position: absolute; inset: 0;
            background: linear-gradient(180deg, #04090F 0%, rgba(4,9,15,0.5) 50%, #04090F 100%);
            z-index: 1;
        "></div>

        <div style="position:relative; z-index:2; max-width: 640px; margin: 0 auto; text-align: center;">
            <p class="eyebrow">立即加入</p>
            <h2 style="font-size: clamp(1.8rem, 4vw, 2.6rem); font-weight: 800; color: #F1F5F9; letter-spacing: -0.02em; margin-bottom: 16px;">
                智慧照護，從今天開始
            </h2>
            <p style="font-size: 15px; color: #64748B; margin-bottom: 36px; line-height: 1.75;">
                不需安裝、不需設備，一啟動即可啟動完整的智慧用藥管理系統。
            </p>
            <a href="login.php" class="btn-primary" style="margin: 0 auto;">
                立即開始體驗
                <i class="ti ti-arrow-right" style="font-size:17px;"></i>
            </a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer-bar py-10 text-center">
        <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white"
                     style="background: linear-gradient(135deg, #2563EB, #4F46E5);">
                    <i class="ti ti-pill" style="font-size: 14px;"></i>
                </div>
                <span style="font-size: 13px; color: #475569; font-weight: 500;">智齡藥箱 AI MedBox © 2026. All rights reserved.</span>
            </div>
            <div style="font-size: 12px; color: #334155;">本系統為課程期末專題，僅供學術交流與評分使用，無商業營利行為</div>
        </div>
    </footer>

</body>
</html>