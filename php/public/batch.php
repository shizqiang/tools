<?php
use utils\db\Storage;

require __DIR__ . '/../autoload.php';

function get($name) {
    return isset($_GET[$name]) ? $_GET[$name] : NULL;
}

function post($name) {
    return isset($_POST[$name]) ? $_POST[$name] : NULL;
}

function json($data, $message, $code) {
    header('Content-Type: application/json');
    print json_encode(['data' => $data, 'message' => $message, 'code' => $code]);
}

function json_failed($message = 'UNKNOWN_EXCEPTION', $code = 1) {
    json(null, $message, $code);
}

function json_ok($data = null, $message = 'SUCCESS', $code = 0) {
    json($data, $message, $code);
}

function auth($appKey, $appSecret) {
    return true;
}

// 码批次申请 
function apply() {
    $data = new stdClass();
    $data->name = post('name');
    $data->prefix = post('prefix');
    $data->batch_no = post('batch_no');
    $data->len = post('len');
    $data->file_num = post('file_num');
    $data->remark = post('remark');
    if (empty($data->name)) {
        return json_failed('name字段不能为空');
    }
    if (empty($data->prefix)) {
        return json_failed('prefix字段不能为空');
    }
    if (empty($data->batch_no) or strlen($data->batch_no) < 9 or strlen($data->batch_no) > 45) {
        return json_failed('batch_no字段只能包括数字、字母、下划线，且长度范围9-45');
    }
    if (!preg_match('/^[0-9a-zA-z_]{9,45}$/', $data->batch_no)) {
        return json_failed('batch_no字段只能包括数字、字母、下划线，且长度范围9-45');
    }
    if ($data->len < 10 or $data->len > 10000000) {
        return json_failed('len字段范围10-10000000');
    }
    if (!$data->file_num) {
        $data->file_num = 1;
    }
    $mysql = Storage::mysql();
    $mysql->insert('batches', $data);
}

$appKey = isset($_SERVER['HTTP_APP_KEY']) ? $_SERVER['HTTP_APP_KEY'] : NULL;
$appSecret = isset($_SERVER['HTTP_APP_SECRET']) ? $_SERVER['HTTP_APP_SECRET'] : NULL;
if (auth($appKey, $appSecret)) {
    // 校验appkey和appsecret
    if (isset($_GET['action']) and function_exists($_GET['action'])) {
        call_user_func($_GET['action']);
    } else {
        json_failed('invalid action');
    }
} else {
    header("HTTP/1.1 401 Unauthorized");
}

// var_dump(file_get_contents('php://input'));

// $data = [
//     'name' => 'test_name_001',
//     'prefix' => 'http://xxx.cn/',
//     'batch_no' => time(),
//     'len' => 10,
//     'remark' => 'test remark content'
// ];
// $response = Fetch::post('http://127.0.0.1:8090/batch.php?action=apply', $data);
// var_dump($response);