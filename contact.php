<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$name    = htmlspecialchars(strip_tags($data['name']     ?? ''));
$email   = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$product = htmlspecialchars(strip_tags($data['product']  ?? ''));
$company = htmlspecialchars(strip_tags($data['company']  ?? ''));
$phone   = htmlspecialchars(strip_tags($data['phone']    ?? ''));
$country = htmlspecialchars(strip_tags($data['country']  ?? ''));
$quantity= htmlspecialchars(strip_tags($data['quantity'] ?? ''));
$message = htmlspecialchars(strip_tags($data['message']  ?? ''));

if (!$name || !$email || !$product) {
    http_response_code(400);
    echo json_encode(['error' => 'Please fill in your name, email, and product of interest.']);
    exit;
}

$to      = 'sales@speedlifeexports.com';
$subject = "New Inquiry: $product — $name" . ($company ? " ($company)" : '');

$rows = [
    ['Full Name',          $name],
    ['Company',            $company  ?: '—'],
    ['Email',              $email],
    ['Phone',              $phone    ?: '—'],
    ['Country',            $country  ?: '—'],
    ['Product of Interest',$product],
    ['Quantity (MT)',      $quantity ?: '—'],
    ['Message',            $message  ?: '—'],
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
<html>
<head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;font-family:Arial,sans-serif;background:#f5f5f5;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background:#f5f5f5;padding:32px 0;'>
    <tr><td align='center'>
      <table width='600' cellpadding='0' cellspacing='0' style='background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);'>
        <tr>
          <td style='background:#0d1f2d;padding:28px 36px;'>
            <p style='margin:0;font-size:22px;font-weight:700;color:#dfa83c;letter-spacing:-0.01em;'>SPEEDLIFE</p>
            <p style='margin:4px 0 0;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:rgba(255,255,255,0.6);'>General Trading LLC</p>
          </td>
        </tr>
        <tr>
          <td style='padding:28px 36px 8px;'>
            <p style='margin:0;font-size:18px;font-weight:700;color:#0d1f2d;'>New Inquiry Received</p>
            <p style='margin:8px 0 0;font-size:14px;color:#5d6675;'>Submitted via the Speedlife General Trading website</p>
          </td>
        </tr>
        <tr>
          <td style='padding:20px 36px 36px;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #e4ddcd;border-radius:8px;overflow:hidden;'>
              $tableRows
            </table>
          </td>
        </tr>
        <tr>
          <td style='background:#f3eee3;padding:18px 36px;font-size:13px;color:#5d6675;'>
            Reply directly to this email to respond to $name.
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: Speedlife Website <noreply@speedlifeexports.com>\r\n";
$headers .= "Reply-To: $name <$email>\r\n";

if (mail($to, $subject, $html, $headers)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send. Please email us directly at sales@speedlifeexports.com']);
}
