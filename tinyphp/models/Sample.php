<?php

namespace models;

class Sample extends Model {
    
    protected static $table = 'your_table_name';
    
    public $id;
    public $name;
    public $code;
    
    function validate() {
        if (!$this->str($this->name, 2, 45)) {
            $this->errors['name'] = '名称必须包含2-45个字符';
        }
    }
    
    function store() {
        $row = static::find(['code =' => $this->code]);
        if ($row) {
            throw new \Exception('产品编码<'.$this->code.'>已存在', 403);
        }
        parent::store();
    }
    
}