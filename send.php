<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
$config = require 'config.php';

// بررسی متد POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('<div class="alert alert-danger">دسترسی غیرمجاز</div>');
}

$subject = trim($_POST['subject'] ?? '');
$htmlBody = $_POST['html_body'] ?? '';
$altBody = trim($_POST['alt_body'] ?? 'این یک ایمیل HTML است.');

if (empty($subject) || empty($htmlBody)) {
    die('<div class="alert alert-warning">عنوان و محتوای HTML الزامی است.</div>');
}

// --- پردازش لیست ایمیل ---
$recipients = [];

// 1. اگر فایل آپلود شده
if (isset($_FILES['email_list']) && $_FILES['email_list']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['email_list']['tmp_name'];
    $ext = pathinfo($_FILES['email_list']['name'], PATHINFO_EXTENSION);

    if ($ext === 'json') {
        $json = json_decode(file_get_contents($file), true);
        if (isset($json['emails'])) {
            foreach ($json['emails'] as $e) {
                if (is_array($e)) $recipients = array_merge($recipients, $e);
                else $recipients[] = $e;
            }
        }
    } elseif ($ext === 'txt') {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $recipients = array_map('trim', $lines);
    }
} else {
    // 2. استفاده از emails.json
    if (file_exists('emails.json')) {
        $json = json_decode(file_get_contents('emails.json.json'), true);
        if (isset($json['emails'])) {
            foreach ($json['emails'] as $e) {
                if (is_array($e)) $recipients = array_merge($recipients, $e);
                else $recipients[] = $e;
            }
        }
    }
}

if (empty($recipients)) {
    die('<div class="alert alert-danger">هیچ ایمیلی برای ارسال پیدا نشد!</div>');
}

// حذف تکراری و فیلتر معتبر
$recipients = array_filter(array_unique($recipients), 'filter_var', FILTER_VALIDATE_EMAIL);

if (empty($recipients)) {
    die('<div class="alert alert-danger">هیچ ایمیل معتبری پیدا نشد!</div>');
}

$success = 0;
$failed = 0;
$errors = [];

foreach ($recipients as $email) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $config['smtp_host'];
        $mail->SMTPAuth   = $config['smtp_auth'];
        $mail->Username   = $config['smtp_username'];
        $mail->Password   = $config['smtp_password'];
        $mail->SMTPSecure = $config['smtp_secure'];
        $mail->Port       = $config['smtp_port'];
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody;

        $mail->send();
        $success++;
    } catch (Exception $e) {
        $msg = "خطا برای $email: " . $mail->ErrorInfo;
        $errors[] = $msg;
        error_log($msg . PHP_EOL, 3, 'errors.log');
        $failed++;
    }
    // پاک کردن برای ایمیل بعدی
    $mail->clearAddresses();
}

// --- نمایش نتیجه ---
echo "<div class='alert alert-success'>ارسال تکمیل شد!</div>";
echo "<p><strong>موفق: $success</strong> | <strong>ناموفق: $failed</strong></p>";

if ($failed > 0) {
    echo "<details><summary>جزئیات خطاها ($failed)</summary><ul>";
    foreach ($errors as $e) echo "<li>$e</li>";
    echo "</ul></details>";
}
?>
