<?php
class Log {
    
    private static $PATH = 'd:/var/log/web';
    
    static function debug($str, $data = null) {
        if (Config::debug()) {
            $str = 'd -> ' . $str;
            self::write($str, $data);
        }
    }
    
    static function info($str, $data = null) {
        $str = 'i -> ' . $str;
        self::write($str, $data);
    }
    
    static function warning($str, $data = null) {
        $str = 'w -> ' . $str;
        self::write($str, $data);
    }
    
    static function error($str, $data = null) {
        $str = 'e -> ' . $str;
        self::write($str, $data);
    }
    
    private static function write($str, $data) {
        if ($data) {
            $str .= ' ' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        file_put_contents(self::$PATH . '/php-' . date('Y-m-d') . '.log', date('Y-m-d H:i:s') . ' -> ' . $str . PHP_EOL, FILE_APPEND);
    }
}