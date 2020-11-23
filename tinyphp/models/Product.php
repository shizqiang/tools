<?php

namespace models;

class Product extends Model {
    
    protected static $table = 'products';
    
    public $id;
    public $name;
    public $code;
    public $ups_code;
    public $introduce;
    
    protected function validate() {
        $errors = [];
        if (!$this->str_range($this->name, 2, 45)) {
            $errors['name'] = '产品名称必须包含2-45个字符';
        }
        return $errors;
    }
    
    function store() {
        $row = static::find(['code =' => $this->code]);
        if ($row) {
            throw new \Exception('产品编码<'.$this->code.'>已存在', 403);
        }
        parent::store();
    }
    
}