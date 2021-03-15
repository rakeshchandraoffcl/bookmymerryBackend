<?php
include_once(dirname(__FILE__) . '/../controller/venue_query.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('patch', 'Invalid request method');
$validator->validate_json_body($data, 'id');

// $validator->validate_json_body($data, 'email');
// $validator->validate_json_body($data, 'phone_number');
// $validator->validate_json_body($data, 'password');

$event = new VenueQuery();


// $hashedPassword = password_hash($data["password"], PASSWORD_DEFAULT);
query_response($event->updateVenueQuery($data, $data['id']));