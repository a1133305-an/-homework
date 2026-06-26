<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];
$patient_name = $_SESSION['username'];

try {
    $sql = "SELECT p.drug_id, p.once_qty, p.daily_dosage, m.name AS medicine_name 
            FROM medication_plan p
            JOIN medicine_db m ON p.drug_id = m.drug_id
            WHERE p.user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$patient_id]);
    $my_medicines = $stmt->fetchAll();
} catch (Exception $e) {
    echo "系統錯誤：" . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>智齡藥箱 - 長輩服藥端</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Noto Sans TC', sans-serif;
            background: #020810;
            color: #E2E8F0;
            min-height: 100vh;
        }

        /* ── 動態背景 ── */
        .bg-canvas { position: fixed; inset: 0; z-index: 0; overflow: hidden; }

        .orb {
            position: absolute; border-radius: 50%; filter: blur(90px);
            animation: orb-drift ease-in-out infinite alternate;
        }
        @keyframes orb-drift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(var(--dx),var(--dy)) scale(var(--ds)); }
        }

        .bg-grid {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.018) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.018) 1px, transparent 1px);
            background-size: 44px 44px;
        }

        .beam {
            position: absolute; width: 1px;
            background: linear-gradient(180deg, transparent 0%, rgba(56,139,253,0.45) 40%, rgba(167,139,250,0.25) 70%, transparent 100%);
            animation: beam-fall linear infinite; opacity: 0;
        }
        @keyframes beam-fall {
            0%   { transform: translateY(-100%); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 0.5; }
            100% { transform: translateY(110vh); opacity: 0; }
        }

        /* ── 頁面主體 ── */
        .page {
            position: relative; z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.75rem 2rem 3rem;
        }

        /* ── 頂部導覽 ── */
        .top-bar {
            display: flex; align-items: center; gap: 14px;
            padding: 14px 22px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 16px;
            margin-bottom: 1.75rem;
            backdrop-filter: blur(14px);
            position: relative; overflow: hidden;
        }
        .top-bar::before {
            content: '';
            position: absolute;
            top: 0; left: 20px; right: 20px; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.14), transparent);
        }
        .logo-mark {
            width: 44px; height: 44px; border-radius: 11px;
            background: linear-gradient(135deg, #2563EB, #4F46E5);
            box-shadow: 0 4px 16px rgba(37,99,235,0.40);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .logo-mark i { font-size: 22px; color: #fff; }
        .bar-brand-name { font-size: 16px; font-weight: 700; color: #F1F5F9; }
        .bar-brand-sub  { font-size: 11px; font-weight: 600; letter-spacing: 0.09em; text-transform: uppercase; color: #60A5FA; margin-top: 2px; }

        /* 右側時鐘 */
        .bar-right { margin-left: auto; text-align: right; }
        #liveClock { font-size: 28px; font-weight: 900; color: #F1F5F9; letter-spacing: -0.02em; line-height: 1; }
        #liveDate  { font-size: 13px; color: #475569; margin-top: 3px; font-weight: 500; }

        /* ── 歡迎橫幅（全寬） ── */
        .greeting-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 22px;
            padding: 1.75rem 2rem;
            margin-bottom: 1.5rem;
            display: flex; align-items: center; gap: 1.5rem;
            backdrop-filter: blur(14px);
            position: relative; overflow: hidden;
        }
        .greeting-card::before {
            content: '';
            position: absolute;
            top: 0; left: 24px; right: 24px; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(96,165,250,0.3), transparent);
        }
        .avatar {
            width: 80px; height: 80px; border-radius: 50%;
            background: linear-gradient(135deg, #2563EB, #4F46E5);
            box-shadow: 0 4px 24px rgba(37,99,235,0.40);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .avatar i { font-size: 42px; color: #fff; }
        .greeting-name { font-size: 36px; font-weight: 900; color: #F1F5F9; line-height: 1.2; }
        .greeting-sub  { font-size: 18px; color: #64748B; margin-top: 6px; font-weight: 500; }

        /* 右側提示標 */
        .greeting-right { margin-left: auto; display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
        .med-count-tag {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 15px; font-weight: 700;
            color: #93C5FD;
            background: rgba(37,99,235,0.14);
            border: 1px solid rgba(96,165,250,0.25);
            border-radius: 99px;
            padding: 8px 20px;
        }
        .med-count-tag i { font-size: 17px; }

        /* ── 主體雙欄 ── */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            align-items: start;
        }

        /* ── 卡片通用 ── */
        .step-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 22px;
            padding: 1.75rem;
            backdrop-filter: blur(12px);
            position: relative; overflow: hidden;
        }
        .step-card::before {
            content: '';
            position: absolute;
            top: 0; left: 18px; right: 18px; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.10), transparent);
        }

        .step-header {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 1.5rem;
        }
        .step-num {
            width: 38px; height: 38px; border-radius: 50%;
            background: rgba(37,99,235,0.20);
            border: 1px solid rgba(96,165,250,0.30);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 900; color: #60A5FA;
            flex-shrink: 0;
        }
        .step-title { font-size: 20px; font-weight: 700; color: #93C5FD; }

        .hint-tag {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 14px; color: #475569; font-weight: 500;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 9px;
            padding: 7px 14px;
            margin-bottom: 1.25rem;
        }
        .hint-tag i { font-size: 15px; }

        /* ── 相機按鈕 ── */
        .btn-camera {
            display: flex; align-items: center; justify-content: center; gap: 16px;
            width: 100%;
            padding: 1.6rem;
            font-size: 26px; font-weight: 700; font-family: inherit;
            background: rgba(37,99,235,0.16);
            color: #93C5FD;
            border: 1px solid rgba(96,165,250,0.28);
            border-radius: 18px;
            cursor: pointer;
            letter-spacing: 0.02em;
            transition: background 0.18s, border-color 0.18s, transform 0.10s;
            margin-bottom: 1.1rem;
        }
        .btn-camera:hover  { background: rgba(37,99,235,0.28); border-color: rgba(96,165,250,0.50); }
        .btn-camera:active { transform: scale(0.97); }
        .btn-camera i { font-size: 32px; }

        #aiStatus {
            font-size: 18px; font-weight: 600;
            color: #475569;
            text-align: center;
            min-height: 30px;
            transition: color 0.2s;
        }

        /* ── 語音說明 ── */
        .voice-box {
            background: rgba(37,99,235,0.10);
            border: 1px solid rgba(96,165,250,0.18);
            border-radius: 16px;
            padding: 1.5rem 1.75rem;
            margin-bottom: 1.25rem;
        }
        .voice-icon { font-size: 30px; color: #60A5FA; margin-bottom: 10px; }
        .voice-text {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.75;
            color: #F1F5F9;
            letter-spacing: 0.01em;
        }

        .btn-speak {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 17px; font-weight: 600; font-family: inherit;
            padding: 12px 24px;
            color: #60A5FA;
            background: rgba(37,99,235,0.10);
            border: 1px solid rgba(96,165,250,0.22);
            border-radius: 99px;
            cursor: pointer;
            transition: background 0.15s;
            margin-bottom: 1.5rem;
        }
        .btn-speak:hover { background: rgba(37,99,235,0.20); }
        .btn-speak i { font-size: 20px; }

        /* ── 吃藥確認按鈕 ── */
        .btn-eat {
            display: flex; align-items: center; justify-content: center; gap: 16px;
            width: 100%;
            padding: 1.8rem;
            font-size: 32px; font-weight: 900; font-family: inherit;
            background: rgba(16,185,129,0.16);
            color: #34D399;
            border: 1px solid rgba(52,211,153,0.30);
            border-radius: 20px;
            cursor: pointer;
            letter-spacing: 0.02em;
            transition: background 0.18s, border-color 0.18s, transform 0.10s;
        }
        .btn-eat:hover  { background: rgba(16,185,129,0.26); border-color: rgba(52,211,153,0.55); }
        .btn-eat:active { transform: scale(0.97); }
        .btn-eat i { font-size: 36px; }

        /* ── 右欄：等待中的佔位區 ── */
        .waiting-panel {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            min-height: 420px;
            gap: 1.25rem;
        }
        .waiting-icon {
            width: 96px; height: 96px; border-radius: 50%;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            display: flex; align-items: center; justify-content: center;
        }
        .waiting-icon i { font-size: 48px; color: #1E293B; }
        .waiting-text { font-size: 18px; color: #334155; font-weight: 600; text-align: center; line-height: 1.6; }

        /* ── 安全警告（全寬底部） ── */
        .safety-note {
            display: flex; align-items: flex-start; gap: 12px;
            background: rgba(153,60,29,0.12);
            border: 1px solid rgba(248,113,113,0.20);
            border-radius: 16px;
            padding: 1.1rem 1.4rem;
            margin-top: 1.25rem;
            font-size: 17px; font-weight: 500;
            color: #FCA5A5;
            line-height: 1.65;
        }
        .safety-note i { font-size: 22px; margin-top: 2px; flex-shrink: 0; color: #F87171; }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<!-- 動態背景 -->
<div class="bg-canvas">
    <div class="bg-grid"></div>
    <div class="orb" style="width:600px;height:600px;top:-120px;right:-140px;background:rgba(37,99,235,0.11);--dx:40px;--dy:28px;--ds:1.1;animation-duration:20s;"></div>
    <div class="orb" style="width:400px;height:400px;bottom:-80px;left:-100px;background:rgba(124,58,237,0.09);--dx:-28px;--dy:-22px;--ds:0.9;animation-duration:24s;"></div>
    <div class="orb" style="width:260px;height:260px;top:50%;left:48%;background:rgba(16,185,129,0.06);--dx:20px;--dy:-38px;--ds:1.12;animation-duration:18s;"></div>
    <div class="beam" style="left:10%;height:45vh;animation-duration:7s;animation-delay:0s;"></div>
    <div class="beam" style="left:35%;height:30vh;animation-duration:9s;animation-delay:2s;background:linear-gradient(180deg,transparent,rgba(167,139,250,0.35),transparent);"></div>
    <div class="beam" style="left:62%;height:38vh;animation-duration:8s;animation-delay:4s;background:linear-gradient(180deg,transparent,rgba(52,211,153,0.30),transparent);"></div>
    <div class="beam" style="left:88%;height:26vh;animation-duration:10s;animation-delay:6s;"></div>
</div>

<div class="page">

    <!-- 頂部導覽 -->
    <div class="top-bar">
        <div class="logo-mark"><i class="ti ti-pill"></i></div>
        <div>
            <div class="bar-brand-name">智齡藥箱 AI MedBox</div>
            <div class="bar-brand-sub">長輩服藥端</div>
        </div>
        <div class="bar-right">
            <div id="liveClock">--:--</div>
            <div id="liveDate">載入中...</div>
        </div>
    </div>

    <!-- 歡迎橫幅 -->
    <div class="greeting-card">
        <div class="avatar"><i class="ti ti-mood-smile"></i></div>
        <div>
            <div class="greeting-name"><?php echo htmlspecialchars($patient_name); ?>，哈摟！</div>
            <div class="greeting-sub">請按照步驟完成今天的服藥</div>
        </div>
        <div class="greeting-right">
            <div class="med-count-tag">
                <i class="ti ti-pill"></i>
                今日共 <?php echo count($my_medicines); ?> 種藥
            </div>
        </div>
    </div>

    <!-- 主體雙欄 -->
    <div class="main-grid">

        <!-- 左欄：步驟一 拍照 -->
        <div class="step-card">
            <div class="step-header">
                <div class="step-num">1</div>
                <div class="step-title">將藥盒對準鏡頭</div>
            </div>
            <div class="hint-tag">
                <i class="ti ti-info-circle"></i>
                點擊下方按鈕啟動相機，AI 將自動比對藥物
            </div>

            <input type="file" id="cameraInput" accept="image/*" capture="camera" style="display:none;" onchange="uploadAndRecognize()">

            <button type="button" class="btn-camera" onclick="triggerCamera()">
                <i class="ti ti-camera"></i> 打開相機拍照
            </button>

            <div id="aiStatus">等待拍照中...</div>

            <form id="medForm" action="take_medicine_process.php" method="POST">
                <input type="hidden" name="drug_id" id="hiddenDrugId" value="">
            </form>
        </div>

        <!-- 右欄：步驟二（等待時顯示佔位；辨識成功後顯示語音卡） -->
        <div class="step-card">
            <!-- 等待中佔位 -->
            <div class="waiting-panel" id="waitingPanel">
                <div class="waiting-icon"><i class="ti ti-scan"></i></div>
                <div class="waiting-text">請先完成左側拍照<br>辨識結果將在這裡顯示</div>
            </div>

            <!-- 語音說明（辨識後顯示） -->
            <div id="guideInner" style="display:none;">
                <div class="step-header">
                    <div class="step-num">2</div>
                    <div class="step-title">聽完說明後服藥</div>
                </div>
                <div class="voice-box">
                    <div class="voice-icon"><i class="ti ti-speakerphone"></i></div>
                    <div class="voice-text" id="voiceText">請先選擇藥物</div>
                </div>
                <button class="btn-speak" onclick="speakNow()">
                    <i class="ti ti-refresh"></i> 再唸一次給我聽
                </button>
                <button type="button" class="btn-eat" id="btnEat" onclick="submitForm()">
                    <i class="ti ti-circle-check"></i> 我吃藥了
                </button>
            </div>
        </div>

    </div>

    <!-- 底部安全警告（全寬） -->
    <div class="safety-note">
        <i class="ti ti-alert-triangle"></i>
        <span>若藥品名稱與手上藥盒不符，請勿服用，並立刻告知家人或照護人員。</span>
    </div>

</div>

<script>
<?php
$guide_array = [];
foreach ($my_medicines as $med) {
    $text = "這是" . $med['medicine_name'] . "。請吃 " . $med['once_qty'] . " 顆。叮嚀：" . $med['daily_dosage'] . "。";
    $guide_array[$med['drug_id']] = $text;
}
?>
const medGuides = <?php echo json_encode($guide_array, JSON_UNESCAPED_UNICODE); ?>;

// 時鐘
function updateClock() {
    const now = new Date();
    const hh = String(now.getHours()).padStart(2, '0');
    const mm = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('liveClock').textContent = hh + ':' + mm;
    const days = ['日','一','二','三','四','五','六'];
    const dateStr = (now.getMonth()+1) + ' 月 ' + now.getDate() + ' 日（週' + days[now.getDay()] + '）';
    document.getElementById('liveDate').textContent = dateStr;
}
updateClock();
setInterval(updateClock, 10000);

function triggerCamera() {
    document.getElementById('cameraInput').click();
}

function uploadAndRecognize() {
    const fileInput   = document.getElementById('cameraInput');
    const aiStatus    = document.getElementById('aiStatus');
    const waitingPanel = document.getElementById('waitingPanel');
    const guideInner  = document.getElementById('guideInner');
    const voiceText   = document.getElementById('voiceText');
    const hiddenDrugId = document.getElementById('hiddenDrugId');
    const btnEat      = document.getElementById('btnEat');

    if (fileInput.files.length === 0) return;

    aiStatus.style.color = '#60A5FA';
    aiStatus.innerHTML = '<i class="ti ti-loader-2" style="display:inline-block; animation: spin 1s linear infinite;"></i> AI 正在辨識藥盒中，請稍候...';
    waitingPanel.style.display = 'flex';
    guideInner.style.display = 'none';
    btnEat.style.display = 'flex';

    const formData = new FormData();
    formData.append('image', fileInput.files[0]);

    fetch('ai_recognize_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const matchedDrugId = data.drug_id;
            if (medGuides[matchedDrugId]) {
                aiStatus.style.color = '#34D399';
                aiStatus.innerHTML = '✅ 辨識成功！這是您該吃的藥。';
                hiddenDrugId.value = matchedDrugId;
                waitingPanel.style.display = 'none';
                guideInner.style.display = 'block';
                voiceText.innerText = medGuides[matchedDrugId];
                speakNow();
            } else {
                aiStatus.style.color = '#F87171';
                aiStatus.innerHTML = '❌ 警告：這不是您現在該吃的藥物！';
                waitingPanel.style.display = 'none';
                guideInner.style.display = 'block';
                voiceText.innerText = "警告！拿錯藥了！這不是您現在該吃的藥物，請換回正確的藥盒，或立刻聯絡家屬！";
                speakNow();
                btnEat.style.display = 'none';
            }
        } else {
            aiStatus.style.color = '#F87171';
            aiStatus.innerHTML = '❌ AI 無法辨識物體，請對準藥盒重新拍照。';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        aiStatus.style.color = '#F87171';
        aiStatus.innerHTML = '❌ 系統連線錯誤，請重試。';
    });
}

function speakNow() {
    const text = document.getElementById('voiceText').innerText;
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'zh-TW';
    utterance.rate = 0.9;
    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(utterance);
}

function submitForm() {
    document.getElementById('medForm').submit();
}
</script>

</body>
</html>