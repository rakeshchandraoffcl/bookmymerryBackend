<?php
include_once(dirname(__FILE__) . '/../controller/event.php');
include_once(dirname(__FILE__) . '/../utils/validator.php');
include_once(dirname(__FILE__) . '/../utils/response.php');

$validator = new Validator();
$data = json_decode(file_get_contents('php://input'), true);
$validator->validate_request_method('post', 'Invalid request method');
$validator->validate_json_body($data, 'name');
$validator->validate_json_body($data, 'status');


$event = new Event();
query_response($event->addEvent($data['name'], $data['status']));
