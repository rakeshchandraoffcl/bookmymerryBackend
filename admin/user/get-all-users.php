<?php
include_once(dirname(__FILE__) . '/../../controller/user.php');
include_once(dirname(__FILE__) . '/../../utils/validator.php');
include_once(dirname(__FILE__) . '/../../utils/response.php');

$validator = new Validator();
$validator->validate_request_method('get', 'Invalid request method');

if (isset($_GET['count'])) {
    $count = $_GET['count'];
} else {
    $count = 10;
}

if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 1;
}

if (isset($_GET['search'])) {
    $search = $_GET['search'];
} else {
    $search = '';
}

$user = new User();
query_response($user->getUsers($count, $page, $search));
