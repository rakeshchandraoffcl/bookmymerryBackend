<?php
include_once(dirname(__FILE__) . '/../../controller/venue.php');
include_once(dirname(__FILE__) . '/../../utils/validator.php');
include_once(dirname(__FILE__) . '/../../utils/response.php');
include_once(dirname(__FILE__) . '/../../utils/helper.php');

$validator = new Validator();
$validator->validate_request_method('post', 'Invalid request method');
$fields = array(
    "name",
    "city",
    "location",
    "about",
    "phone_number",
    "opening_time",
    "landmark",
    "max_guest_hall",
    "max_guest_lawn",
    "max_seat_guest_hall",
    "max_seat_guest_lawn",
    "changing_room",
);

$otherFields = array(
    "event",
    "type",
    "usp",
    "amenity",
    "time_slot",
    "decoration",
    "policy",
    "menu",
);

$data = array();
$otherData = array();


foreach ($fields as $val) {
    $validator->validate_body_exist($val);
    $data[$val] = $_POST[$val];
}
foreach ($otherFields as $val) {
    $validator->validate_body_exist($val);
    $otherData[$val] = $_POST[$val];
}
// print_r($_POST['time_slots']);

$venue = new Venue();
query_response($venue->addVenue($data, $otherData, $_FILES));
