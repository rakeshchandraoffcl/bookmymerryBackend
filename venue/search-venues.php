<?php
include_once(dirname(__FILE__) . '/../controller/venue.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
// $validator->validate_json_body($data, 'name');
// $validator->validate_json_body($data, 'status');
// $validator->validate_json_body($data, 'price');
$data = $data ? $data : array();
if (array_key_exists('events', $data) && count($data['events']) > 0) {
    $events = $data['events'];
} else {
    $events = null;
}
if (array_key_exists('types', $data) && count($data['types']) > 0) {
    $types = $data['types'];
} else {
    $types = null;
}
if (array_key_exists('amenities', $data) && count($data['amenities']) > 0) {
    $amenities = $data['amenities'];
} else {
    $amenities = null;
}
if (array_key_exists('city', $data)) {
    $city = $data['city'];
} else {
    $city = null;
}


$event = new Venue();
query_response($event->searchVenues($events, $types, $amenities, $city));
