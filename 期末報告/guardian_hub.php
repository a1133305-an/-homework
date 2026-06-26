<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guardian') {
    header("Location: login.php");
    exit;
}
$guardian_name = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>選擇模式 - 智齡藥箱</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', 'Noto Sans TC', sans-serif;
            background: #04090F;
            color: #E8EEF5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem;
            position: relative;
            overflow-x: hidden;
        }

        /* 純色背景裝飾光暈 */
        .glow-1 {
            position: fixed;
            width: 700px; height: 700px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.09) 0%, transparent 65%);
            top: -200px; right: -150px;
            pointer-events: none; z-index: 0;
        }
        .glow-2 {
            position: fixed;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.07) 0%, transparent 65%);
            bottom: -150px; left: -100px;
            pointer-events: none; z-index: 0;
        }
        /* 細網格紋路（純 CSS，增加科技感） */
        .grid-texture {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none; z-index: 0;
        }

        .page {
            position: relative; z-index: 1;
            max-width: 860px; width: 100%;
        }

        /* 頂部導覽列 */
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            margin-bottom: 48px;
            backdrop-filter: blur(12px);
        }
        .top-bar-left { display: flex; align-items: center; gap: 12px; }
        .logo-mark {
            width: 40px; height: 40px; border-radius: 10px;
            background: linear-gradient(135deg, #2563EB, #4F46E5);
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.35);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .logo-mark i { font-size: 20px; color: #fff; }
        .brand-name { font-size: 14px; font-weight: 700; color: #F1F5F9; }
        .brand-sub  { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #60A5FA; margin-top: 2px; }

        /* 登出按鈕 */
        .btn-logout {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 600; font-family: inherit;
            color: #F87171;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid rgba(248, 113, 113, 0.22);
            background: rgba(248, 113, 113, 0.08);
            transition: all 0.18s ease;
        }
        .btn-logout:hover {
            background: rgba(248, 113, 113, 0.16);
            border-color: rgba(248, 113, 113, 0.38);
        }
        .btn-logout i { font-size: 15px; }

        /* 歡迎區 */
        .header-text {
            text-align: center;
            margin-bottom: 44px;
        }
        .header-eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #60A5FA;
            margin-bottom: 12px;
        }
        .header-text h1 {
            font-size: clamp(1.8rem, 4vw, 2.4rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #F1F5F9;
            margin-bottom: 10px;
        }
        .header-text h1 span {
            background: linear-gradient(90deg, #60A5FA, #A78BFA);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .header-text p {
            font-size: 15px;
            color: #475569;
        }

        /* 分隔線 */
        .divider {
            height: 1px;
            background: rgba(255,255,255,0.06);
            margin: 0 auto 44px;
            max-width: 280px;
        }

        /* 模式卡片 */
        .cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 32px;
        }
        @media (max-width: 560px) { .cards-grid { grid-template-columns: 1fr; } }

        .mode-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 22px;
            padding: 40px 28px 32px;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow: hidden;
            transition: transform 0.25s ease, border-color 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
        }
        /* 卡片頂部光澤 */
        .mode-card::before {
            content: '';
            position: absolute;
            top: 0; left: 20px; right: 20px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.14), transparent);
        }
        .mode-card:hover {
            transform: translateY(-6px);
            background: rgba(255,255,255,0.07);
            box-shadow: 0 24px 60px rgba(0,0,0,0.35);
        }
        .mode-card.blue:hover  { border-color: rgba(96, 165, 250, 0.40); box-shadow: 0 24px 60px rgba(37,99,235,0.18); }
        .mode-card.green:hover { border-color: rgba(52, 211, 153, 0.35); box-shadow: 0 24px 60px rgba(16,185,129,0.14); }
        .mode-card:active { transform: translateY(-2px); }

        /* 圖示圓圈 */
        .icon-wrap {
            width: 88px; height: 88px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 24px;
            border: 1px solid;
        }
        .icon-wrap i { font-size: 40px; }
        .icon-blue  {
            background: rgba(37, 99, 235, 0.14);
            border-color: rgba(96, 165, 250, 0.25);
            color: #60A5FA;
        }
        .icon-green {
            background: rgba(16, 185, 129, 0.12);
            border-color: rgba(52, 211, 153, 0.22);
            color: #34D399;
        }

        .mode-title {
            font-size: 20px;
            font-weight: 700;
            color: #F1F5F9;
            letter-spacing: -0.01em;
            margin-bottom: 10px;
        }
        .mode-desc {
            font-size: 14px;
            color: #475569;
            line-height: 1.7;
            max-width: 240px;
        }

        .mode-tag {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 600;
            padding: 6px 16px;
            border-radius: 999px;
            margin-top: 24px;
            border: 1px solid;
            letter-spacing: 0.02em;
        }
        .mode-tag i { font-size: 13px; }
        .tag-blue {
            background: rgba(37, 99, 235, 0.12);
            color: #93C5FD;
            border-color: rgba(37, 99, 235, 0.28);
        }
        .tag-green {
            background: rgba(16, 185, 129, 0.10);
            color: #6EE7B7;
            border-color: rgba(16, 185, 129, 0.25);
        }

        /* 箭頭指示 */
        .card-arrow {
            position: absolute;
            bottom: 20px; right: 22px;
            font-size: 18px;
            color: rgba(255,255,255,0.12);
            transition: color 0.2s, transform 0.2s;
        }
        .mode-card:hover .card-arrow { color: rgba(255,255,255,0.35); transform: translate(2px, -2px); }
    </style>
</head>
<body>

    <div class="glow-1"></div>
    <div class="glow-2"></div>
    <div class="grid-texture"></div>

    <div class="page">

        <!-- 頂部導覽列 -->
        <div class="top-bar">
            <div class="top-bar-left">
                <div class="logo-mark"><i class="ti ti-pill"></i></div>
                <div>
                    <div class="brand-name">智齡藥箱 AI MedBox</div>
                    <div class="brand-sub">家屬管理端</div>
                </div>
            </div>
            <a href="logout.php" class="btn-logout">
                <i class="ti ti-logout"></i> 登出
            </a>
        </div>

        <!-- 歡迎區 -->
        <div class="header-text">
            <p class="header-eyebrow">家屬操作中心</p>
            <h1>歡迎回來，<span><?php echo htmlspecialchars($guardian_name); ?></span></h1>
            <p>請選擇您現在要進行的操作模式</p>
        </div>

        <div class="divider"></div>

        <!-- 模式卡片 -->
        <div class="cards-grid">

            <a href="guardian.php" class="mode-card blue">
                <div class="icon-wrap icon-blue">
                    <i class="ti ti-heart-rate-monitor"></i>
                </div>
                <div class="mode-title">監控模式</div>
                <div class="mode-desc">單純查看長輩的服藥狀況、剩餘庫存，不進行任何資料修改。</div>
                <span class="mode-tag tag-blue">
                    <i class="ti ti-eye"></i> 查看狀況
                </span>
                <i class="ti ti-arrow-up-right card-arrow"></i>
            </a>

            <a href="guardian_edit.php" class="mode-card green">
                <div class="icon-wrap icon-green">
                    <i class="ti ti-edit-circle"></i>
                </div>
                <div class="mode-title">編輯模式</div>
                <div class="mode-desc">為長輩新增新的服藥計畫、修改現有藥單與設定庫存警告。</div>
                <span class="mode-tag tag-green">
                    <i class="ti ti-pencil"></i> 編輯藥單
                </span>
                <i class="ti ti-arrow-up-right card-arrow"></i>
            </a>

        </div>

    </div>

</body>
</html>