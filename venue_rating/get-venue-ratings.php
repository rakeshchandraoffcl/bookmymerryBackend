<?php
include_once(dirname(__FILE__) . '/../controller/venue_rating.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$validator->validate_request_method('get', 'Invalid request method');
$validator->validate_param_exist('venue_id');

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

$user = new VenueRating();
query_response($user->getRatingsOfAVenue($count, $page, $search, $_GET['venue_id']));
