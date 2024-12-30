<?php
// بارگذاری کلاس‌های PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// بارگذاری تنظیمات از فایل کانفیگ
return [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_auth' => true,
    'smtp_username' => 'm4tinbeigi@gmail.com',
    'smtp_password' => '**** **** **** ****',
    'smtp_secure' => PHPMailer::ENCRYPTION_STARTTLS,  // مطمئن شوید که PHPMailer به درستی بارگذاری شده باشد
    'smtp_port' => 587,
    'from_email' => 'm4tinbeigi@gmail.com',
    'from_name' => 'Rick Sanchez',
];
?>
