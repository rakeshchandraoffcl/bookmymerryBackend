<?php
include_once(dirname(__FILE__) . '/../controller/user.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');
include_once(dirname(__FILE__) . '/../utils/helper.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'phone_number');

$user = new User();
$userDetails = $user->getDetailsByPhone($data['phone_number']);
throw_error_if_any($userDetails, 'Incorrect phone number');
if ($userDetails['data']['status'] !== 1) {
    send_response('fail', 'Your account is not active please contact admin', 401);
}
$otp = mt_rand(1000, 9999);
$message = 'Your bookmymerry mobile verification code is ' . $otp;
$result = send_text($data['phone_number'], $message);
if (substr($result, 0, 2) === "OK") {
    $updateUser = $user->setOtp($otp, $userDetails['data']['id']);
    send_response('success', 'OTP sent successfully', 200);
} else {
    send_response('fail', 'Unable to send otp', 500);
}
