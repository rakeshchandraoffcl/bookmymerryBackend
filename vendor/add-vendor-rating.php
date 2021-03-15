<?php
include_once(dirname(__FILE__) . '/../controller/vendor_rating.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');
$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'vendor');
$validator->validate_json_body($data, 'user');
$vendor = $data['vendor'];
$user = $data['user'];
if (array_key_exists('rating', $data)) {
    $rating = $data['rating'];
    if ($rating > 5 || $rating < 0) {
        send_response('fail', 'Invalid rating', 400);
    }
} else {
    $rating = null;
}
if (array_key_exists('comment', $data)) {
    $comment = $data['comment'];
} else {
    $comment = null;
}


$event = new VendorRating();
query_response($event->addRating($vendor, $user, $rating, $comment));
