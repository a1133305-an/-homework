<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guardian') {
    header("Location: login.php");
    exit;
}

$patient_id = $_SESSION['patient_id'] ?? null;

try {
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ? AND role = 'patient'");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch();

    if ($patient) {
        $patient_name = $patient['username'];

        $plan_sql = "SELECT p.*, m.name AS medicine_name 
                     FROM medication_plan p
                     JOIN medicine_db m ON p.drug_id = m.drug_id
                     WHERE p.user_id = ?";
        $stmt = $pdo->prepare($plan_sql);
        $stmt->execute([$patient_id]);
        $plans = $stmt->fetchAll();

        $log_sql = "SELECT h.*, m.name AS medicine_name 
                    FROM history_log h
                    JOIN medicine_db m ON h.drug_id = m.drug_id
                    WHERE h.user_id = ?
                    ORDER BY h.taken_time DESC";
        $stmt = $pdo->prepare($log_sql);
        $stmt->execute([$patient_id]);
        $logs = $stmt->fetchAll();
    } else {
        echo "<h2 style='color:red; text-align:center; margin-top:50px;'>此家屬目前尚未綁定任何受照護長輩，或該長輩帳號不存在。</h2>";
        exit;
    }
} catch (Exception $e) {
    echo "系統錯誤：" . $e->getMessage();
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    $filename = "智齡藥箱_" . $patient_name . "_服藥紀錄報表_" . date('Ymd') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['服藥時間', '藥品名稱', '狀態']);
    foreach ($logs as $log) {
        fputcsv($output, [$log['taken_time'], $log['medicine_name'], $log['status']]);
    }
    fclose($output);
    exit;
}

$low_stock_count = 0;
foreach ($plans as $p) {
    if ($p['remaining_qty'] <= $p['alert_threshold']) $low_stock_count++;
}
$total_logs = count($logs);

$anomaly_alert = false;
$current_hour = (int)date('H');
$today_start = date('Y-m-d 00:00:00');
$today_logs_count = 0;
foreach ($logs as $log) {
    if ($log['taken_time'] >= $today_start) $today_logs_count++;
}

if ($current_hour >= 13 && $today_logs_count === 0 && !isset($_SESSION['mail_sent_today'])) {
    $to = "guardian_email@example.com";
    $subject = "【智齡藥箱警告】長輩今日尚未服藥提醒！";
    $mail_message = "您好：\n\n系統偵測到您的受照護長輩 " . $patient_name . " 截至今日中午 13:00 為止，尚未有任何拍照辨識與服藥確認紀錄。\n\n請儘速關心長輩用藥狀況。\n\n智齡藥箱 自動監測系統";
    $headers = "From: sim_medbox@mail.com\r\nReply-To: sim_medbox@mail.com\r\nX-Mailer: PHP/" . phpversion();
    @mail($to, $subject, $mail_message, $headers);
    $_SESSION['mail_sent_today'] = true;
    $anomaly_alert = true;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>智齡藥箱 - 家屬遠端管理面板</title>
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

        /* 流動光柱 */
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

        /* 漂浮光點 */
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

        /* 超細網格 */
        .bg-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.022) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.022) 1px, transparent 1px);
            background-size: 44px 44px;
        }

        /* 頂部暈染橫帶 */
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
            max-width: 960px;
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

        .tag-patient {
            font-size: 12px; padding: 5px 14px;
            background: rgba(37,99,235,0.14);
            color: #93C5FD;
            border: 1px solid rgba(96,165,250,0.25);
            border-radius: 99px; font-weight: 600;
            display: flex; align-items: center; gap: 6px;
        }
        .tag-patient i { font-size: 13px; }

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

        /* ── 警告橫幅 ── */
        .anomaly-banner {
            background: rgba(153,60,29,0.16);
            border: 1px solid rgba(240,153,123,0.28);
            border-radius: 12px;
            padding: 12px 18px;
            color: #FCA5A5;
            font-size: 13px; font-weight: 500;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
            backdrop-filter: blur(8px);
        }
        .anomaly-banner i { font-size: 18px; color: #F87171; flex-shrink: 0; }

        /* ── 統計卡片 ── */
        .stat-row {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 18px 20px;
            backdrop-filter: blur(12px);
            position: relative; overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 12px; right: 12px; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
        }
        .stat-label {
            font-size: 11px; font-weight: 600; letter-spacing: 0.07em;
            text-transform: uppercase; color: #475569;
            margin-bottom: 10px; display: flex; align-items: center; gap: 5px;
        }
        .stat-label i { font-size: 13px; }
        .stat-value { font-size: 26px; font-weight: 800; letter-spacing: -0.02em; color: #F1F5F9; }
        .stat-value.warn { color: #F87171; }
        .stat-value.ok   { color: #34D399; }

        /* ── 內容卡片 ── */
        .section-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 18px;
            margin-bottom: 20px;
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
        .section-header.gray i { color: #64748B; }
        .section-header.gray .title { color: #94A3B8; }

        /* 匯出按鈕 */
        .btn-export {
            margin-left: auto;
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 600; font-family: inherit;
            color: #34D399; text-decoration: none;
            padding: 6px 14px; border-radius: 8px;
            border: 1px solid rgba(52,211,153,0.25);
            background: rgba(16,185,129,0.10);
            transition: all 0.18s;
        }
        .btn-export:hover { background: rgba(16,185,129,0.18); border-color: rgba(52,211,153,0.40); }
        .btn-export i { font-size: 13px; }

        /* ── 表格 ── */
        .med-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .med-table th {
            font-size: 11px; font-weight: 700; color: #334155;
            padding: 10px 20px; text-align: left;
            text-transform: uppercase; letter-spacing: 0.06em;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            background: rgba(255,255,255,0.02);
        }
        .med-table td {
            padding: 13px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #CBD5E1; vertical-align: middle;
        }
        .med-table tr:last-child td { border-bottom: none; }
        .med-table tbody tr:hover td { background: rgba(255,255,255,0.03); }

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

        /* 日誌 */
        .log-date  { font-size: 14px; color: #E2E8F0; font-weight: 600; }
        .log-time  { font-size: 11px; color: #475569; margin-top: 2px; }
        .pill-med {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 600;
            padding: 4px 12px;
            background: rgba(37,99,235,0.14);
            color: #93C5FD;
            border: 1px solid rgba(96,165,250,0.22);
            border-radius: 99px;
        }
        .pill-med i { font-size: 12px; }
        .status-ok { font-size: 12px; color: #34D399; display: flex; align-items: center; gap: 4px; font-weight: 600; }
        .status-ok i { font-size: 13px; }

        .divider-row td {
            background: rgba(255,255,255,0.02);
            padding: 6px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.05) !important;
        }
        .divider-label { font-size: 11px; color: #334155; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }

        @media (max-width: 600px) {
            .stat-value { font-size: 20px; }
            .med-table th, .med-table td { padding: 10px 12px; }
            .stat-row { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<!-- 動態背景 -->
<div class="bg-canvas">
    <div class="bg-grid"></div>
    <div class="bg-top-glow"></div>

    <!-- 漂浮光暈球 -->
    <div class="orb" style="width:520px;height:520px;top:-80px;right:-100px;background:rgba(37,99,235,0.12);--dx:40px;--dy:30px;--ds:1.1;animation-duration:18s;"></div>
    <div class="orb" style="width:380px;height:380px;bottom:-60px;left:-80px;background:rgba(124,58,237,0.10);--dx:-30px;--dy:-25px;--ds:0.9;animation-duration:22s;"></div>
    <div class="orb" style="width:260px;height:260px;top:45%;left:55%;background:rgba(16,185,129,0.07);--dx:20px;--dy:-40px;--ds:1.15;animation-duration:16s;"></div>

    <!-- 流光柱 -->
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
            <div class="bar-brand-sub">家屬遠端管理面板</div>
        </div>
        <div class="bar-right">
            <div class="tag-patient">
                <i class="ti ti-user-heart"></i>
                查看中：<strong><?php echo htmlspecialchars($patient_name); ?></strong>
            </div>
            <a href="guardian_hub.php" class="btn-back">
                <i class="ti ti-arrow-left"></i> 返回
            </a>
        </div>
    </div>

    <!-- 未服藥警告 -->
    <?php if ($today_logs_count === 0 && $current_hour >= 13): ?>
    <div class="anomaly-banner">
        <i class="ti ti-bell-ringing-2"></i>
        <span><strong>未遵醫囑預警：</strong> 偵測到 <?php echo htmlspecialchars($patient_name); ?> 截至今日 13:00 尚未有服藥紀錄，系統已同步發送郵件通知家屬。</span>
    </div>
    <?php endif; ?>

    <!-- 統計摘要 -->
    <div class="stat-row">
        <div class="stat-card">
            <div class="stat-label"><i class="ti ti-clipboard-list"></i> 服藥計畫</div>
            <div class="stat-value"><?php echo count($plans); ?> <span style="font-size:14px;font-weight:500;color:#475569;">項</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><i class="ti ti-alert-triangle"></i> 庫存警示</div>
            <div class="stat-value <?php echo $low_stock_count > 0 ? 'warn' : 'ok'; ?>">
                <?php echo $low_stock_count; ?> <span style="font-size:14px;font-weight:500;color:#475569;">項</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-label"><i class="ti ti-history"></i> 服藥紀錄</div>
            <div class="stat-value ok"><?php echo $total_logs; ?> <span style="font-size:14px;font-weight:500;color:#475569;">筆</span></div>
        </div>
    </div>

    <!-- 服藥計畫表格 -->
    <div class="section-card">
        <div class="section-header blue">
            <i class="ti ti-package"></i>
            <span class="title">服藥計畫與庫存追蹤</span>
        </div>
        <table class="med-table">
            <thead>
                <tr>
                    <th>藥品名稱</th>
                    <th>服用頻率</th>
                    <th>單次劑量</th>
                    <th>剩餘庫存</th>
                    <th>狀態</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plans as $plan):
                    $is_low    = $plan['remaining_qty'] <= $plan['alert_threshold'];
                    $max_qty   = $plan['alert_threshold'] * 5;
                    $bar_pct   = min(100, round(($plan['remaining_qty'] / max($max_qty, 1)) * 100));
                    $bar_class = $is_low ? 'warn' : 'ok';
                ?>
                <tr>
                    <td><div class="med-name"><?php echo htmlspecialchars($plan['medicine_name']); ?></div></td>
                    <td><?php echo htmlspecialchars($plan['daily_dosage']); ?></td>
                    <td><?php echo htmlspecialchars($plan['once_qty']); ?> 顆</td>
                    <td>
                        <div class="qty-bar-wrap">
                            <div class="qty-bar-bg">
                                <div class="qty-bar <?php echo $bar_class; ?>" style="width:<?php echo $bar_pct; ?>%"></div>
                            </div>
                            <span class="qty-num <?php echo $bar_class; ?>"><?php echo htmlspecialchars($plan['remaining_qty']); ?> 顆</span>
                        </div>
                    </td>
                    <td>
                        <?php if ($is_low): ?>
                            <span class="badge danger"><i class="ti ti-alert-triangle"></i>庫存過低</span>
                        <?php else: ?>
                            <span class="badge success"><i class="ti ti-circle-check"></i>庫存充足</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 服藥歷史日誌 -->
    <div class="section-card">
        <div class="section-header gray">
            <i class="ti ti-clipboard-text"></i>
            <span class="title">服藥歷史日誌</span>
            <a href="?action=export_csv" class="btn-export">
                <i class="ti ti-file-spreadsheet"></i> 匯出 Excel 報表
            </a>
        </div>
        <table class="med-table">
            <thead>
                <tr>
                    <th>服藥時間</th>
                    <th>確認服用藥品</th>
                    <th>紀錄狀態</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $prev_date = null;
                foreach ($logs as $log):
                    $taken_dt  = new DateTime($log['taken_time']);
                    $date_str  = $taken_dt->format('Y-m-d');
                    $today     = (new DateTime())->format('Y-m-d');
                    $yesterday = (new DateTime('-1 day'))->format('Y-m-d');

                    if ($date_str !== $prev_date):
                        if ($date_str === $today)         $label = '今天';
                        elseif ($date_str === $yesterday) $label = '昨天';
                        else                              $label = $taken_dt->format('m / d');
                        $prev_date = $date_str;
                ?>
                <tr class="divider-row">
                    <td colspan="3"><span class="divider-label"><?php echo $label; ?></span></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>
                        <div class="log-date"><?php echo $taken_dt->format('H:i'); ?></div>
                        <div class="log-time"><?php echo $taken_dt->format('A') === 'AM' ? '上午' : '下午'; ?></div>
                    </td>
                    <td>
                        <span class="pill-med">
                            <i class="ti ti-pill"></i>
                            <?php echo htmlspecialchars($log['medicine_name']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-ok">
                            <i class="ti ti-circle-check"></i>
                            <?php echo htmlspecialchars($log['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>