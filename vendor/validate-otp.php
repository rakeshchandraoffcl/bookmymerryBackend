<?php
session_start();
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');
include_once(dirname(__FILE__) . '/../utils/helper.php');
$validator = new Validator();
$validator->validate_request_method('get', 'Invalid request method');
$validator->validate_param_exist('mobile');
$validator->validate_param_exist('otp');


$mobile = '' . $_GET['mobile'] . '';
$otp = $_GET['otp'];

if (!isset($_SESSION['row_count_' . $mobile])) {
    send_response('fail', 'otp not matched', 401);
} else {
    if ($_SESSION['row_count_' . $mobile] == $otp) {
        session_destroy();
        send_response('success', 'OTP Matched Successfully', 200);
    } else {
        send_response('fail', 'otp not matched', 401);
    }
}
