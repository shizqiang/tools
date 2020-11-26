<?php
namespace models;

use libs\DB;

abstract class Model {
    
    protected static $pageSize = 10;
    
    protected static $table = '';
    
    protected static $softDelete = true;
    
    protected $_errors = [];
    
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
        if (static::$softDelete) {
            return DB::MySQL()->where('deleted_at is', null)->select($select)->find(static::$table, $where);
        }
        return DB::MySQL()->select($select)->find(static::$table, $where);
    }
    
    static function search(array $where = [], string $select = '*') {
        if (static::$softDelete) {
            return DB::MySQL()->where('deleted_at is', null)->select($select)->search(static::$table, $where);
        }
        return DB::MySQL()->select($select)->search(static::$table, $where);
    }
    
    static function pages(array $where = [], string $select = '*') {
        if (isset($_GET['pageSize']) and is_numeric($_GET['pageSize']) and $_GET['pageSize'] > 0) {
            static::$pageSize = $_GET['pageSize'];
        }
        $skip = 0;
        $page = 1;
        if (isset($_GET['page']) and is_numeric($_GET['page']) and $_GET['page'] > 0) {
            $page = $_GET['page'];
        }
        $skip = static::$pageSize * ($page - 1);
        $limit = $skip . ',' . static::$pageSize;
        if (static::$softDelete) {
            $row = DB::MySQL()->where('deleted_at is', null)->select('count(1) total')->one(static::$table);
            $rows = DB::MySQL()->where('deleted_at is', null)->limit($limit)->select($select)->search(static::$table, $where);
            return [$row->total, $rows, ceil($row->total / static::$pageSize), $page];
        } else {
            $row = DB::MySQL()->select('count(1) total')->one(static::$table);
            $rows = DB::MySQL()->limit($limit)->select($select)->search(static::$table, $where);
            return [$row->total, $rows, ceil($row->total / static::$pageSize), $page];
        }
    }
    
    function store() {
    	if ($this->_errors) {
            $_REQUEST['errors'] = $this->_errors;
            throw new \Exception('invalid model attr', 406);
        }
        DB::MySQL()->insert(static::$table, $this);
    }

    function update() {
    	if ($this->_errors) {
    		$_REQUEST['errors'] = $this->_errors;
    		throw new \Exception('invalid model attr', 406);
    	}
        return DB::MySQL()->where('id =', $this->id)->update(static::$table, $this);
    }
    
    function delete() {
        if (static::$softDelete) {
            DB::MySQL()->where('id =', $this->id)->update(static::$table, ['deleted_at', date('Y-m-d H:i:s')]);
        } else {
            DB::MySQL()->where('id =', $this->id)->delete(static::$table);
        }
    }
    
    /**
     * :抽象方法，由具体的子类实现验证
     */
    function validate() {
    	if ($this->_errors) {
    		$_REQUEST['errors'] = $this->_errors;
    		throw new \Exception('invalid model attr', 406);
    	}
    }
    
    protected function str($val, $min, $max) {
        return strlen($val) >= $min and strlen($val) <= $max;
    }
    
    protected function int($val, $min, $max) {
        
    }
    
    protected function num($val, $min, $max) {
        
    }
    
    protected function reg($val, $reg) {
        
    }
    
}