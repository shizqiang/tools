<?php
namespace libs;

class Lang {
    
    private static $config = [];
    
    private static $defaultLang = 'zh-CN';
    
    private static function parse($file) {
        static::$config = parse_ini_file($file);
    }
    
    public static function get($name) {
        if (empty(static::$config)) {
            if (!isset($_SESSION['language']) or !file_exists(__DIR__ . '/../i18n/'. $_SESSION['language'] .'.ini')) {
                static::parse(__DIR__ . '/../i18n/'. static::$defaultLang .'.ini');
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
