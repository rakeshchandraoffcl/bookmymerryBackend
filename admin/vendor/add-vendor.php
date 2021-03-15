<?php
include_once(dirname(__FILE__) . '/../../controller/vendor.php');
include_once(dirname(__FILE__) . '/../../utils/validator.php');
include_once(dirname(__FILE__) . '/../../utils/response.php');
include_once(dirname(__FILE__) . '/../../utils/helper.php');

$validator = new Validator();
$validator->validate_request_method('post', 'Invalid request method');
$fields = array(
    "name",
    "description",
    "city",
    "address",
    "phone_number",
    "email",
    "wh_number",
    "main_service",
    "booking_policy",
    "cancellation_policy",
    "terms",
    "type",
);

$otherFields = array(
    "package",
    "faq",
    "gallery"
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

$venue = new Vendor();
query_response($venue->addVendor($data, $otherData, $_FILES));
