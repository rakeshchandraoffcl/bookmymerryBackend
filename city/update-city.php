<?php
include_once(dirname(__FILE__) . '/../controller/city.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');
include_once(dirname(__FILE__) . '/../utils/helper.php');

$validator = new Validator();
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_body_exist('id');


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
    $imageName = null;
}

$data = array();


if (isset($_POST['city_name'])) {
    $data['city_name'] = $_POST['city_name'];
}

if ($imageName) {
    $data['city_img'] = $imageName;
}

if (isset($_POST['city_slug'])) {
    $data['city_slug'] = $_POST['city_slug'];
}
if (isset($_POST['top_city'])) {
    $data['top_city'] = $_POST['top_city'] ? 1 : 0;
}
if (isset($_POST['is_active'])) {
    $data['is_active'] = $_POST['is_active'] ? 1 : 0;
}

$city = new City();
query_response($city->updateCity($data, $_POST['id']));
