<?php
namespace libs;

class Fetch {
    
    private static $method = 'GET';
    
    public static function get($url, $data = NULL) {
        $url = http_build_query($data);
        return static::httpRequest($url);
    }
    
    public static function post($url, $data) {
        static::$method = 'POST';
        return static::httpRequest($url, $data);
    }
    
    public static function put($url, $data) {
        static::$method = 'PUT';
        return static::httpRequest($url, $data);
    }
    
    public static function delete($url) {
        static::$method = 'DELETE';
        return static::httpRequest($url);
    }
    
    private static function httpRequest($url, $data = NULL) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array('X-Requested-With', 'xmlhttprequest'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, static::$method);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            if (is_string($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if ($output) {
            curl_close($ch);
            return $output;
        } else {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception($error);
        }
    }
    
    private static function httpRequestSSL($url, $data, $cert) {
        $vars = self::array2xml($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSLCERT, $cert['ssl_cert']);
        curl_setopt($ch, CURLOPT_SSLKEY, $cert['ssl_key']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        $output = curl_exec($ch);
        if ($output) {
            curl_close($ch);
            return $output;
        } else {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception($error);
        }
    }
    
    private static function array2xml($arr) {
        $xml = '<xml>';
        $fmt = '<%s><![CDATA[%s]]></%s>';
        foreach ($arr as $key => $val) {
            $xml .= sprintf($fmt, $key, $val, $key);
        }
        $xml .= '</xml>';
        return $xml;
    }
}