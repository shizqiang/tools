<?php
spl_autoload_register(function($class) {
    $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    } else {
        throw new Exception($file . ' not found');
    }
});

if (!class_exists('stdClass')) {
    class Redis {
        function connect() {}
        function auth() {}
        function get() {}
        function set() {}
        function expire() {}
        function incr() {}
    }
}

class Config {
    static $config = [];

    public static function parse() {
        $env = __DIR__ . '/.env';
        if (!file_exists($env)) {
            throw new \Exception('[env not set]');
        }
        $config = parse_ini_file($env, true);
        static::$config = $config;
    }

    public static function get($type) {
        return static::$config[$type];
    }

    static function debug() {
        return static::$config['app']['debug'];
    }
}

Config::parse();

class DBOption {
    public $lock = false;
    public $first = false;
    public $ignore = false;
    public $replace = false;
}

function redirect($url) {
    header('location:'. $url);
    exit;
}

function is_cli() {
    return (PHP_SAPI === 'cli' OR defined('STDIN'));
}

function println($msg = '', $color = '', $loading = false) {
    $colors = [
        ''          => '',
        'red'       => '31',
        'green'     => '32',
        'yellow'    => '33',
        'blue'      => '34',
        'purple'    => '35',
    ];
    if (!array_key_exists($color, $colors)) {
        $color = '';
    }
    $color = $colors[$color];
    if (is_object($msg) or is_array($msg)) {
        $msg = json_encode($msg);
    }
    $msg = '['.date('Y-m-d H:i:s') . '] -> ' . $msg;
    if (is_cli()) {
        if (empty($color)) {
            print $msg;
        } else {
            print "\033[{$color}m{$msg}\033[0m";
        }
        print PHP_EOL;
    }
    if ($loading) {
        print "\033[?25l";
        print "\033[1A";
    }
}