<?php
if (isset($_SERVER['PATH_INFO'])) {
    $file = '.' . $_SERVER['PATH_INFO'] . '.php';
    $arr = explode('/', $_SERVER['PATH_INFO']);
    if (count($arr) > 2) {
        $_GET['f'] = $arr[2];
        if (file_exists($arr[1] . '.php')) {
            require $arr[1] . '.php';
        }
    } elseif (file_exists($file)) {
        require $file;
    } else {
        require 'home.php';
    }
} elseif (PHP_SAPI === 'cli' OR defined('STDIN')) {
    print 'cli';
} else {
    require 'home.php';
}