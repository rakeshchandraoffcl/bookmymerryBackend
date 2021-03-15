<?php
include_once(dirname(__FILE__) . '/../controller/resources.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$validator->validate_request_method('get', 'Invalid request method');



$user = new Resources();
query_response($user->getResources());
