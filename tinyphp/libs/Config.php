<?php
class Config {
    
    static $config = [];
    
    public static function get($type) {
    	if (empty(static::$config)) {
    		static::$config = parse_ini_file('../.env', true);
    	}
        if (!isset(static::$config[$type])) {
            return null;
        }
        return static::$config[$type];
    }
    
    public static function debug() {
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