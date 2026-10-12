<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// SMTP credentials live in smtp-config.php (gitignored).
// Copy smtp-config.example.php -> smtp-config.php and fill it in on the server.
$configPath = __DIR__ . '/smtp-config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Mail is not configured on this server.']);
    exit;
}
require_once $configPath;

require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode(file_get_contents('php://input'), true);

$name     = htmlspecialchars(strip_tags($data['name']     ?? ''));
$email    = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$product  = htmlspecialchars(strip_tags($data['product']  ?? ''));
$company  = htmlspecialchars(strip_tags($data['company']  ?? ''));
$phone    = htmlspecialchars(strip_tags($data['phone']    ?? ''));
$country  = htmlspecialchars(strip_tags($data['country']  ?? ''));
$quantity = htmlspecialchars(strip_tags($data['quantity'] ?? ''));
$message  = htmlspecialchars(strip_tags($data['message']  ?? ''));

if (!$name || !$email || !$product) {
    http_response_code(400);
    echo json_encode(['error' => 'Please fill in your name, email, and product of interest.']);
    exit;
}

$rows = [
    ['Full Name',           $name],
    ['Company',             $company  ?: '—'],
    ['Email',               $email],
    ['Phone',               $phone    ?: '—'],
    ['Country',             $country  ?: '—'],
    ['Product of Interest', $product],
    ['Quantity (MT)',       $quantity ?: '—'],
    ['Message',             $message  ?: '—'],
];

$tableRows = '';
foreach ($rows as [$key, $val]) {
    $tableRows .= "
    <tr>
      <td style='padding:10px 16px;font-weight:600;color:#122844;background:#f3eee3;white-space:nowrap;border-bottom:1px solid #e4ddcd;'>$key</td>
      <td style='padding:10px 16px;color:#16202c;border-bottom:1px solid #e4ddcd;'>$val</td>
    </tr>";
}

$html = "<!DOCTYPE html>
<html><head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;font-family:Arial,sans-serif;background:#f5f5f5;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f5f5f5;padding:32px 0;'>
    <tr><td align='center'>
      <table width='600' cellpadding='0' cellspacing='0' style='background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);'>
        <tr><td style='background:#0d1f2d;padding:28px 36px;'>
          <p style='margin:0;font-size:22px;font-weight:700;color:#dfa83c;'>SPEEDLIFE</p>
          <p style='margin:4px 0 0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:rgba(255,255,255,0.6);'>General Trading LLC</p>
        </td></tr>
        <tr><td style='padding:28px 36px 8px;'>
          <p style='margin:0;font-size:18px;font-weight:700;color:#0d1f2d;'>New Inquiry Received</p>
          <p style='margin:8px 0 0;font-size:14px;color:#5d6675;'>Submitted via the Speedlife General Trading website</p>
        </td></tr>
        <tr><td style='padding:20px 36px 36px;'>
          <table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #e4ddcd;border-radius:8px;overflow:hidden;'>
            $tableRows
          </table>
        </td></tr>
        <tr><td style='background:#f3eee3;padding:18px 36px;font-size:13px;color:#5d6675;'>
          Reply directly to this email to respond to $name.
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>";

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = SMTP_PORT;
    $mail->Timeout    = 10;

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress(MAIL_TO);
    $mail->addReplyTo($email, $name);

    $mail->isHTML(true);
    $mail->Subject = "New Inquiry: $product — $name" . ($company ? " ($company)" : '');
    $mail->Body    = $html;

    $mail->send();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed: ' . $e->getMessage()]);
}
