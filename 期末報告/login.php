<?php
session_start();
require_once 'db.php';

$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'guardian') {
                $_SESSION['patient_id'] = $user['patient_id'] ?? null;
            } else {
                $_SESSION['patient_id'] = null;
            }

            if ($_SESSION['role'] === 'guardian') {
                header("Location: guardian_hub.php");
            } else {
                header("Location: patient.php");
            }
            exit;
        } else {
            $error_msg = "帳號或密碼錯誤，請重新輸入！";
        }
    } catch (Exception $e) {
        $error_msg = "系統錯誤：" . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入 - 智齡藥箱</title>
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
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        /* 背景照片 */
        .bg-photo {
            position: fixed;
            inset: 0;
            background-image: url('https://images.unsplash.com/photo-1631549916768-4119b2e5f926?auto=format&fit=crop&w=1800&q=80');
            background-size: cover;
            background-position: center 30%;
            filter: brightness(0.25) saturate(0.6);
            z-index: 0;
        }
        .bg-overlay {
            position: fixed;
            inset: 0;
            background: linear-gradient(
                160deg,
                rgba(4, 9, 15, 0.60) 0%,
                rgba(10, 25, 60, 0.45) 50%,
                rgba(4, 9, 15, 0.85) 100%
            );
            z-index: 1;
        }
        /* 光暈裝飾 */
        .bg-glow {
            position: fixed;
            width: 600px; height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(56, 139, 253, 0.14) 0%, transparent 70%);
            top: -100px; right: -100px;
            z-index: 2;
            pointer-events: none;
        }
        .bg-glow-2 {
            position: fixed;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.10) 0%, transparent 70%);
            bottom: -80px; left: -60px;
            z-index: 2;
            pointer-events: none;
        }

        /* 主容器 */
        .login-wrap {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
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

        /* 登入卡片 */
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
        /* 卡片頂部光澤線 */
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

        /* 錯誤訊息 */
        .error-box {
            display: flex; align-items: center; gap: 10px;
            background: rgba(153, 60, 29, 0.18);
            border: 1px solid rgba(240, 153, 123, 0.25);
            border-radius: 10px;
            padding: 11px 14px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #FCA5A5;
        }
        .error-box i { font-size: 16px; flex-shrink: 0; color: #F87171; }

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

        .field input {
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
        }
        .field input:focus {
            border-color: rgba(96, 165, 250, 0.55);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .field input::placeholder { color: #334155; }

        /* 登入按鈕 */
        .btn-submit {
            width: 100%;
            padding: 14px;
            margin-top: 8px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            background: linear-gradient(135deg, #2563EB, #4F46E5);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 8px 28px rgba(37, 99, 235, 0.35);
            transition: all 0.22s ease;
            letter-spacing: 0.01em;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #3B82F6, #6D28D9);
            box-shadow: 0 12px 36px rgba(37, 99, 235, 0.50);
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

        /* 返回首頁 */
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
</head>
<body>

    <div class="bg-photo"></div>
    <div class="bg-overlay"></div>
    <div class="bg-glow"></div>
    <div class="bg-glow-2"></div>

    <div class="login-wrap">

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

        <!-- 登入卡片 -->
        <div class="card">
            <div class="card-title">帳號登入</div>

            <?php if ($error_msg): ?>
            <div class="error-box">
                <i class="ti ti-alert-circle"></i>
                <?php echo htmlspecialchars($error_msg); ?>
            </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="field">
                    <label><i class="ti ti-user"></i> 帳號</label>
                    <input type="text" name="username" placeholder="請輸入帳號" required>
                </div>
                <div class="field">
                    <label><i class="ti ti-lock"></i> 密碼</label>
                    <input type="password" name="password" placeholder="請輸入密碼" required>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="ti ti-login"></i> 登入系統
                </button>
            </form>

            <div class="footer-link">
                還沒有帳號？ <a href="register.php">按此註冊</a>
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