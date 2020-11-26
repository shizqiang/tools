<?php
function parse($uri) {
    $file = '.' . $uri . '.php';
    $arr = explode('/', $uri);
    if (count($arr) > 2) {
        $_GET['f'] = $arr[2];
        if (file_exists($arr[1] . '.php')) {
            require $arr[1] . '.php';
        } else {
            require 'home.php';
        }
    } elseif (file_exists($file)) {
        require $file;
    } else {
        require 'home.php';
    }
}

if (isset($_SERVER['PATH_INFO'])) {
    parse($_SERVER['PATH_INFO']);
} elseif (isset($_SERVER['REQUEST_URI'])) {
    parse($_SERVER['REQUEST_URI']);
} elseif (PHP_SAPI === 'cli' OR defined('STDIN')) {
    print 'cli';
} else {
    require 'home.php';
}
