<?php
// بارگذاری کلاس PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// بارگذاری تنظیمات از فایل کانفیگ
$config = require 'config.php';

$mail = new PHPMailer(true);  // ایجاد یک نمونه از PHPMailer

try {
    // تنظیمات سرور SMTP جیمیل از فایل کانفیگ
    $mail->isSMTP();
    $mail->Host = $config['smtp_host'];  // سرور SMTP جیمیل
    $mail->SMTPAuth = $config['smtp_auth'];
    $mail->Username = $config['smtp_username'];  // ایمیل جیمیل شما
    $mail->Password = $config['smtp_password'];  // رمز عبور اپلیکیشن جیمیل شما
    $mail->SMTPSecure = $config['smtp_secure'];
    $mail->Port = $config['smtp_port'];  // پورت SMTP جیمیل

    // تنظیمات ایمیل
    $mail->setFrom($config['from_email'], $config['from_name']);  // ایمیل فرستنده و نام

    // بررسی وجود فایل JSON
    if (!file_exists('emails.json')) {
        throw new Exception('فایل ایمیل‌ها یافت نشد');
    }

    // خواندن محتوای فایل JSON
    $jsonContent = file_get_contents('emails.json');  // خواندن فایل JSON
    $emailList = json_decode($jsonContent, true);  // تبدیل JSON به آرایه

    // بررسی اینکه آیا داده‌های JSON به درستی بارگذاری شده‌اند
    if ($emailList === null) {
        throw new Exception('فرمت JSON نادرست است');
    }

    // بررسی اضافه کردن آدرس‌های ایمیل
    foreach ($emailList['emails'] as $email) {
        if (is_array($email)) {
            // اگر $email یک آرایه است، همه ایمیل‌ها را اضافه کن
            foreach ($email as $individualEmail) {
                $mail->addAddress($individualEmail);  // اضافه کردن ایمیل به فهرست گیرندگان
            }
        } else {
            $mail->addAddress($email);  // اضافه کردن ایمیل به فهرست گیرندگان
        }
    }

    // بارگذاری محتوای HTML ایمیل از فایل
    $mail->isHTML(true);
    $mail->Subject = mb_encode_mimeheader('سیاسفید 6 - رویداد یلدایی', 'UTF-8', 'B');
    $mail->Body = file_get_contents('email_content.html');  // بارگذاری محتوا از فایل

    // محتوای متنی برای ایمیل (در صورت عدم پشتیبانی از HTML)
    $mail->AltBody = 'ویژه برنامه یلدایی سیاسفید ۶ - تاریخ: جمعه ۳۰ آذر ماه، ساعت ۹ الی ۱۵ - محل برگزاری: تهران، شهرک غرب، رستوران باماریا - ثبت‌نام: https://B2n.ir/siasefid-off';

    // ارسال ایمیل
    $mail->send();
    echo 'پیام ارسال شد';
} catch (Exception $e) {
    echo "پیام ارسال نشد. خطای Mailer: {$mail->ErrorInfo}";
    // ثبت خطا در فایل لاگ
    error_log($e->getMessage(), 3, '/path/to/error.log');
}
?>