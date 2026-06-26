<?php
// 資料庫設定值
$host     = 'sql200.infinityfree.com';
$db       = 'if0_42265962_medbox'; // ⚠️ 請改成你在 phpMyAdmin 建立的資料庫名稱
$user     = 'if0_42265962';
$pass     = 'VL5noxsrQ0hjj';     // XAMPP 預設密碼為空字串
$charset  = 'utf8mb4';

// PDO 連線設定
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // 開啟錯誤回報模式
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // 用關聯陣列取回資料
    PDO::ATTR_EMULATE_PREPARES   => false,                  // 關閉模擬預處理，提高安全性
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     // 測試連線成功時可以用，確認沒問題後可以把下面這行註解掉
     // echo "資料庫連線成功！"; 
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>