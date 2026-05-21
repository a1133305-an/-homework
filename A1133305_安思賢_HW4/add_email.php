<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']); exit;
}

$data  = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$label = trim($data['label'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => "INVALID EMAIL FORMAT: $email"]); exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO recipients (email, label) VALUES (?, ?)");
    $stmt->execute([$email, $label ?: null]);
    echo json_encode([
        'success' => true,
        'message' => "TARGET ADDED: $email",
        'id'      => (int)$pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        echo json_encode(['success' => false, 'message' => "DUPLICATE ENTRY: $email already exists"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'DB ERROR: ' . $e->getMessage()]);
    }
}
