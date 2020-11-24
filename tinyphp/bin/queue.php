<?php
use libs\Queue;

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
        println('sleeping...', 'blue', true);
    }
    if (file_exists('/tmp/stop')) {
        println('stoped', 'red');
        break;
    }
}