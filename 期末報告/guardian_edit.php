<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guardian') {
    header("Location: login.php");
    exit;
}

$guardian_id   = $_SESSION['user_id'];
$guardian_name = $_SESSION['username'];

try {
    $stmt = $pdo->prepare("SELECT patient_id FROM users WHERE id = ?");
    $stmt->execute([$guardian_id]);
    $user_info  = $stmt->fetch();
    $patient_id = $user_info['patient_id'] ?? null;

    $patient_name     = null;
    $medication_plans = [];

    if ($patient_id) {
        $stmt_p = $pdo->prepare("SELECT username FROM users WHERE id = ? AND role = 'patient'");
        $stmt_p->execute([$patient_id]);
        $p_info = $stmt_p->fetch();
        if ($p_info) { $patient_name = $p_info['username']; }

        $sql_plans = "SELECT p.*, m.name AS medicine_name 
                      FROM medication_plan p
                      JOIN medicine_db m ON p.drug_id = m.drug_id
                      WHERE p.user_id = ?";
        $stmt_plans = $pdo->prepare($sql_plans);
        $stmt_plans->execute([$patient_id]);
        $medication_plans = $stmt_plans->fetchAll();
    }

    $stmt_med    = $pdo->query("SELECT * FROM medicine_db");
    $all_medicines = $stmt_med->fetchAll();

} catch (Exception $e) {
    echo "<div style='color:red; padding:20px;'>系統錯誤：" . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}

$low_count = 0;
foreach ($medication_plans as $p) {
    if ($p['remaining_qty'] <= $p['alert_threshold']) $low_count++;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯模式 - 智齡藥箱</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', 'Noto Sans TC', sans-serif;
            background: #020810;
            color: #E2E8F0;
            min-height: 100vh;
        }

        /* ── 動態背景 ── */
        .bg-canvas {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .beam {
            position: absolute;
            width: 1px;
            background: linear-gradient(180deg, transparent 0%, rgba(56,139,253,0.5) 40%, rgba(167,139,250,0.3) 70%, transparent 100%);
            animation: beam-fall linear infinite;
            opacity: 0;
        }
        @keyframes beam-fall {
            0%   { transform: translateY(-100%); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 0.6; }
            100% { transform: translateY(110vh); opacity: 0; }
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            animation: orb-drift ease-in-out infinite alternate;
        }
        @keyframes orb-drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(var(--dx), var(--dy)) scale(var(--ds)); }
        }

        .bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.022) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.022) 1px, transparent 1px);
            background-size: 44px 44px;
        }

        .bg-top-glow {
            position: absolute;
            top: -120px; left: 50%;
            transform: translateX(-50%);
            width: 900px; height: 300px;
            background: radial-gradient(ellipse, rgba(37,99,235,0.18) 0%, transparent 70%);
        }

        /* ── 頁面主體 ── */
        .page {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 0 auto;
            padding: 1.75rem 1.25rem 3rem;
        }

        /* ── 頂部導覽 ── */
        .top-bar {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 18px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 16px;
            margin-bottom: 20px;
            backdrop-filter: blur(14px);
            position: relative; overflow: hidden;
        }
        .top-bar::before {
            content: '';
            position: absolute;
            top: 0; left: 20px; right: 20px; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
        }
        .logo-mark {
            width: 40px; height: 40px; border-radius: 10px;
            background: linear-gradient(135deg, #2563EB, #4F46E5);
            box-shadow: 0 4px 16px rgba(37,99,235,0.40);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .logo-mark i { font-size: 20px; color: #fff; }
        .bar-brand-name { font-size: 14px; font-weight: 700; color: #F1F5F9; }
        .bar-brand-sub  { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #60A5FA; margin-top: 2px; }
        .bar-right { margin-left: auto; display: flex; align-items: center; gap: 10px; }

        .btn-back {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 600; font-family: inherit;
            color: #64748B; text-decoration: none;
            padding: 7px 14px; border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.04);
            transition: all 0.18s;
        }
        .btn-back:hover { color: #94A3B8; background: rgba(255,255,255,0.07); }
        .btn-back i { font-size: 14px; }

        .mode-badge {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 700; letter-spacing: 0.04em;
            color: #34D399;
            background: rgba(16,185,129,0.10);
            border: 1px solid rgba(52,211,153,0.25);
            border-radius: 99px;
            padding: 6px 14px;
        }
        .mode-badge i { font-size: 13px; }

        /* ── 長輩資訊橫幅 ── */
        .patient-banner {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 18px;
            padding: 1.25rem 1.75rem;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 1rem;
            backdrop-filter: blur(14px);
            position: relative; overflow: hidden;
        }
        .patient-banner::before {
            content: '';
            position: absolute;
            top: 0; left: 20px; right: 20px; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(96,165,250,0.25), transparent);
        }
        .patient-avatar {
            width: 52px; height: 52px; border-radius: 50%;
            background: linear-gradient(135deg, #2563EB, #4F46E5);
            box-shadow: 0 4px 18px rgba(37,99,235,0.35);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .patient-avatar i { font-size: 26px; color: #fff; }
        .patient-info { flex: 1; }
        .patient-name { font-size: 18px; font-weight: 700; color: #F1F5F9; }
        .patient-sub  { font-size: 12px; color: #475569; margin-top: 3px; }
        .patient-stats { display: flex; gap: 10px; }
        .pstat {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 10px 18px;
            text-align: center;
        }
        .pstat-num   { font-size: 20px; font-weight: 800; color: #F1F5F9; letter-spacing: -0.02em; }
        .pstat-num.warn { color: #F87171; }
        .pstat-label { font-size: 11px; color: #475569; margin-top: 2px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; }

        /* ── 雙欄版面 ── */
        .main-grid {
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            gap: 16px;
            align-items: start;
        }
        @media (max-width: 850px) { .main-grid { grid-template-columns: 1fr; } }

        /* ── 內容卡片 ── */
        .section-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 18px;
            overflow: hidden;
            backdrop-filter: blur(12px);
        }
        .section-header {
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            display: flex; align-items: center; gap: 8px;
        }
        .section-header i { font-size: 17px; }
        .section-header .title { font-size: 13px; font-weight: 700; letter-spacing: 0.01em; }
        .section-header.blue i,
        .section-header.blue .title { color: #60A5FA; }
        .section-header.green i,
        .section-header.green .title { color: #34D399; }

        /* ── 表格 ── */
        .plan-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .plan-table th {
            font-size: 11px; font-weight: 700; color: #334155;
            padding: 10px 20px; text-align: left;
            text-transform: uppercase; letter-spacing: 0.06em;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            background: rgba(255,255,255,0.02);
        }
        .plan-table td {
            padding: 13px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #CBD5E1; vertical-align: middle;
        }
        .plan-table tr:last-child td { border-bottom: none; }
        .plan-table tbody tr:hover td { background: rgba(255,255,255,0.03); }

        .med-name { font-weight: 600; font-size: 13px; color: #E2E8F0; }

        .qty-bar-wrap { display: flex; align-items: center; gap: 10px; }
        .qty-bar-bg {
            flex: 1; height: 4px; max-width: 90px;
            background: rgba(255,255,255,0.08); border-radius: 99px; overflow: hidden;
        }
        .qty-bar { height: 100%; border-radius: 99px; }
        .qty-bar.ok   { background: linear-gradient(90deg, #10B981, #34D399); }
        .qty-bar.warn { background: linear-gradient(90deg, #DC2626, #F87171); }
        .qty-num { font-size: 13px; font-weight: 700; }
        .qty-num.ok   { color: #34D399; }
        .qty-num.warn { color: #F87171; }

        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 11px; font-weight: 700; padding: 4px 10px;
            border-radius: 99px; border: 1px solid; letter-spacing: 0.02em;
        }
        .badge i { font-size: 11px; }
        .badge.danger  { background: rgba(153,60,29,0.16); color: #FCA5A5; border-color: rgba(248,113,113,0.25); }
        .badge.success { background: rgba(16,185,129,0.12); color: #6EE7B7; border-color: rgba(52,211,153,0.25); }

        .empty-state {
            text-align: center; padding: 3rem 1rem;
            color: #334155; font-size: 14px;
        }
        .empty-state i { font-size: 36px; color: #1E293B; display: block; margin-bottom: 12px; }

        /* ── 表單 ── */
        .card-body { padding: 1.25rem 1.5rem; }

        .form-group { margin-bottom: 1rem; }
        .form-group label {
            display: flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 600; letter-spacing: 0.03em;
            color: #64748B;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .form-group label i { font-size: 13px; }

        .form-control {
            width: 100%; padding: 10px 13px;
            font-size: 13px; font-family: inherit;
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
            color: #E2E8F0;
            outline: none;
            transition: border-color 0.15s, background 0.15s;
            appearance: none; -webkit-appearance: none;
        }
        select.form-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }
        select.form-control option { background: #0F172A; color: #E2E8F0; }
        .form-control:focus { border-color: rgba(52,211,153,0.45); background: rgba(255,255,255,0.07); }
        .form-control::placeholder { color: #334155; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        .btn-submit {
            width: 100%; padding: 12px;
            font-size: 13px; font-weight: 700; font-family: inherit;
            background: rgba(16,185,129,0.14);
            color: #34D399;
            border: 1px solid rgba(52,211,153,0.30);
            border-radius: 10px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 7px;
            margin-top: 0.5rem;
            letter-spacing: 0.02em;
            transition: background 0.18s, border-color 0.18s, transform 0.1s;
        }
        .btn-submit:hover  { background: rgba(16,185,129,0.22); border-color: rgba(52,211,153,0.50); }
        .btn-submit:active { transform: scale(0.98); }
        .btn-submit i { font-size: 17px; }

        @media (max-width: 600px) {
            .patient-stats { display: none; }
            .plan-table th, .plan-table td { padding: 10px 12px; }
        }
    </style>
</head>
<body>

<!-- 動態背景 -->
<div class="bg-canvas">
    <div class="bg-grid"></div>
    <div class="bg-top-glow"></div>

    <div class="orb" style="width:520px;height:520px;top:-80px;right:-100px;background:rgba(37,99,235,0.12);--dx:40px;--dy:30px;--ds:1.1;animation-duration:18s;"></div>
    <div class="orb" style="width:380px;height:380px;bottom:-60px;left:-80px;background:rgba(124,58,237,0.10);--dx:-30px;--dy:-25px;--ds:0.9;animation-duration:22s;"></div>
    <div class="orb" style="width:260px;height:260px;top:45%;left:55%;background:rgba(16,185,129,0.07);--dx:20px;--dy:-40px;--ds:1.15;animation-duration:16s;"></div>

    <div class="beam" style="left:12%;height:35vh;animation-duration:6s;animation-delay:0s;"></div>
    <div class="beam" style="left:27%;height:28vh;animation-duration:8s;animation-delay:2.5s;background:linear-gradient(180deg,transparent,rgba(167,139,250,0.45),transparent);"></div>
    <div class="beam" style="left:51%;height:40vh;animation-duration:7s;animation-delay:1s;"></div>
    <div class="beam" style="left:68%;height:22vh;animation-duration:9s;animation-delay:4s;background:linear-gradient(180deg,transparent,rgba(52,211,153,0.35),transparent);"></div>
    <div class="beam" style="left:83%;height:32vh;animation-duration:6.5s;animation-delay:3s;"></div>
</div>

<div class="page">

    <!-- 頂部導覽 -->
    <div class="top-bar">
        <div class="logo-mark"><i class="ti ti-pill"></i></div>
        <div>
            <div class="bar-brand-name">智齡藥箱 AI MedBox</div>
            <div class="bar-brand-sub">家屬管理端</div>
        </div>
        <div class="bar-right">
            <a href="guardian_hub.php" class="btn-back">
                <i class="ti ti-arrow-left"></i> 回選擇模式
            </a>
            <span class="mode-badge">
                <i class="ti ti-edit-circle"></i> 編輯模式
            </span>
        </div>
    </div>

    <!-- 長輩資訊橫幅 -->
    <div class="patient-banner">
        <div class="patient-avatar"><i class="ti ti-user-heart"></i></div>
        <div class="patient-info">
            <div class="patient-name"><?php echo htmlspecialchars($patient_name ?? '未綁定長輩'); ?> 的服藥管理</div>
            <div class="patient-sub">目前共有 <?php echo count($medication_plans); ?> 項服藥計畫</div>
        </div>
        <div class="patient-stats">
            <div class="pstat">
                <div class="pstat-num"><?php echo count($medication_plans); ?></div>
                <div class="pstat-label">計畫總數</div>
            </div>
            <div class="pstat">
                <div class="pstat-num <?php echo $low_count > 0 ? 'warn' : ''; ?>">
                    <?php echo $low_count; ?>
                </div>
                <div class="pstat-label">庫存警示</div>
            </div>
        </div>
    </div>

    <!-- 主體雙欄 -->
    <div class="main-grid">

        <!-- 左：目前計畫列表 -->
        <div class="section-card">
            <div class="section-header blue">
                <i class="ti ti-list-check"></i>
                <span class="title">目前服藥計畫與庫存</span>
            </div>
            <?php if (empty($medication_plans)): ?>
                <div class="empty-state">
                    <i class="ti ti-pill-off"></i>
                    目前尚無計畫，請由右側表單新增。
                </div>
            <?php else: ?>
                <table class="plan-table">
                    <thead>
                        <tr>
                            <th>藥品名稱</th>
                            <th>服用頻率</th>
                            <th>剩餘庫存</th>
                            <th>狀態</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medication_plans as $plan):
                            $is_low    = $plan['remaining_qty'] <= $plan['alert_threshold'];
                            $max_qty   = $plan['alert_threshold'] * 5;
                            $bar_pct   = min(100, round(($plan['remaining_qty'] / max($max_qty, 1)) * 100));
                            $bar_class = $is_low ? 'warn' : 'ok';
                        ?>
                        <tr>
                            <td><div class="med-name"><?php echo htmlspecialchars($plan['medicine_name']); ?></div></td>
                            <td><?php echo htmlspecialchars($plan['daily_dosage']); ?></td>
                            <td>
                                <div class="qty-bar-wrap">
                                    <div class="qty-bar-bg">
                                        <div class="qty-bar <?php echo $bar_class; ?>" style="width:<?php echo $bar_pct; ?>%"></div>
                                    </div>
                                    <span class="qty-num <?php echo $bar_class; ?>"><?php echo $plan['remaining_qty']; ?> 顆</span>
                                </div>
                            </td>
                            <td>
                                <?php if ($is_low): ?>
                                    <span class="badge danger"><i class="ti ti-alert-triangle"></i>過低</span>
                                <?php else: ?>
                                    <span class="badge success"><i class="ti ti-circle-check"></i>充足</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- 右：新增計畫表單 -->
        <div class="section-card">
            <div class="section-header green">
                <i class="ti ti-square-plus"></i>
                <span class="title">新增服藥計畫</span>
            </div>
            <div class="card-body">
                <form action="add_plan_process.php" method="POST">
                    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">

                    <div class="form-group">
                        <label><i class="ti ti-pill"></i> 選擇藥品名稱</label>
                        <select name="drug_id" class="form-control" required>
                            <option value="">── 請選擇藥庫中的藥品 ──</option>
                            <?php foreach ($all_medicines as $medicine): ?>
                                <option value="<?php echo $medicine['drug_id']; ?>">
                                    <?php echo htmlspecialchars($medicine['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="ti ti-clock"></i> 服用頻率</label>
                        <input type="text" name="daily_dosage" class="form-control" placeholder="如：一天兩次、飯後服用" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="ti ti-hash"></i> 單次劑量（顆）</label>
                            <input type="number" name="once_qty" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="form-group">
                            <label><i class="ti ti-package"></i> 放入總量（顆）</label>
                            <input type="number" name="remaining_qty" class="form-control" placeholder="如：30" min="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="ti ti-alert-triangle"></i> 低庫存警告值（顆）</label>
                        <input type="number" name="alert_threshold" class="form-control" value="5" min="0" required>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="ti ti-device-floppy"></i> 確認儲存並同步
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

</body>
</html>