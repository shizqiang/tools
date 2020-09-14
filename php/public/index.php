<?php
require __DIR__ . '/../autoload.php';

use utils\db\Storage;

// 获取MySQL实例 
$mysql = Storage::mysql();

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

// try {
// 	print(json_encode($dataList));
// } catch (Exception $e) {
// 	println($e->getMessage(), 'red');
// }



$redis = Storage::redis();
print $redis->get('name');

// print Fetch::get('https://svn.lsa0.cn/upload.php') ;




/**
 * 数据库第一范式
 * 字段不能再拆分（列原子性），比如字段“地址”应该拆分为“省市区”
 * 
 * 数据库第二范式
 * 每一行的数据只和该列（主键）相关，比如订单表，（用户编号，商品编号，商品名称，商品价格），很显然
 * 商品名称和价格不应该出现在这个表中。
 * 
 * 数据库第三范式
 * 数据不能存在传递关系，即没个属性都跟主键有直接关系而不是间接关系。
 * 像：a-->b-->c  属性之间含有这样的关系，是不符合第三范式的。
	比如Student表（学号，姓名，年龄，性别，所在院校，院校地址，院校电话）
	这样一个表结构，就存在上述关系。 学号--> 所在院校 --> (院校地址，院校电话)
	这样的表结构，我们应该拆开来，如下。
	（学号，姓名，年龄，性别，所在院校）--（所在院校，院校地址，院校电话）
 * 
 * 
 * 
 * 
 * 
 * 
 */