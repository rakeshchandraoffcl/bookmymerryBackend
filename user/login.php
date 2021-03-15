<?php
include_once(dirname(__FILE__) . '/../controller/user.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'email');
$validator->validate_json_body($data, 'password');

$user = new User();
$userDetails = $user->getDetailsByEmail($data['email']);
throw_error_if_any($userDetails, 'Incorrect email or password');
if ($data['password'] === $userDetails['data']['password']) {
    query_response($user->getDetailsById($userDetails['data']['id']));
} else {
    send_response('fail', 'Incorrect email or password', 401);
}
