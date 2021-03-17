<?php
include_once(dirname(__FILE__) . '/../controller/venue.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'id');
$validator->validate_json_body($data, 'date');

$event = new Venue();
query_response($event->checkAvailableTimeSlots($data['id'], $data['date']));
