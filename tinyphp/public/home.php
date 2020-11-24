<?php

use tasks\MailTask;

require '../autoload.php';

$task = new MailTask('123');
$task->dispach();