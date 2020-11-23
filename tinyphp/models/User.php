<?php
namespace models;

class User extends Model {
    
    protected static $table = 'users';
    
    public $id;
    public $username;
    public $email;
    public $password;
    public $language;
    public $google_key;
    public $credit;
    public $fund;
    public $buy_num;
    public $sell_num;
    public $accuse_count;
    public $argu_count;
    public $win_count;
    public $fail_count;
    public $register_time;
    public $last_login_time;
    public $last_business_time;
    
}