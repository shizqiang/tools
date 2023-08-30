<?php
namespace libs;

use Config;

class DB {
    
    private static $instance = [];
    
    private static $redis;

    private $pdo;
    
    private $select = '*';
    
    private $where = [];

    private $group = '';
    
    private $order = '';
    
    private $limit = '';

    private function __construct($dsn, $config) {
        empty($config['charset']) and $config['charset'] = 'utf8mb4';
        if (!isset($config['file'])) { // file说明是sqlite
            // 长连接，第三个参数配置为 [\PDO::ATTR_PERSISTENT => true]
            $this->pdo = new \PDO($dsn, $config['user'], $config['pass']);
            $this->pdo->exec('set names ' . $config['charset']);
        } else {
            // 'mysql:dbname=testdb;host=127.0.0.1;port=3333';
            // 'mysql:dbname=testdb;unix_socket=/path/to/socket'
            $this->pdo = new \PDO($dsn);
            $this->pdo->exec('set names ' . $config['charset']);
        }
    }
    
    public static function MySQL($name = 'mysql') {
        if (isset(static::$instance[$name])) {
            return static::$instance[$name];
        }
        $config = Config::get($name);
        $dsn = 'mysql:host=%s;dbname=%s;port=%s';
        $dsn = sprintf($dsn, $config['host'], $config['db'], $config['port']);
        $storage= new self($dsn, $config);
        static::$instance[$name] = $storage;
        return $storage;
    }
    
    public static function SQLite($name = 'sqlite') {
        if (isset(static::$instance[$name])) {
            return static::$instance[$name];
        }
        $config = Config::get($name);
        $dsn = 'sqlite:' . $config['file'];
        $storage = new self($dsn, $config);
        static::$instance[$name] = $storage;
        return $storage;
    }
    
    public static function Redis() {
        if (isset(static::$redis)) {
            return static::$redis;
        }
        $config = Config::get('redis');
        $redis = new \Redis();
        $redis->connect($config['host'], $config['port']);
        $config['auth'] and $redis->auth($config['auth']);
        static::$redis = $redis;
        return $redis;
    }
    
    public function find($table, $where = [], $option = null) {
        return $this->where($where)->one($table, $option);
    }
    
    public function search($table, $where = [], $option = null) {
        return $this->where($where)->all($table, $option);
    }
    
    public function select($select) {
        $this->select = $select;
        return $this;
    }
    
    public function where($where, $value = null) {
        if (is_array($where)) {
            $this->where = array_merge($this->where, $where);
        } elseif (is_bool($value) or is_object($value)) {
            throw new \Exception('[value is invalid]');
        } else {
            $this->where = array_merge($this->where, [$where => $value]);
        }
        return $this;
    }

    public function groupby($group) {
        $this->group = '`' . $group . '`';
        return $this;
    }
    
    public function orderby($order, $type = 'asc') {
        $this->order = '`' . $order . '` ' . $type;
        return $this;
    }
    
    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function one($table, $option = null) {
        $result = $this->limit(1)->all($table, $option);
        if (is_object($result)) {
            return $result;
        }
        if (!empty($result)) {
            return $result[0];
        }
        return NULL;
    }
    
    public function all($table, $option = null) {
        $sql = "select %s from `%s`%s";
        list($where, $bind) = $this->_where();
        $sql = sprintf($sql, $this->select, $table, $where);
        return $this->query($sql, $bind, $option);
    }
    
    public function usedb($db) {
        $_ = $this->pdo->exec('use '. $db);
        if ($_ === false) {
            throw new \Exception('[database ' . $db . ' not exists]');
        }
    }
    
    public function query($sql, $bind = [], $option = null) {
        if ($this->group) {
            $sql .= ' group by ' . $this->group;
        }
        if ($this->order) {
            $sql .= ' order by ' . $this->order;
        }
        if ($option and $option->first) {
            $this->limit = 1;
        }
        if ($this->limit) {
            $sql .= ' limit ' . $this->limit;
        }
        if ($option and $option->lock) {
            $sql .= ' for update';
        }
        $stmt = $this->pdo->prepare($sql);
        if ($this->pdo->errorCode() != '00000') {
            throw new \Exception($this->pdo->errorInfo()[2], $this->pdo->errorInfo()[1]);
        }
        Config::debug() and println($sql);
        $stmt->execute($bind);
        if ($stmt->errorCode() != '00000') {
            throw new \Exception($stmt->errorInfo()[2], $stmt->errorInfo()[1]);
        }
        $this->_reset();
        $dataList = [];
        while (true) {
            $row = $stmt->fetchObject();
            if ($option and $option->first) {
                return $row;
            }
            if (!$row) {
                break;
            }
            $dataList[] = $row;
        }
        return $dataList;
    }
    
    public function execute($sql, $option = null) {
        if ($option and $option->first) {
            $this->limit = 1;
        }
        if ($option and $option->ignore) {
            $sql = str_replace('insert', 'insert ignore', $sql);
        }
        if ($option and $option->replace) {
            $sql = str_replace('insert', 'replace', $sql);
        }
        if ($this->limit) {
            $sql .= ' limit ' . $this->limit;
        }
        Config::debug() and println($sql);
        $result = $this->pdo->exec($sql);
        if ($this->pdo->errorCode() != '00000') {
            throw new \Exception($this->pdo->errorInfo()[2], $this->pdo->errorInfo()[1]);
        }
        return $result;
    }

    public function update($table, $update) {
        $sql = "update `%s` set %s%s";
        list($where, $bind) = $this->_where();
        list($set, $bindSet) = $this->_set($update);
        $sql = sprintf($sql, $table, $set, $where);
        if ($this->limit) {
            $sql .= ' limit ' . $this->limit;
        }
        Config::debug() and println($sql);
        if (empty($where) and empty($this->limit)) {
            throw new \Exception('[Danger without where and limit]');
        }
        $stmt = $this->pdo->prepare($sql);
        if ($this->pdo->errorCode() != '00000') {
            throw new \Exception($this->pdo->errorInfo()[2], $this->pdo->errorInfo()[1]);
        }
        $stmt->execute(array_merge($bindSet, $bind));
        if ($stmt->errorCode() != '00000') {
            throw new \Exception($stmt->errorInfo()[2], $stmt->errorInfo()[1]);
        }
        $this->_reset();
        return $stmt->rowCount();
    }

    public function insert($table, $data) {
        $sql = "insert into `%s` (%s) values %s";
        list($column, $data, $bind) = $this->_column($data);
        $sql = sprintf($sql, $table, $column, $data);
        Config::debug() and println($sql);
        $stmt = $this->pdo->prepare($sql);
        if ($this->pdo->errorCode() != '00000') {
            throw new \Exception($this->pdo->errorInfo()[2], $this->pdo->errorInfo()[1]);
        }
        $stmt->execute($bind);
        if ($stmt->errorCode() != '00000') {
            throw new \Exception($stmt->errorInfo()[2], $stmt->errorInfo()[1]);
        }
        $this->_reset();
        return $stmt->rowCount();
    }

    public function delete($table) {
        $sql = "delete from `%s`%s";
        list($where, $bind) = $this->_where();
        if (empty($where) and empty($this->limit)) {
            throw new \Exception('[Danger without where and limit]');
        }
        $sql = sprintf($sql, $table, $where);
        if ($this->limit) {
            $sql .= ' limit ' . $this->limit;
        }
        Config::debug() and println($sql);
        $stmt = $this->pdo->prepare($sql);
        if ($this->pdo->errorCode() != '00000') {
            throw new \Exception($this->pdo->errorInfo()[2], $this->pdo->errorInfo()[1]);
        }
        $stmt->execute($bind);
        if ($stmt->errorCode() != '00000') {
            throw new \Exception($stmt->errorInfo()[2], $stmt->errorInfo()[1]);
        }
        $this->_reset();
        return $stmt->rowCount();
    }

    private function _where() {
        $where = '';
        $bind = [];
        foreach ($this->where as $column => $value) {
            if (empty($where)) {
                $where .= ' where ';
            } else {
                $where .= ' and ';
            }
            if (strpos($column, ' is') > 0) {
                if (is_null($value)) {
                    $where .= $column . ' null';
                } else {
                    $where .= $column . ' ' . $value;
                }
            } elseif (is_array($value) and strpos($column, ' in') > 0) {
                $_ = [];
                foreach ($value as $v) {
                    $_[] = '?';
                    $bind[] = $v;
                }
                $where .= $column . ' (' . implode(',', $_) . ')';
            } elseif (is_array($value) and strpos($column, ' between') > 0) {
                $where .= $column . ' ? and ?';
                foreach ($value as $v) {
                    $bind[] = $v;
                }
            } else {
                $where .= $column . ' ?';
                $bind[] = $value;
            }
        }
        return [$where, $bind];
    }

    private function _set($update) {
        if (empty($update) and !is_array($update) and !is_object($update)) {
            $update = json_encode($update);
            throw new \Exception("[No update for set:{$update}]", 1);
        }
        $set = '';
        $bind = [];
        foreach ($update as $column => $value) {
            if (empty($set)) {
                $set .= sprintf('`%s` = ?', $column);
            } else {
                $set .= sprintf(', `%s` = ?', $column);
            }
            $bind[] = $value;
        }
        return [$set, $bind];
    }

    private function _column($data) {
        if (empty($data) and !is_array($data) and !is_object($data)) {
            $data = json_encode($data);
            throw new \Exception("[No data for insert:{$data}]", 1);
        }
        $set = '';
        $value = [];
        $bind = [];
        $batch = false;
        $not_batch = false;
        $_set = false;
        foreach ($data as $column => $v) {
            if (is_array($v) or is_object($v)) {
                if ($not_batch) {
                    continue;
                }
                $batch = true;
                $_value = [];
                foreach ($v as $_column => $_v) {
                    if (!$_set) {
                        if (empty($set)) {
                            $set .= sprintf('`%s`', $_column);
                        } else {
                            $set .= sprintf(', `%s`', $_column);
                        }
                    }
                    $_value[] = '?';
                    $bind[] = $_v;
                }
                $value[] = '(' . implode(', ', $_value) . ')';
                $_set = true; // column is set, 上面的循环执行一次就设置好了字段
            } else {
                if ($batch) {
                    continue;
                }
                $not_batch = true;
                if (empty($set)) {
                    $set .= sprintf('`%s`', $column);
                } else {
                    $set .= sprintf(', `%s`', $column);
                }
                $value[] = '?';
                $bind[] = $v;
            }
        }
        if ($not_batch) {
            $value = '(' . implode(', ', $value) . ')';
        } else {
            $value = implode(', ', $value);
        }
        return [$set, $value, $bind];
    }

    private function _reset() {
        $this->select = '*';
        $this->where = [];
        $this->limit = '';
        $this->order = '';
    }
}
