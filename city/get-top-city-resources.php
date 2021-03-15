<?php
include_once(dirname(__FILE__) . '/../controller/city.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$validator->validate_request_method('get', 'Invalid request method');


$user = new City();
query_response($user->getCityResources());
