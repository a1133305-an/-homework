<?php
session_start();
require_once 'db.php';
require_once 'config.php';

// 門禁：確保長輩有登入
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登入']);
    exit;
}

// 🔥 請把下方的字串換成你在 Google AI Studio 申請到的免費 API Key
//define('GEMINI_API_KEY', '你的_GEMINI_API_KEY_貼在這裡');

$patient_id = $_SESSION['user_id'];

try {
    // 1. 動態撈出「這位長輩」目前所有該吃的藥物，用來建立給 AI 的專屬清單
    $sql = "SELECT p.drug_id, m.name AS medicine_name 
            FROM medication_plan p
            JOIN medicine_db m ON p.drug_id = m.drug_id
            WHERE p.user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$patient_id]);
    $my_medicines = $stmt->fetchAll();

    if (empty($my_medicines)) {
        echo json_encode(['success' => false, 'message' => '該長輩目前無服藥計畫']);
        exit;
    }

    // 2. 將藥物清單組合成文字，等一下要塞進 Prompt 給 AI 當作對比選項
    $drug_options_text = "";
    foreach ($my_medicines as $med) {
        $drug_options_text .= "編號: " . $med['drug_id'] . " , 藥名: " . $med['medicine_name'] . "\n";
    }

    // 3. 檢查是否有收到前端拍照上傳的圖片
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => '未接收到有效的照片']);
        exit;
    }

    // 4. 將照片轉成 Base64 編碼，這樣才能透過 JSON 傳給 Google API
    $image_path = $_FILES['image']['tmp_name'];
    $image_data = base64_encode(file_get_contents($image_path));
    $mime_type = mime_content_type($image_path);

    // 5. 撰寫最強防呆 Prompt
    $prompt = "你是一個智齡藥箱的醫療影像辨識助手。\n"
            . "請仔細看這張長輩拍下的藥品（可能是藥盒、藥瓶或鋁箔排裝）照片，並比對以下這份該長輩目前的服藥清單：\n\n" // 👈 擴充包容性
            . $drug_options_text . "\n"
            . "請判斷照片中的藥品最符合清單中的哪一個「編號」。\n\n"
            . "【嚴格規則】\n"
            . "1. 如果確實在清單內，請「只回覆該藥物的數字編號」（例如：5 或 12），不要包含任何其他中英文字、標點符號或空格。\n"
            . "2. 如果照片極度模糊、拍的根本不是任何藥品、或者該藥品完全不在清單內，請絕對只能回覆大寫：UNKNOWN\n" // 👈 調整防呆語氣
            . "3. 嚴禁任何解釋、寒暄或廢話，你的回覆將直接被程式解析。";

    // 6. 打包 JSON 準備送往 Gemini 1.5 Flash (目前速度最快、對圖文辨識極強的模型)
    $clean_api_key = trim(GEMINI_API_KEY);
$api_url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . $clean_api_key;
    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt],
                    [
                        "inlineData" => [
                            "mimeType" => $mime_type,
                            "data" => $image_data
                        ]
                    ]
                ]
            ]
        ]
    ];

    // 7. 使用 cURL 發送 POST 請求給 Google API
    // 7. 使用 cURL 發送 POST 請求給 Google API（大檔案優化版）
    $ch = curl_init(); 
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url, 
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Connection: Keep-Alive' // ⚡ 保持連線活著，避免被 Google 提早踢掉
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        
        // 🛠️ 解決 503 的核心參數：
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // ⚡ 強制使用 IPv4 解析，避免 Localhost 尋找 IPv6 浪費時間
        CURLOPT_TIMEOUT => 60,                  // ⚡ 將逾時時間拉長到 60 秒，給大照片足夠的上傳時間
        CURLOPT_CONNECTTIMEOUT => 10,           // ⚡ 設定連線等待時間為 10 秒
        CURLOPT_BUFFERSIZE => 128000            // ⚡ 加大傳輸緩衝區，加速 Base64 圖片的上傳
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        // 🔥 為了讓你不再猜原因，如果 503 沒過，我們直接把 Google 吐出來的原文印在畫面上！
        echo json_encode([
            'success' => false, 
            'message' => 'API 連線失敗，代碼：' . $http_code . '，Google 原文：' . $response
        ]);
        exit;
    }

    // 8. 解析 Gemini 回傳的結果
    $resultData = json_decode($response, true);
    // 撈出 AI 輸出的純文字並去掉空白
    $ai_reply = trim($resultData['candidates'][0]['content']['parts'][0]['text'] ?? '');

    $ai_reply = trim(str_replace(['```markdown', '```'], '', $ai_reply));

  // 9. 【強化版判斷】先用正則表達式把 AI 回傳值裡面的任何隱形換行或雜質過濾掉
    // 有時候 Gemini 回傳 "3" 後面會偷偷帶有不可見的 \n
    preg_match('/\d+/', $ai_reply, $matches);
    $clean_drug_id = isset($matches[0]) ? (int)$matches[0] : null;

    if ($ai_reply !== 'UNKNOWN' && $clean_drug_id !== null) {
        echo json_encode([
            'success' => true,
            'drug_id' => $clean_drug_id
        ]);
    } else {
        // 【除錯貼心提示】如果辨識失敗，我們把 AI 的心裡話放進 JSON，前端才能 debug
        echo json_encode([
            'success' => true,
            'drug_id' => null,
            'debug_msg' => "AI原始回答為: '{$ai_reply}'，當時長輩藥單文字為: " . str_replace("\n", " ", $drug_options_text)
        ]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '系統錯誤：' . $e->getMessage()]);
}