<?php
include_once(dirname(__FILE__) . '/../controller/client.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'business');
$validator->validate_json_body($data, 'city');
$validator->validate_json_body($data, 'business_type');
$validator->validate_json_body($data, 'client_name');
$validator->validate_json_body($data, 'mobile');
$validator->validate_json_body($data, 'email');
$validator->validate_json_body($data, 'password');
$validator->validate_json_body($data, 'comments');
$validator->validate_json_body($data, 'object');


$event = new Client();
query_response($event->addClient($data));
