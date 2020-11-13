<?php
require __DIR__ . '/../autoload.php';

use utils\db\DB;
use utils\queue\Queue;

ini_set('display_errors', 0);

// 获取MySQL实例 
$mysql = DB::mysql();

// 查询所有结果 
$dataList = $mysql->query('show tables');
foreach ($dataList as $table) {
	foreach ($table as $tableStr) {
		print $tableStr;
		print PHP_EOL;
	}
}

// 查询第一条结果，返回对象 
// $row = $mysql->one('sys_users');
// var_dump($dataList);


$redis = DB::redis();
print $redis->get('name');

// print Fetch::get('https://svn.lsa0.cn/upload.php') ;

class Abc extends Queue {
    
    function run() {
        var_dump($this->data);
    }
}

Abc::dispach('shizq');

while (true) {
    $r = Abc::handle();
    if (!$r) {
        break;
    }
}