<?php

namespace models;

class UserCredit extends Model {
    
    protected static $table = 'user_credit_logs';
    
    protected static $softDelete = false;
    
    public $id;
    public $user_id;
    public $credit;
    public $old_credit;
    public $new_credit;
    public $time;
    public $remark;
}