<?php
// 1. 啟動 Session（必須先啟動才能找到並摧毀它）
session_start();

// 2. 清空所有的 Session 變數（把陣列洗空）
$_SESSION = array();

// 3. 為了更安全，把瀏覽器裡的 Session Cookie 也強制過期清掉
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. 徹底銷毀 Session 檔案
session_destroy();

// 5. 將使用者導向回登入頁面（假設你的登入頁叫做 login.php）
header("Location: login.php");
exit;