<?php
require __DIR__ . '/../autoload.php';

use libs\DB;

ini_set('display_errors', 0);

// 获取MySQL实例
$db = DB::MySQL();
$i = 10;
while ($i > 0) {
    $i--;
    $skip = 100 * ($i - 10);
    $rows = $db->limit(100)->select("id,from_unixtime(puttime)")->search('tts_orders', ['id >' => 49999]);
    println(count($rows));
}

