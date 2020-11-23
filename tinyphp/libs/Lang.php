<?php
class Lang {
    
    static $config = [];
    
    private static function parse($file) {
        static::$config = parse_ini_file($file);
    }
    
    public static function get($name) {
        if (empty(static::$config)) {
            if (!isset($_SESSION['language']) or !file_exists(__DIR__ . '/../i18n/'. $_SESSION['language'] .'.ini')) {
                static::parse(__DIR__ . '/../i18n/zh_CN.ini');
            } elseif (file_exists(__DIR__ . '/../i18n/'. $_SESSION['language'] .'.ini')) {
                static::parse(__DIR__ . '/../i18n/'. $_SESSION['language'] .'.ini');
            }
        }
        if (isset(static::$config[$name])) {
            return static::$config[$name];
        }
        return '';
    }
    
}
