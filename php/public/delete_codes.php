<?php
require __DIR__ . '/../autoload.php';

use libs\DB;

ini_set('display_errors', 0);

// 获取MySQL实例
$mysql = DB::mysql();

$redis = DB::redis();

$backupRedis = new Redis();
$backupRedis->connect('127.0.0.1', 6381);

// 查询所有结果
$codes = [
		'5TWt46sMjllz',
		'51mF35Hct9Ie',
		'5pIhIpPMKxiT',
		'5Jp4I1b8IA1W',
		'5prS12RbGLD2',
		'5ZXzMykcrV7o',
		'5l2Yuigo0Qan',
		'5y4WOOXtqf3J',
		'5YbzXfnwOHf5',
		'5lDyGsPYJmhc',
		'5uYe6x4J2KkX',
		'57XaMrODgFm0',
		'5GkxaojHw4Xo',
		'5luwgBcJzWrV',
		'5dGNONaAjybr',
		'5PPeEVnTdhcE',
		'5lR9azzld45P',
		'50iZYGTzHAEU',
		'5gVplOQXG1Ta',
		'5cuBOau8tLwQ',
		'5TEub7Ex6hBt',
		'5QvFUt8kTMFC',
		'5qOgShimW6jb',
		'5bjnCmkTkYf2',
		'5XOSZ2mcTAEa',
		'5AkK11qe4a2Z',
		'5PEUPYSG6Mv1',
		'5CRzdsi2nxk0',
		'5Sjf0v7udSg8',
		'5THlB9tr480O',
		'5yuMNLbwsdwk',
		'5IEImsJPWqBe',
		'5MKwL1ddOtbc',
		'5FQJAvsvuDD9',
		'5HjtZcVW43QE',
		'5Jv59uN9g0kH',
		'5YljbBZIWhLj',
		'5luTgYN3i4f6'
];
$dataList = $mysql->select('id,code,time')->where(['code in' => $codes, 'mchId =' => 0])->orderby('id', 'desc')->limit(10)->all('hr_code');
var_dump($dataList);

foreach ($dataList as $row) {
    delete($row->code);
    print PHP_EOL;
}
// echo $redis->get('name');

/**
 * 删除扫码记录包括缓存等数据，将码置为完全未扫状态
 * @param string $code
 */
function delete($code) {
    println('删除 ->' . $code);
    global $mysql;
    global $redis;
    global $backupRedis;
    $mysql->where(['mchId =' => 0, 'code =' => $code])->limit(1)->delete('scan_log');
    $mysql->where(['mchId =' => 0, 'code =' => $code])->limit(1)->delete('hr_code');
    $mysql->where(['mchId =' => 0, 'code =' => $code])->limit(1)->delete('hr_user_redpackets');
    $redis->del('SCAN_LOG:'. $code);
    $ord = ord(substr($code, 0, 1));
    $backupRedis->srem('SCAN_LOG:'. $ord, $code);
    println('删除 ->' . $code . '完成');
}