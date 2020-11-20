<?php
require __DIR__ . '/../autoload.php';

use utils\db\DBHelper;

ini_set('display_errors', 0);

// 获取MySQL实例
$mysql = DBHelper::mysql();

$redis = DBHelper::redis();

$backupRedis = new Redis();
$backupRedis->connect('127.0.0.1', 6381);

// 查询所有结果
$codes = [
    '5bIdODnNtA6B',
    '5EcnnU5um4Ni',
    '5QvFUt8kTMFC',
    '5qOgShimW6jb',
    '5PEUPYSG6Mv1',
    '5TEub7Ex6hBt',
    '5bjnCmkTkYf2'];
$dataList = $mysql->select('id,code,time')->where(['code in' => $codes, 'mchId =' => 0])->orderby('id', 'desc')->limit(10)->all('hr_code');
foreach ($dataList as $row) {
    print $row->code; 
    delete($row->code);
    print PHP_EOL;
}
echo $redis->get('name');

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