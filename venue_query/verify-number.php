<?php
include_once(dirname(__FILE__) . '/../controller/venue_query.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'id');
$validator->validate_json_body($data, 'otp');




$event = new VenueQuery();
query_response($event->verifyNumber($data['id'], $data['otp']));
