<?php
session_start();
require_once 'db.php';

// 1. 門禁檢查：確保是登入的家屬才能操作
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guardian') {
    header("Location: login.php");
    exit;
}

// 2. 檢查是否為 POST 正常送出表單
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 接收表單傳過來的資料
    $patient_id      = $_POST['patient_id'] ?? null;      // 長輩的 user_id
    $drug_id         = $_POST['drug_id'] ?? null;         // 藥品 ID
    $daily_dosage    = trim($_POST['daily_dosage'] ?? '');// 服用頻率說明
    $once_qty        = intval($_POST['once_qty'] ?? 1);   // 單次劑量
    $remaining_qty   = intval($_POST['remaining_qty'] ?? 0); // 放入藥箱總總量
    $alert_threshold = intval($_POST['alert_threshold'] ?? 5); // 警告閾值

    // 欄位防呆檢查
    if (empty($patient_id) || empty($drug_id) || empty($daily_dosage)) {
        echo "<script>alert('請填寫所有必要欄位！'); window.history.back();</script>";
        exit;
    }

    try {
        // 3. 寫入資料庫：塞進 medication_plan 表格
        // 這裡確保沒有誤用任何不存在的 category 欄位，純粹寫入計畫與庫存
        $sql = "INSERT INTO medication_plan (user_id, drug_id, daily_dosage, once_qty, remaining_qty, alert_threshold) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $patient_id, 
            $drug_id, 
            $daily_dosage, 
            $once_qty, 
            $remaining_qty, 
            $alert_threshold
        ]);

        // 4. 成功後跳出通知，並自動跳轉回「編輯模式」頁面
        echo "<script>
                alert('藥品服藥計畫已成功同步至智齡藥箱！');
                window.location.href = 'guardian_edit.php';
              </script>";
        exit;

    } catch (Exception $e) {
        // 如果資料庫寫入失敗（例如重複加入同一種藥，或是欄位對不上），跳出錯誤訊息
        echo "<script>
                alert('同步失敗！錯誤原因：" . addslashes($e->getMessage()) . "');
                window.history.back();
              </script>";
        exit;
    }
} else {
    // 如果有人直接在網址輸入這個檔案，直接踢回中轉頁
    header("Location: guardian_hub.php");
    exit;
}