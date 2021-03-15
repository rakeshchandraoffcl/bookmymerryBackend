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
if (array_key_exists('city', $data)) {
    $city = $data['city'];
} else {
    $city = null;
}
if (array_key_exists('capacity', $data)) {
    $capacity = $data['capacity'];
} else {
    $capacity = null;
}
if (array_key_exists('price', $data)) {
    $price = $data['price'];
} else {
    $price = null;
}
if (array_key_exists('events', $data)) {
    $events = $data['events'];
} else {
    $events = [];
}
if (array_key_exists('types', $data)) {
    $types = $data['types'];
} else {
    $types = [];
}
if (array_key_exists('amenities', $data)) {
    $amenities = $data['amenities'];
} else {
    $amenities = [];
}



$venue = new Venue();
$ids = $venue->filterVenueIds($city, $capacity, $price, $events, $types, $amenities);
query_response($venue->getVenuesByIds($ids));
