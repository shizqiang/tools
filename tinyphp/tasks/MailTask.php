<?php
namespace tasks;

use libs\Queue;

class MailTask extends Queue {
    
    function __construct($name) {
        $this->name = $name;
    }
    
    public function run() {
        println('发送邮件'. $this->name);
    }

}