<?php
namespace libs;

abstract class Queue {
    
    const Q = 'QUEUE:';
    
    public $data;
    
    static function dispach($message, $queue = 'default') {
        $task = new static();
        $task->data = $message;
        $redis = DB::redis();
        $redis->lPush(static::Q . $queue, serialize($task));
    }
    
    static function handle($queue = 'default') {
        $redis = DB::redis();
        $str = $redis->rPop(static::Q . $queue);
        if (!$str) {
            return false;
        }
        $task = unserialize($str);
        return $task->run();
    }
    
    abstract function run();

}