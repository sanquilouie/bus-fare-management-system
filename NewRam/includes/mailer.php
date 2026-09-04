<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../libraries/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libraries/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../libraries/PHPMailer/src/Exception.php';

function bfms_create_mailer(): PHPMailer\PHPMailer\PHPMailer
{
    $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = bfms_required_env('SMTP_HOST');
    $mailer->SMTPAuth = true;
    $mailer->Username = bfms_required_env('SMTP_USERNAME');
    $mailer->Password = bfms_required_env('SMTP_PASSWORD');
    $mailer->Port = (int) bfms_env('SMTP_PORT', '587');

    $encryption = strtolower((string) bfms_env('SMTP_ENCRYPTION', 'tls'));
    if ($encryption === 'ssl' || $encryption === 'smtps') {
        $mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
    } elseif ($encryption === 'tls' || $encryption === 'starttls') {
        $mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    }

    $mailer->setFrom(
        bfms_required_env('MAIL_FROM_ADDRESS'),
        bfms_env('MAIL_FROM_NAME', 'Bus Fare Management System')
    );

    return $mailer;
}

