<?php
include_once(dirname(__FILE__) . '/../controller/user.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$validator->validate_request_method('get', 'Invalid request method');
$validator->validate_param_exist('id');


$user = new User();
query_response($user->getDetailsById($_GET['id']));
