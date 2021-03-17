<?php
include_once(dirname(__FILE__) . '/../controller/resources.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'resource');
$availableResources = ['active-venues', 'venue-time-slots'];
if (!in_array($data['resource'], $availableResources)) {
    send_response('fail', 'Invalid value <' . $param . '>', 400);
}
$resource = new Resources();
switch ($data['resource']) {
    case 'active-venues':
        query_response($resource->getActiveVenues());
        break;
    case 'venue-time-slots':
        $validator->validate_json_body($data, 'vendor_id');
        query_response($resource->getTimeSlotsOfVenue($data['vendor_id']));
        break;

    default:
        send_response('fail', 'Invalid value <' . $param . '>', 400);
        break;
}
