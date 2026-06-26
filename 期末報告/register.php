<?php
session_start();
require_once 'db.php';

$message = '';
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $target_patient_username = trim($_POST['target_patient_username'] ?? '');

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $message = "這個帳號已經被註冊過囉！";
            $error = true;
        } else {
            $patient_id = null;

            if ($role === 'guardian') {
                if (empty($target_patient_username)) {
                    $message = "家屬註冊時，必須填寫負責照護的長輩帳號！";
                    $error = true;
                } else {
                    $stmt_find = $pdo->prepare("SELECT id, role FROM users WHERE username = ?");
                    $stmt_find->execute([$target_patient_username]);
                    $target_user = $stmt_find->fetch();

                    if ($target_user && $target_user['role'] === 'patient') {
                        $patient_id = $target_user['id'];
                    } else {
                        $message = "找不到名為「{$target_patient_username}」的長輩帳號，請確認對方已先完成註冊！";
                        $error = true;
                    }
                }
            }

            if (!$error) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, password, role, patient_id) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$username, $hashed_password, $role, $patient_id]);
                $message = "註冊成功！歡迎使用智齡藥箱，請點下方連結前往登入。";
            }
        }
    } catch (Exception $e) {
        $message = "註冊失敗：" . $e->getMessage();
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>註冊 - 智齡藥箱</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', 'Noto Sans TC', sans-serif;
            background: #04090F;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* 背景照片 */
        .bg-photo {
            position: fixed;
            inset: 0;
            background-image: url('https://images.unsplash.com/photo-1585421514738-01798e348b17?auto=format&fit=crop&w=1800&q=80');
            background-size: cover;
            background-position: center;
            filter: brightness(0.20) saturate(0.55);
            z-index: 0;
        }
        .bg-overlay {
            position: fixed;
            inset: 0;
            background: linear-gradient(
                160deg,
                rgba(4, 9, 15, 0.65) 0%,
                rgba(10, 25, 60, 0.40) 50%,
                rgba(4, 9, 15, 0.88) 100%
            );
            z-index: 1;
        }
        .bg-glow {
            position: fixed;
            width: 600px; height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.10) 0%, transparent 70%);
            top: -120px; right: -100px;
            z-index: 2;
            pointer-events: none;
        }
        .bg-glow-2 {
            position: fixed;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.10) 0%, transparent 70%);
            bottom: -80px; left: -60px;
            z-index: 2;
            pointer-events: none;
        }

        /* 主容器 */
        .register-wrap {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
        }

        /* 品牌 */
        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 28px;
        }
        .brand-logo {
            width: 58px; height: 58px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 14px;
            background: linear-gradient(135deg, #2563EB, #4F46E5);
            box-shadow: 0 8px 28px rgba(37, 99, 235, 0.40);
        }
        .brand-logo i { font-size: 28px; color: #fff; }
        .brand-name {
            font-size: 19px;
            font-weight: 700;
            color: #F1F5F9;
            letter-spacing: -0.01em;
        }
        .brand-sub {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #60A5FA;
            margin-top: 4px;
        }

        /* 卡片 */
        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 24px;
            padding: 36px 32px;
            backdrop-filter: blur(20px) saturate(1.3);
            -webkit-backdrop-filter: blur(20px) saturate(1.3);
            box-shadow:
                0 0 0 1px rgba(56, 139, 253, 0.07),
                0 32px 80px rgba(0, 0, 0, 0.45);
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 24px; right: 24px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.18), transparent);
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #94A3B8;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
            letter-spacing: 0.01em;
        }

        /* 訊息框 */
        .msg-box {
            display: flex; align-items: flex-start; gap: 10px;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.6;
        }
        .msg-box i { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
        .msg-box.error {
            background: rgba(153, 60, 29, 0.18);
            border: 1px solid rgba(240, 153, 123, 0.25);
            color: #FCA5A5;
        }
        .msg-box.error i { color: #F87171; }
        .msg-box.success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(52, 211, 153, 0.25);
            color: #6EE7B7;
        }
        .msg-box.success i { color: #34D399; }
        .msg-box.success a {
            color: #34D399;
            font-weight: 600;
            text-decoration: none;
            margin-left: 4px;
        }
        .msg-box.success a:hover { text-decoration: underline; }

        /* 表單欄位 */
        .field { margin-bottom: 16px; }
        .field label {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748B;
            margin-bottom: 8px;
        }
        .field label i { font-size: 14px; }

        .field input,
        .field select {
            width: 100%;
            padding: 13px 16px;
            font-size: 15px;
            font-family: inherit;
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            color: #F1F5F9;
            outline: none;
            transition: border-color 0.18s, background 0.18s, box-shadow 0.18s;
            appearance: none;
            -webkit-appearance: none;
        }
        .field select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2360A5FA' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            cursor: pointer;
        }
        .field select option {
            background: #0F172A;
            color: #F1F5F9;
        }
        .field input:focus,
        .field select:focus {
            border-color: rgba(96, 165, 250, 0.55);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .field input::placeholder { color: #334155; }

        /* 家屬綁定區塊 */
        .guardian-field {
            display: none;
            background: rgba(16, 185, 129, 0.07);
            border: 1px solid rgba(52, 211, 153, 0.22);
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 16px;
        }
        .guardian-field-label {
            display: flex; align-items: center; gap: 6px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #34D399;
            margin-bottom: 10px;
        }
        .guardian-field-label i { font-size: 14px; }
        .guardian-field input {
            width: 100%;
            padding: 12px 14px;
            font-size: 15px;
            font-family: inherit;
            border: 1px solid rgba(52, 211, 153, 0.25);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            color: #F1F5F9;
            outline: none;
            transition: border-color 0.18s, box-shadow 0.18s;
        }
        .guardian-field input:focus {
            border-color: rgba(52, 211, 153, 0.55);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
        }
        .guardian-field input::placeholder { color: #334155; }
        .guardian-hint {
            display: flex; align-items: flex-start; gap: 6px;
            font-size: 12px;
            color: #34D399;
            opacity: 0.7;
            margin-top: 10px;
            line-height: 1.55;
        }
        .guardian-hint i { font-size: 13px; flex-shrink: 0; margin-top: 1px; }

        /* 送出按鈕 */
        .btn-submit {
            width: 100%;
            padding: 14px;
            margin-top: 8px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            background: linear-gradient(135deg, #059669, #0F6E56);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 8px 28px rgba(16, 185, 129, 0.28);
            transition: all 0.22s ease;
            letter-spacing: 0.01em;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #10B981, #065F46);
            box-shadow: 0 12px 36px rgba(16, 185, 129, 0.42);
            transform: translateY(-1px);
        }
        .btn-submit:active { transform: scale(0.98); }
        .btn-submit i { font-size: 17px; }

        /* 底部連結 */
        .footer-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #475569;
        }
        .footer-link a {
            color: #60A5FA;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.15s;
        }
        .footer-link a:hover { color: #93C5FD; }

        /* 角色標籤 */
        .role-hint {
            display: flex; gap: 10px; justify-content: center;
            margin-top: 24px;
        }
        .role-tag {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12px; padding: 6px 14px;
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: 0.02em;
            border: 1px solid;
            backdrop-filter: blur(8px);
        }
        .role-tag.guardian {
            background: rgba(37, 99, 235, 0.12);
            color: #93C5FD;
            border-color: rgba(37, 99, 235, 0.28);
        }
        .role-tag.patient {
            background: rgba(16, 185, 129, 0.10);
            color: #6EE7B7;
            border-color: rgba(16, 185, 129, 0.25);
        }
        .role-tag i { font-size: 13px; }

        /* 返回連結 */
        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #475569;
            text-decoration: none;
            transition: color 0.15s;
        }
        .back-link:hover { color: #94A3B8; }
        .back-link i { font-size: 15px; }
    </style>
    <script>
        function togglePatientField() {
            var roleSelect = document.getElementById("roleSelect");
            var patientField = document.getElementById("patientField");
            if (roleSelect.value === "guardian") {
                patientField.style.display = "block";
                document.getElementById("target_input").required = true;
            } else {
                patientField.style.display = "none";
                document.getElementById("target_input").required = false;
            }
        }
    </script>
</head>
<body onload="togglePatientField()">

    <div class="bg-photo"></div>
    <div class="bg-overlay"></div>
    <div class="bg-glow"></div>
    <div class="bg-glow-2"></div>

    <div class="register-wrap">

        <!-- 返回首頁 -->
        <a href="index.php" class="back-link">
            <i class="ti ti-arrow-left"></i>
            返回首頁
        </a>

        <!-- 品牌 -->
        <div class="brand">
            <div class="brand-logo"><i class="ti ti-pill"></i></div>
            <div class="brand-name">智齡藥箱 AI MedBox</div>
            <div class="brand-sub">智慧用藥照護系統</div>
        </div>

        <!-- 註冊卡片 -->
        <div class="card">
            <div class="card-title">建立新帳號</div>

            <?php if ($message): ?>
            <div class="msg-box <?php echo $error ? 'error' : 'success'; ?>">
                <i class="ti ti-<?php echo $error ? 'alert-circle' : 'circle-check'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
                <?php if (!$error): ?>
                    <a href="login.php">前往登入 →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <form action="register.php" method="POST">

                <div class="field">
                    <label><i class="ti ti-user"></i> 設定帳號</label>
                    <input type="text" name="username" placeholder="請輸入帳號" required>
                </div>

                <div class="field">
                    <label><i class="ti ti-lock"></i> 設定密碼</label>
                    <input type="password" name="password" placeholder="請輸入密碼" required>
                </div>

                <div class="field">
                    <label><i class="ti ti-id-badge"></i> 選擇身分</label>
                    <select name="role" id="roleSelect" onchange="togglePatientField()" required>
                        <option value="patient">我是受照護長輩 (Patient)</option>
                        <option value="guardian">我是家屬／照護者 (Guardian)</option>
                    </select>
                </div>

                <!-- 家屬專屬：綁定長輩帳號（JS 控制顯示，邏輯不變） -->
                <div id="patientField" class="guardian-field">
                    <div class="guardian-field-label">
                        <i class="ti ti-link"></i> 綁定長輩帳號
                    </div>
                    <input
                        type="text"
                        name="target_patient_username"
                        id="target_input"
                        placeholder="請輸入您要照護的長輩帳號"
                    >
                    <div class="guardian-hint">
                        <i class="ti ti-info-circle"></i>
                        長輩必須先完成註冊，您才能搜尋到對方的帳號。
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="ti ti-user-plus"></i> 確認註冊
                </button>
            </form>

            <div class="footer-link">
                已經有帳號了？ <a href="login.php">返回登入</a>
            </div>
        </div>

        <!-- 角色提示 -->
        <div class="role-hint">
            <span class="role-tag guardian"><i class="ti ti-users"></i> 家屬管理端</span>
            <span class="role-tag patient"><i class="ti ti-mood-smile"></i> 長輩服藥端</span>
        </div>

    </div>

</body>
</html>