<?php

include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');
include_once(dirname(__FILE__) . '/../utils/helper.php');

$validator = new Validator();
$validator->validate_request_method('get', 'Invalid request method');
$validator->validate_param_exist('mobile');

$otp = mt_rand(1000, 9999);
$mobile = $_GET['mobile'];
$otp = mt_rand(1000, 9999);
$message = 'Your bookmymerry mobile verification code is ' . $otp;

// print_r($_SESSION);
$result = send_text($mobile, $message);
print_r($result);
if (substr($result, 0, 2) === "OK") {
    // if (true) {
    session_start();
    if (isset($_SESSION['row_count_' . $mobile])) {
        unset($_SESSION['row_count_' . $mobile]);
    }
    $_SESSION['row_count_' . $mobile] = $otp;
    send_response('success', $otp . 'OTP sent successfully', 200);
} else {
    send_response('fail', 'Unable to send otpp', 500);
}
