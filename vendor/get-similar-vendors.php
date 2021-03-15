<?php
include_once(dirname(__FILE__) . '/../controller/vendor.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$validator->validate_request_method('get', 'Invalid request method');
$validator->validate_param_exist('type');
$validator->validate_param_exist('city');
$validator->validate_param_exist('exclude');



$user = new Vendor();
query_response($user->getSimilarVendors($_GET['exclude'], $_GET['type'], $_GET['city']));
