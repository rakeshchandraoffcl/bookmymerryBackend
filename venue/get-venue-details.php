<?php
include_once(dirname(__FILE__) . '/../controller/venue.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$validator->validate_request_method('get', 'Invalid request method');
$validator->validate_param_exist('id');


$venue = new Venue();
query_response($venue->getVenueDetails($_GET['id']));
