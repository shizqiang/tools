<?php
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
    print 'index';
}

