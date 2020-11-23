<?php
use models\Product;

require '../autoload.php';

function get() {
    if (ajax()) {
        print json([]);
    } else {
        $_REQUEST['name'] = Lang::get('language');
        include '../views/batches.php';
    }
}

function post() {
    $rows = Product::search();
    var_dump($rows);
}

function cli() {
    $rows = Product::find([], 'id,name');
    var_dump($rows);
}

