<?php
include_once(dirname(__FILE__) . '/../controller/vendor.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$validator->validate_request_method('get', 'Invalid request method');
$validator->validate_param_exist('id');


$venue = new Vendor();
query_response($venue->getVendorDetails($_GET['id']));
