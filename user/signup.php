<?php
include_once(dirname(__FILE__) . '/../controller/user.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');
include_once(dirname(__FILE__) . '/../utils/helper.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'full_name');
$validator->validate_json_body($data, 'email');
$validator->validate_json_body($data, 'phone_number');
$validator->validate_json_body($data, 'password');

$user = new User();
$signup = $user->signUp($data['full_name'], $data['email'], $data['phone_number'], $data["password"]);
if ($signup['status'] === 'success') {
    $otp = mt_rand(1000, 9999);
    $message = 'Your bookmymerry mobile verification code is ' . $otp;
    $result = send_text($signup['data']['phone_number'], $message);
    if (substr($result, 0, 2) === "OK") {
        $updateUser = $user->setOtp($otp, $signup['data']['id']);
        send_response('success', 'OTP sent successfully', 200);
    }
}
query_response($signup);
