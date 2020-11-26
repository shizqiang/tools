<?php

use models\User;
use libs\DB;

require '../autoload.php';

function get() {
    $token = _GET_('token');
    $redis = DB::Redis();
    $data = $redis->get($token);
    if (!$data) {
        throw new Exception('链接已过期');
    }
    $data = unserialize($data);
    $_REQUEST['qrcode'] = $data['qrcode'];
    include '../views/active.php';
}

function post() {
   $user = new User($_POST);
   $user->username = '123456';
   $user->email = 'shizhiqiang@acctrue.com';
   $user->password = 'xxx4444';
   $user->language = 'en_US';
   $user->store();
}

function active() {
    $token = _GET_('token');
    $redis = DB::Redis();
    $data = $redis->get($token);
    if (!$data) {
        throw new Exception('链接已过期');
    }
    $data = unserialize($data);
    $row = User::find(['email =' => $data['email']]);
    if (!$row) {
        throw new Exception('用户不存在');
    }
    $user = new User($row);
    $user->active(_POST_('code'));
    print jsonSuccess();
}


function cli() {
    
}