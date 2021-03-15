<?php
include_once(dirname(__FILE__) . '/../controller/vendor_query.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'vendor');
$validator->validate_json_body($data, 'user_name');
$validator->validate_json_body($data, 'user_email');
$validator->validate_json_body($data, 'user_phone_number');
$validator->validate_json_body($data, 'type');
if ($data['type'] === 'details') {
    $validator->validate_json_body($data, 'request');
}


$event = new VendorQuery();
query_response($event->addVendorQuery($data));
