<?php
include_once(dirname(__FILE__) . '/response.php');
class Validator
{

    function validate_request_method($method, $message)
    {
        switch ($method) {
            case 'get':
                if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                    return true;
                } else {
                    send_response('fail', $message, 405);
                    die();
                }
                break;
            case 'post':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    return true;
                } else {
                    send_response('fail', $message, 405);
                    die();
                }
                break;
            case 'patch':
                if ($_SERVER['REQUEST_METHOD'] == 'PATCH') {
                    return true;
                } else {
                    send_response('fail', $message, 405);
                    die();
                }
                break;

            default:
                # code...
                break;
        }
    }

    function validate_json_body($data, $name)
    {
        if (!$data || !array_key_exists($name, $data)) {
            send_response('fail', 'Missing parameter <' . $name . '>', 400);
            die();
        }
    }

    function validate_param_exist($param)
    {
        if (!isset($_GET[$param])) {
            send_response('fail', 'Missing parameter <' . $param . '>', 400);
            die();
        }
    }
    function validate_body_exist($body)
    {
        if (!isset($_POST[$body])) {
            send_response('fail', 'Missing parameter <' . $body . '>', 400);
            die();
        }
    }

    function validate_either_body_exist($data, $params)
    {
        if (!$data) {
            $data = [];
        }
        $test_pass = false;
        foreach ($params as &$value) {
            if (array_key_exists($value, $data)) {
                $test_pass = true;
            }
        }
        if (!$test_pass) {
            send_response('fail', 'One of the following param required <' . implode(" | ", $params) . '>', 400);
            die();
        }
    }

    function validate_number($phone_number, $length)
    {
        if (!is_numeric($phone_number) || ceil(log10($phone_number)) != $length) {
            send_response('fail', 'Not a valid number', 400);
            die();
        }
    }

    function validate_messsage_status($result)
    {
        if (0 === strpos($result, 'OK')) {
            send_response('success', null);
        } else {
            send_response('fail', 'Unable to send message', 200);
        }
    }

    function validate_should_be_only($param, $values)
    {
        if (!in_array($param, $values)) {
            send_response('fail', 'Invalid value <' . $param . '>', 200);
            die();
        }
    }
}
