<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';   // PDO + config constants
require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$ids  = array_filter(array_map('intval', $data['ids'] ?? []));

if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'NO TARGETS SELECTED']); exit;
}

// ─── Auto-generated Email Templates ─────────────────────────────────────────
$templates = [
    [
        'subject' => 'System Notification [AUTO] — ' . date('Y-m-d H:i:s'),
        'body'    =>
            "Greetings,\n\n" .
            "This is an automated system notification dispatched on " . date('Y-m-d H:i:s') . ".\n\n" .
            "All scheduled processes are running within normal parameters.\n" .
            "No action is required at this time.\n\n" .
            "-- MAILSYS Automated Dispatch Unit\n" .
            "   Timestamp: " . time(),
        'html'    =>
            "<div style='font-family:monospace;background:#030a04;color:#00ff41;padding:24px;border:1px solid #1a4d1e;'>" .
            "<h2 style='color:#00e5ff;'>◈ MAILSYS — System Notification</h2>" .
            "<p>This is an automated system notification dispatched on <strong>" . date('Y-m-d H:i:s') . "</strong>.</p>" .
            "<p>All scheduled processes are running within normal parameters.<br>No action is required at this time.</p>" .
            "<hr style='border-color:#1a4d1e;'><small style='color:#2d7a38;'>MAILSYS Automated Dispatch Unit // " . time() . "</small></div>"
    ],
    [
        'subject' => '[ALERT] Routine Security Broadcast — ' . date('D, d M Y'),
        'body'    =>
            "Security Broadcast — " . date('D, d M Y H:i:s') . "\n\n" .
            "Routine system integrity check has completed successfully.\n" .
            "No anomalies detected in the monitored network segments.\n\n" .
            "This message was generated automatically. Do not reply.\n\n" .
            "-- Automated Security Module v1.0",
        'html'    =>
            "<div style='font-family:monospace;background:#030a04;color:#00ff41;padding:24px;border:1px solid #1a4d1e;'>" .
            "<h2 style='color:#ffe600;'>⚠ MAILSYS — Security Broadcast</h2>" .
            "<p><strong>" . date('D, d M Y H:i:s') . "</strong></p>" .
            "<p>Routine system integrity check has completed successfully.<br>No anomalies detected in the monitored network segments.</p>" .
            "<p style='color:#2d7a38;'>This message was generated automatically. Do not reply.</p>" .
            "<hr style='border-color:#1a4d1e;'><small style='color:#2d7a38;'>Automated Security Module v1.0</small></div>"
    ],
    [
        'subject' => 'Scheduled Report: System Status — ' . date('Y-m-d'),
        'body'    =>
            "SCHEDULED REPORT\n" .
            "Generated: " . date('Y-m-d H:i:s') . "\n" .
            "────────────────────────────\n" .
            "STATUS  : OPERATIONAL\n" .
            "UPTIME  : NORMAL\n" .
            "ALERTS  : NONE\n" .
            "────────────────────────────\n\n" .
            "This report was dispatched automatically by MAILSYS.\n" .
            "Frequency: As configured by the operator.",
        'html'    =>
            "<div style='font-family:monospace;background:#030a04;color:#00ff41;padding:24px;border:1px solid #1a4d1e;'>" .
            "<h2 style='color:#00e5ff;'>◈ MAILSYS — Scheduled Report</h2>" .
            "<table style='border-collapse:collapse;width:100%;'>" .
            "<tr><td style='color:#2d7a38;padding:4px 12px 4px 0;'>GENERATED</td><td>" . date('Y-m-d H:i:s') . "</td></tr>" .
            "<tr><td style='color:#2d7a38;padding:4px 12px 4px 0;'>STATUS</td><td style='color:#00ff41;'>OPERATIONAL</td></tr>" .
            "<tr><td style='color:#2d7a38;padding:4px 12px 4px 0;'>UPTIME</td><td>NORMAL</td></tr>" .
            "<tr><td style='color:#2d7a38;padding:4px 12px 4px 0;'>ALERTS</td><td>NONE</td></tr>" .
            "</table><hr style='border-color:#1a4d1e;'>" .
            "<small style='color:#2d7a38;'>Dispatched automatically by MAILSYS</small></div>"
    ],
];

$template = $templates[array_rand($templates)];

// ─── Fetch selected recipients ───────────────────────────────────────────────
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id, email FROM recipients WHERE id IN ($placeholders)");
$stmt->execute($ids);
$recipients = $stmt->fetchAll();

if (empty($recipients)) {
    echo json_encode(['success' => false, 'message' => 'No matching recipients found in DB']); exit;
}

// ─── Send via PHPMailer ──────────────────────────────────────────────────────
$results = [];
$sent    = 0;
$failed  = 0;
$errors  = [];

foreach ($recipients as $r) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($r['email']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $template['subject'];
        $mail->Body    = $template['html'];
        $mail->AltBody = $template['body'];  // plain text fallback

        $mail->send();
        $sent++;
        $results[] = ['email' => $r['email'], 'sent' => true];

    } catch (Exception $e) {
        $failed++;
        $errors[]  = $r['email'] . ': ' . $mail->ErrorInfo;
        $results[] = ['email' => $r['email'], 'sent' => false, 'error' => $mail->ErrorInfo];
    }
}

echo json_encode([
    'success' => true,
    'message' => "DISPATCH COMPLETE — SENT: $sent | FAILED: $failed",
    'subject' => $template['subject'],
    'results' => $results,
    'errors'  => $errors,
    'ts'      => date('H:i:s')
]);
