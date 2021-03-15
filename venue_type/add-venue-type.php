<?php
include_once(dirname(__FILE__) . '/../controller/venue_type.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'name');
$validator->validate_json_body($data, 'status');
$validator->validate_json_body($data, 'image');


$event = new VenueType();
function imagesaver($image_data)
{

    list($type, $data) = explode(';', $image_data); // exploding data for later checking and validating 

    if (preg_match('/^data:image\/(\w+);base64,/', $image_data, $list)) {
        $data = substr($data, strpos($data, ',') + 1);
        $type = strtolower($list[1]); // jpg, png, gif
        echo $type;
        if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
            throw new \Exception('invalid image type');
        }

        $data = base64_decode($data);

        if ($data === false) {
            throw new \Exception('base64_decode failed');
        }
    } else {
        throw new \Exception('did not match data URI with image data');
    }

    $imageName = time() . "." . $type;

    $fullname = dirname(__FILE__) . "/../images/venueType_images/" . $imageName;

    if (file_put_contents($fullname, $data)) {
        $result = $imageName;
    } else {
        $result =  "";
    }
    /* it will return image name if image is saved successfully 
    or it will return error on failing to save image. */
    return $result;
}

$imageName = imagesaver($data['image']);
query_response($event->addVenueType($data['name'], $data['status'], $imageName));
