<?php
require __DIR__ . '/../autoload.php';

use utils\db\Storage;

// 获取MySQL实例 
$mysql = Storage::mysql();

// 查询所有结果 
$dataList = $mysql->all('products');

// 查询第一条结果，返回对象 
$row = $mysql->one('sys_users');
var_dump($row);

try {
	print(json_encode($dataList));
} catch (Exception $e) {
	println($e->getMessage(), 'red');
}



// $redis = Storage::redis();
// print $redis->get('name');

// print Fetch::get('https://svn.lsa0.cn/upload.php') ;
