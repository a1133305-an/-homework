<?php
// 【修改 1】必須啟動 Session 才能拿到登入者的帳號資料
session_start();
require_once 'db.php';

// 【修改 2】加強防呆門禁：確保是長輩本人登入
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit;
}

// 確保長輩是透過按鈕正確送出資料，且有選擇藥物
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['drug_id'])) {
    
    // 【修改 3】把原本寫死的王大同阿公(1)，改成動態抓取目前登入的長輩 ID
    $patient_id = $_SESSION['user_id']; 
    $drug_id = intval($_POST['drug_id']);

    try {
        // 啟動「資料庫交易模式 (Transaction)」
        // 確保「扣庫存」和「寫入日誌」這兩件事要嘛同時成功，要嘛同時失敗，才不會有資料時間對不上的問題
        $pdo->beginTransaction();

        // 步驟 1：去 medication_plan 查一下這隻藥每次要吃幾顆 (once_qty)
        $sql_qty = "SELECT once_qty FROM medication_plan WHERE user_id = ? AND drug_id = ?";
        $stmt = $pdo->prepare($sql_qty);
        $stmt->execute([$patient_id, $drug_id]);
        $plan = $stmt->fetch();

        if ($plan) {
            $once_qty = $plan['once_qty'];

            // 步驟 2：更新計畫表，把剩餘庫存扣掉這次吃的顆數
            $sql_update = "UPDATE medication_plan 
                           SET remaining_qty = remaining_qty - ? 
                           WHERE user_id = ? AND drug_id = ?";
            $stmt = $pdo->prepare($sql_update);
            $stmt->execute([$once_qty, $patient_id, $drug_id]);

            // 步驟 3：在 history_log 塞一筆新的吃藥紀錄（時間會由 MySQL 自動生成）
            $sql_log = "INSERT INTO history_log (user_id, drug_id, status) VALUES (?, ?, '正常')";
            $stmt = $pdo->prepare($sql_log);
            $stmt->execute([$patient_id, $drug_id]);

            // 到這裡都沒出錯，正式把資料寫入倉庫！
            $pdo->commit();

            // 動作完成！讓長輩的網頁自動跳轉回原本的 patient.php
            header("Location: patient.php");
            exit;
        } else {
            throw new Exception("找不到該藥物的服藥計畫");
        }

    } catch (Exception $e) {
        // 如果中間有任何一步卡住，全部回滾（Rollback）當作沒發生過
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "吃藥紀錄失敗，原因：" . $e->getMessage();
    }
} else {
    // 如果不是正常點擊按鈕進來的，直接踢回長輩首頁
    header("Location: patient.php");
    exit;
}
?>