<?php
function sendSMS($number, $message) {
    $ch = curl_init();
    $parameters = array(
        'apikey' => 'REDACTED_SMS_API_KEY', // Your API KEY
        'number' => $number,
        'message' => $message,
        'sendername' => 'RAMSTAR'
    );
    curl_setopt($ch, CURLOPT_URL, 'https://semaphore.co/api/v4/messages');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);

    return $output;
}
?>
