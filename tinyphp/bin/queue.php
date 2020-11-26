<?php
use libs\Queue;
use libs\Log;

require '../autoload.php';

if (!is_cli()) {
    $argv = ['', 'default'];
}

$task = 'default';
if (count($argv) > 1) {
    $task = $argv[1];
}

while (true) {
    $_ = Queue::handle($task);
    if (!$_) {
        sleep(1);
    }
    if (file_exists('/tmp/stop')) {
        Log::info('Task -> ' . $task . ' -> stoped');
        break;
    }
}