<?php
include_once(dirname(__FILE__) . '/../controller/vendor_type.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');
include_once(dirname(__FILE__) . '/../utils/helper.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'name');
$validator->validate_json_body($data, 'status');
$validator->validate_json_body($data, 'description');


$event = new VendorType();
if (array_key_exists("image", $data)) {
    $result = imagesaver($data["image"], dirname(__FILE__) . "/../images/vendorType_images/");
    $image = $result;
}
query_response($event->addVendorType($data['name'], $data['status'], $data['description'], $image));
