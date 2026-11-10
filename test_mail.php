<?php
declare(strict_types=1);

// =================================================================
// STANDALONE MAIL TEST — not wired into the QA system in any way.
// Just two files: this one + phpmailer.php in the same folder.
// Run it directly in the browser to check if your Office 365
// mailbox can send email over SMTP.
// =================================================================

// ---- 1. EDIT THESE WITH YOUR OFFICE 365 CREDENTIALS ------------
$MAIL_HOST     = 'smtp.office365.com';
$MAIL_PORT     = 587;
$MAIL_USERNAME = 'test@yourserver.com'; // <-- your office 365 email
$MAIL_PASSWORD = 'abc123';               // <-- your office 365 password
$MAIL_FROM     = $MAIL_USERNAME;                     // usually same as username
$MAIL_FROM_NAME = 'Mail Test';
// -----------------------------------------------------------------

require_once __DIR__ . '/phpmailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\SMTP;

$result = null;
$sentTo = '';
$log    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sentTo = trim($_POST['recipient'] ?? '');

    if (!filter_var($sentTo, FILTER_VALIDATE_EMAIL)) {
        $result = ['success' => false, 'error' => 'Please enter a valid email address.'];
    } else {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $MAIL_HOST;
            $mail->Port       = $MAIL_PORT;
            $mail->SMTPAuth   = true;
            $mail->Username   = $MAIL_USERNAME;
            $mail->Password   = $MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Office 365 uses STARTTLS on 587

            // Show the raw SMTP conversation so you can see exactly what happens
            $mail->SMTPDebug   = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function (string $str) use (&$log): void {
                $log .= $str . "\n";
            };

            $mail->setFrom($MAIL_FROM, $MAIL_FROM_NAME);
            $mail->addAddress($sentTo);

            $mail->isHTML(true);
            $mail->Subject = 'Action Required: Open Issues Still in WIP or Not Solved';
            $mail->Body    = '<p>Some raised issues remain in WIP/Not solved status. Kindly review and share the latest update. <br><br>
            Synergies Bangladesh <br><br>
            Powered By: QA System<br><br>


************ This is a system-generated email and does not require a reply ****************** 

            
            </p>';
            $mail->AltBody = 'This is a test email sent from a standalone PHP script to confirm SMTP sending works.';

            $mail->send();
            $result = ['success' => true, 'error' => null];
        } catch (PHPMailerException $e) {
            $result = ['success' => false, 'error' => $mail->ErrorInfo];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Standalone Mail Test</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background: #f4f5f7; margin: 0; padding: 40px 16px; }
        .box { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 28px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        h2 { margin-top: 0; }
        .muted { color: #666; font-size: 14px; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
        .alert-success { background: #e6f6ec; color: #14532d; border: 1px solid #a7d8b8; }
        .alert-danger { background: #fde8e8; color: #7a1212; border: 1px solid #f3b4b4; }
        input[type=email] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        button { background: #2563eb; color: #fff; border: none; padding: 10px 18px; border-radius: 6px; font-size: 14px; cursor: pointer; margin-top: 12px; }
        button:hover { background: #1d4ed8; }
        pre { background: #111827; color: #e5e7eb; padding: 14px; border-radius: 6px; font-size: 12px; white-space: pre-wrap; word-break: break-word; max-height: 400px; overflow-y: auto; }
        label { font-size: 13px; font-weight: 600; display: block; margin-bottom: 6px; }
    </style>
</head>
<body>
<div class="box">
    <h2>📧 Standalone SMTP Mail Test</h2>
    <p class="muted">Edit the credentials at the top of this file, then send a test email below.</p>

    <?php if ($result !== null): ?>
        <?php if ($result['success']): ?>
            <div class="alert alert-success">✅ Sent successfully to <strong><?= htmlspecialchars($sentTo) ?></strong>. Check the inbox (and spam folder).</div>
        <?php else: ?>
            <div class="alert alert-danger">❌ Failed: <strong><?= htmlspecialchars((string)$result['error']) ?></strong></div>
        <?php endif; ?>
    <?php endif; ?>

    <form method="POST">
        <label>Send test email to:</label>
        <input type="email" name="recipient" required placeholder="you@example.com" value="<?= htmlspecialchars($sentTo) ?>">
        <button type="submit">Send Test Email</button>
    </form>

    <?php if ($log !== ''): ?>
        <p class="muted" style="margin-top:20px;"><strong>SMTP debug log:</strong></p>
        <pre><?= htmlspecialchars($log) ?></pre>
    <?php endif; ?>
</div>
</body>
</html>
