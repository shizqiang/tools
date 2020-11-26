<?php
use libs\Lang;
use models\User;

spl_autoload_register(function($class) {
    $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    } else {
        throw new Exception($file . ' not found');
    }
});

function _SESSION_($key, $default = null) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

function _GET_($key, $default = null) {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

function _POST_($key, $default = null) {
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

function is_cli() {
    return (PHP_SAPI === 'cli' OR defined('STDIN'));
} 

function getCurrentUser(): User {
    $user = _SESSION_('current_user');
    if (!$user) {
        if (ajax()) {
            header('content-type: application/json; charset=utf-8', true, 401);
            print jsonFailed('No Auth');
            exit();
        } elseif (is_cli()) {
            println('No current user');
            exit();
        } else {
            redirect('/login');
        }
    }
    return $user;
}

function redirect($url) {
    header('location:'. $url, true, 302);
    exit();
}

function ajax() {
    if (!is_cli()) {
        return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
        strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest";
    }
    return false;
}

function json($data) {
    return json_encode($data, JSON_UNESCAPED_UNICODE);
}

function jsonSuccess($data = null) {
    return json(['data' => $data, 'message' => 'SUCCESS', 'code' => 0]);
}

function jsonFailed($message, $data = null, $code = 1) {
    return json(['data' => $data, 'message' => $message, 'code' => $code]);
}

function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

function i18n($str) {
    return Lang::get($str);
}

function println($msg = '', $color = '', $loading = false) {
    $colors = [
        ''          => '',
        'red'       => '31',
        'green'     => '32',
        'yellow'    => '33',
        'blue'      => '34',
        'purple'    => '35',
    ];
    if (!array_key_exists($color, $colors)) {
        $color = '';
    }
    $color = $colors[$color];
    if (is_object($msg) or is_array($msg)) {
        $msg = json_encode($msg);
    }
    $msg = '['.date('Y-m-d H:i:s') . '] -> ' . $msg;
    if (is_cli()) {
        if (empty($color)) {
            print $msg;
        } else {
            print "\033[{$color}m{$msg}\033[0m";
        }
        print PHP_EOL;
    }
    if ($loading) {
        print "\033[?25l";
        print "\033[1A";
    }
}


if (ajax()) {
    header('content-type: application/json; charset=utf-8');
} elseif (!is_cli()) {
    header('content-type: text/html; charset=utf-8');
}

// set language 
if (!isset($_SESSION['language']) and isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $_SESSION['language'] = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])[0];
}

try {
    if (is_cli()) {
        function_exists('cli') and cli();
    } elseif (isset($_GET['f']) and function_exists($_GET['f'])) {
        call_user_func($_GET['f']);
    } elseif (function_exists('get') and $_SERVER['REQUEST_METHOD'] === 'GET') {
        get();
    } elseif (function_exists('post') and $_SERVER['REQUEST_METHOD'] === 'POST') {
        post();
    } elseif (function_exists('put') and $_SERVER['REQUEST_METHOD'] === 'PUT') {
        put();
    } elseif (function_exists('delete') and $_SERVER['REQUEST_METHOD'] === 'DELETE') {
        delete();
    }
} catch (\Exception $e) {
    if (ajax()) {
        !isset($_REQUEST['errors']) and $_REQUEST['errors'] = null;
        print jsonFailed($e->getMessage(), $_REQUEST['errors'], $e->getCode());
    } elseif (is_cli()) {
        println('Error: ' . (isset($_REQUEST['errors']) ? json_encode($_REQUEST['errors']) : $e->getMessage()));
    } else {
        header('content-type: text/html; charset=utf-8', true, $e->getCode());
        print '<script>alert("'. $e->getMessage() .'");</script>';
    }
}
