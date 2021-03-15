<?php
include_once(dirname(__FILE__) . '/../controller/venue_query.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'event');
$validator->validate_json_body($data, 'date');
$validator->validate_json_body($data, 'time_slot');
$validator->validate_json_body($data, 'city');
$validator->validate_json_body($data, 'max_guests');
$validator->validate_json_body($data, 'min_price');
$validator->validate_json_body($data, 'max_price');
$validator->validate_json_body($data, 'user_name');
$validator->validate_json_body($data, 'email');
$validator->validate_json_body($data, 'phone_number');



$event = new VenueQuery();
query_response($event->addVenueQuery($data['event'], $data['date'], $data['time_slot'], $data['city'], $data['max_guests'], $data['min_price'], $data['max_price'], $data['user_name'], $data['email'], $data['phone_number']));
