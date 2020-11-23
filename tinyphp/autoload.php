<?php
require 'libs/Config.php';
require 'libs/Log.php';
require 'libs/Lang.php';

spl_autoload_register(function($class) {
    $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    } else {
        throw new Exception($file . ' not found');
    }
});

function redirect($url) {
    header('location:'. $url);
    exit;
}

function is_cli() {
    return (PHP_SAPI === 'cli' OR defined('STDIN'));
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
    header('content-type: application/json');
} elseif (!is_cli()) {
    header('content-type: text/html; charset=utf-8');
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
        print json(['data' => $_REQUEST['errors'], 'message' => $e->getMessage(), 'code' => $e->getCode()]);
    } elseif (!is_cli()) {
        header('content-type: text/html; charset=utf-8', true, $e->getCode());
        print '<script>alert("'. $e->getMessage() .'");</script>';
    } else {
        print $e->getMessage();
    }
}
