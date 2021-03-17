<?php
include_once(dirname(__FILE__) . '/../controller/venue.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$validator->validate_request_method('get', 'Invalid request method');
$validator->validate_param_exist('city_id');
$validator->validate_param_exist('exclude_id');


$venue = new Venue();
$ids = $venue->getVenuesOfACity($_GET['city_id']);
if (in_array($_GET['exclude_id'], $ids)) {
    unset($ids[array_search($_GET['exclude_id'], $ids)]);
}
query_response($venue->getVenuesByIds($ids));
