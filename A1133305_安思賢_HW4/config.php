<?php
// ============================================================
//  config.php — MAILSYS SMTP 設定
//  只需要設定一次，填好後存檔即可
// ============================================================

// ── 資料庫設定 ────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'mailsys_db');
define('DB_USER',    'root');        // <-- 你的 MySQL 帳號
define('DB_PASS',    '');            // <-- 你的 MySQL 密碼
define('DB_CHARSET', 'utf8mb4');

// ── SMTP 設定 ─────────────────────────────────────────────────
//
//  Gmail 範例:
//    SMTP_HOST = 'smtp.gmail.com'
//    SMTP_PORT = 587
//    SMTP_USER = 'you@gmail.com'
//    SMTP_PASS = 'xxxx xxxx xxxx xxxx'  ← Gmail 應用程式密碼 (非登入密碼)
//    SMTP_FROM = 'you@gmail.com'
//
//  Outlook / Hotmail 範例:
//    SMTP_HOST = 'smtp.office365.com'
//    SMTP_PORT = 587
//
//  其他 (自架 / cPanel 等):
//    SMTP_HOST = 'mail.yourdomain.com'
//    SMTP_PORT = 587 or 465
//
define('SMTP_HOST',       'smtp.gmail.com');      // SMTP 伺服器
define('SMTP_PORT',       587);                   // 587=TLS  /  465=SSL
define('SMTP_ENCRYPTION', 'tls');                 // 'tls' 或 'ssl'
define('SMTP_USER',       'a1133305@mail.nuk.edu.tw');       // <-- 你的 Email 帳號
define('SMTP_PASS',       'defc piyr ivcn dppy'); // <-- 你的密碼 / App Password
define('SMTP_FROM_EMAIL', 'a1133305@mail.nuk.edu.tw');       // 寄件者 Email
define('SMTP_FROM_NAME',  'I AM ANDY');             // 寄件者顯示名稱
