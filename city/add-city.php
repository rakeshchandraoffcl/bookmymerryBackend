<?php
include_once(dirname(__FILE__) . '/../controller/city.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');
include_once(dirname(__FILE__) . '/../utils/helper.php');

$validator = new Validator();
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_body_exist('city_name');
$validator->validate_body_exist('top_city');
$validator->validate_body_exist('is_active');


// Image upload
if (isset($_FILES['img'])) {
    $target_dir = "../images/city_images/";
    $t = time();
    $rand = rand();
    $name = $target_dir . $rand . '_' . basename($_FILES["img"]["name"]);
    $size = $_FILES["img"]["size"];
    $tmp_name = $_FILES["img"]["tmp_name"];
    $image_upload_status = image_upload($name, $tmp_name, $size);
    // print_r($image_upload_status);
    if ($image_upload_status["status"] === "success") {
        $imageName = $rand . '_' . basename($_FILES["img"]["name"]);
    } else {
        send_response('fail', $image_upload_status['error'], 422);
    }
} else {
    $imageName = '';
}

$city_slug = slugify($_POST['city_name']);
$user = new City();
query_response($user->addCity($_POST['city_name'], $imageName, $city_slug, $_POST['top_city'], $_POST['is_active']));
