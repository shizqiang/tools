<?php
class Config {
    
    static $config = [];
    
    public static function parse($env) {
        if (!file_exists($env)) {
            throw new \Exception('[env not set]');
        }
        $config = parse_ini_file($env, true);
        static::$config = $config;
    }
    
    public static function get($type) {
        if (!isset(static::$config[$type])) {
            return null;
        }
        return static::$config[$type];
    }
    
    static function debug() {
        return static::$config['app']['debug'];
    }
}

class DBOption {
    public $lock = false;
    public $first = false;
    public $ignore = false;
    public $replace = false;
}


if (!class_exists('Redis')) {
    class Redis {
        function connect() {}
        function auth() {}
        function get() {}
        function set() {}
        function expire() {}
        function incr() {}
    }
}