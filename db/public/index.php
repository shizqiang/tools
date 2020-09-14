<?php
require '../autoload.php';

use utils\db\Storage;
use utils\db\Model;
use utils\io\Fetch;

$mysql = Storage::mysql();
// $dataList = $mysql->all('sys_merchants');
// var_dump($dataList);
try {
	// $dataList = $mysql->select('id,name')->limit(20)->all('users');
	// var_dump($dataList);
	$m = new Model();
	$m->name = 'qwe';
	// $ms = [];
	// $i = 1;
	// while ($i <= 2000) {
	// 	$i++;
	// 	$m->name = time() . uniqid();
	// 	$ms[] = $m;
	// }
	$dataList = $mysql->search('products');
	// foreach ($dataList as $key => $value) {
	// 	println(iconv('utf-8', 'gbk', $value->name));
	// }
	print(json_encode($dataList));
} catch (Exception $e) {
	println($e->getMessage(), 'red');
}



// $redis = Storage::redis();
// print $redis->get('name');

print Fetch::get('https://svn.lsa0.cn/upload.php') ;
