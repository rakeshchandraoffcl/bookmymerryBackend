<?php

function send_response($type, $data, $code = 200)
{
    header('Content-Type: application/json');
    http_response_code($code);
    if ($type === 'success') {
        $array = array(
            "status" => "success"
        );
        if ($data !== null) {
            $array["data"] = $data;
        }
        print_r(json_encode($array));
        die();
    } else {
        $array = array(
            "status" => "fail"
        );
        if ($data) {
            $array["reason"] = $data;
        }
        print_r(json_encode($array));
        die();
    }
}

function query_response($query_result)
{
    if ($query_result['status'] == 'success') {
        send_response('success', ($query_result['data'] || gettype($query_result['data']) == 'array') ? $query_result['data'] : null, 200);
        die();
    } else {
        send_response('fail', $query_result['error'] ? $query_result['error'] : 'Opps! something goes wrong', 500);
        die();
    }
}

function throw_error_if_any($query_result, $errMessage = null)
{
    if ($query_result['status'] == 'fail') {
        send_response('fail', $errMessage ? $errMessage : $query_result['error'], 500);
        die();
    }
}
