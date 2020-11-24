<?php
namespace libs;

abstract class Queue {
    
    protected static $Q = 'QUEUE:';
    
    public function dispach(string $queue = 'default') {
        $redis = DB::redis();
        $redis->lPush(static::$Q . $queue, serialize($this));
    }
    
    public static function handle($queue = 'default') {
        $redis = DB::redis();
        $str = $redis->rPop(static::$Q . $queue);
        if ($str) {
            $task = unserialize($str);
            $task->run();
            return true;
        }
        return false;
    }
    
    abstract function run();

}