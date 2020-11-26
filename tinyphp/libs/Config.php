<?php
namespace libs;

class Config {
    
    private static $config = [];
    
    public static function get($key, $default = null) {
    	if (empty(static::$config)) {
    		if (!file_exists('../.env')) {
    			return null;
    		}
    		static::$config = parse_ini_file('../.env', true);
    	}
    	$arr = explode('.', $key);
    	if (count($arr) > 1) {
    	    if (!isset(static::$config[$arr[0]])) {
                return $default;
            }
            if (!isset(static::$config[$arr[0]][$arr[1]])) {
                return $default;
            }
            return static::$config[$arr[0]][$arr[1]];
    	} else {
    	    if (!isset(static::$config[$arr[0]])) {
    	        return $default;
    	    }
    	    return static::$config[$arr[0]];
    	}
        
        return $default;
    }
    
    public static function debug() {
        return static::get('app.debug', false);
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