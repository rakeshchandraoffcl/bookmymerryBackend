<?php
include_once(dirname(__FILE__) . '/../controller/venue_type.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');
include_once(dirname(__FILE__) . '/../utils/helper.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('patch', 'Invalid request method');
$validator->validate_json_body($data, 'id');

// $validator->validate_json_body($data, 'email');
// $validator->validate_json_body($data, 'phone_number');
// $validator->validate_json_body($data, 'password');

$event = new VenueType();


// $hashedPassword = password_hash($data["password"], PASSWORD_DEFAULT);
if (array_key_exists("image", $data)) {
    $result = imagesaver($data["image"], dirname(__FILE__) . "/../images/venueType_images/");
    if ($result != '') {
        $data['image'] = $result;
    }
}
query_response($event->updateVenueType($data, $data['id']));
