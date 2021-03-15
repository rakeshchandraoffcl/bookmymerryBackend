<?php
include_once(dirname(__FILE__) . '/../controller/user.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('patch', 'Invalid request method');
$validator->validate_json_body($data, 'id');
if (array_key_exists('password', $data)) {
    $data['password'] = password_hash($data["password"], PASSWORD_DEFAULT);
}
// $validator->validate_json_body($data, 'email');
// $validator->validate_json_body($data, 'phone_number');
// $validator->validate_json_body($data, 'password');

$user = new User();


// $hashedPassword = password_hash($data["password"], PASSWORD_DEFAULT);
query_response($user->updateUser($data));
