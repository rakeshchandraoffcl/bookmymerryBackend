<?php
include_once(dirname(__FILE__) . '/../../controller/user.php');
include_once(dirname(__FILE__) . '/../../utils/validator.php');
include_once(dirname(__FILE__) . '/../../utils/response.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'full_name');
$validator->validate_json_body($data, 'email');
$validator->validate_json_body($data, 'phone_number');
$validator->validate_json_body($data, 'password');
$validator->validate_json_body($data, 'status');
$validator->validate_json_body($data, 'number_verified');

$user = new User();
$hashedPassword = password_hash($data["password"], PASSWORD_DEFAULT);
query_response($user->signUpByAdmin($data['full_name'], $data['email'], $data['phone_number'], $hashedPassword, $data['status'], $data['number_verified']));
