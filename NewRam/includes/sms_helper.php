<?php

require_once __DIR__ . '/config.php';

function sendSMS($number, $message) {
    $apiKey = bfms_env('SMS_API_KEY');
    $apiUrl = bfms_env('SMS_API_URL');
    $senderName = bfms_env('SMS_SENDER_NAME', 'BFMS');

    if ($apiKey === null || $apiUrl === null) {
        error_log('SMS delivery skipped because provider configuration is incomplete.');
        return false;
    }

    $ch = curl_init();
    $parameters = array(
        'apikey' => $apiKey,
        'number' => $number,
        'message' => $message,
        'sendername' => $senderName
    );
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    if ($output === false) {
        error_log('SMS provider request failed: ' . curl_error($ch));
    }
    curl_close($ch);

    return $output;
}
?>
