<?php
namespace models;

use libs\DB;

class Model {
    
    protected static $table = '';
    
    function __construct($data = false) {
        if (is_null($data)) {
            throw new \Exception('can\'t create model from null');
        } elseif ($data) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * rewrite set method, can not set propety which is undefined
     * @param string $key
     * @param mixed $value
     */
    function __set($key, $value) {}
    
    static function find(array $where, string $select = '*') {
    	return DB::MySQL()->where('deleted_at is', null)->select($select)->find(static::$table, $where);
    }
    
    static function search(array $where = [], string $select = '*') {
        return DB::MySQL()->where('deleted_at is', null)->select($select)->search(static::$table, $where);
    }
    
    function store() {
        if (!empty($this->validate())) {
            $_REQUEST['errors'] = $this->validate();
            throw new \Exception('param invalid', 406);
        }
        DB::MySQL()->insert(static::$table, $this);
    }

    function update() {
        if (!empty($this->validate())) {
            $_REQUEST['errors'] = $this->validate();
            throw new \Exception('param invalid', 406);
        }
        return DB::MySQL()->where('id =', $this->id)->update(static::$table, $this);
    }
    
    function delete($software = true) {
        if (!$software) {
            DB::MySQL()->where('id =', $this->id)->delete(static::$table);
        } else {
            DB::MySQL()->where('id =', $this->id)->update(static::$table, ['deleted_at', date('Y-m-d H:i:s')]);
        }
    }
    
    protected function validate() {
        return [];
    }
    
    protected function str_range($val, $min, $max) {
        return strlen($val) >= $min and strlen($val) <= $max;
    }
    
    protected function int_range($val, $min, $max) {
        
    }
    
    protected function num_range($val, $min, $max) {
        
    }
    
    protected function reg($val, $reg) {
        
    }
    
}