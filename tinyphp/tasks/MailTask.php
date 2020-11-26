<?php
namespace tasks;

use libs\Queue;
use libs\Fetch;
use libs\Config;
use libs\Log;

class MailTask extends Queue {
    
    public $email;
    public $subject = '4dealers';
    public $content;
    
    function __construct($email, $content) {
        $this->email = $email;
        $this->content = $content;
    }
    
    public function run() {
        if (!Config::get('mail.address')) {
            println('邮件地址未配置', 'yellow');
            $this->dispach();
            sleep(1);
            return;
        }
        try {
            $data = [];
            $data['subject'] = $this->subject;
            $data['content'] = $this->content;
            $data['address'] = $this->email;
            $response = Fetch::post(Config::get('mail.address') . '?type=163', $data);
            $response = json_decode($response);
            if ($response->message === 200) {
                println('发送邮件 -> '. $this->email, 'blue');
            } else {
                println('发送邮件 -> '. $this->email, 'red');
                Log::error(get_class($this) . " -> " . $response->message);
            }
        } catch (\Exception $e) {
            println('发送邮件 -> '. $this->email, 'red');
            Log::error(get_class($this) . " -> ". $e->getMessage());
        }
        
    }

}