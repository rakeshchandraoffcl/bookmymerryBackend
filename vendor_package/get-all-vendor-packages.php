<?php
include_once(dirname(__FILE__) . '/../controller/vendor_package.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

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

if (isset($_GET['all'])) {
    $all = $_GET['all'];
} else {
    $all = '';
}

$user = new VendorPackage();
query_response($user->getVendorPackages($count, $page, $search, $all));
