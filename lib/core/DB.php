<?php
/*
 * 查询构建器
 *
 * 该工具主要的目的是为了给上层提供更加实用友好的sql操作方式。
 *
 * 该类沿用目前比较流行的连续子查询方式
 *
 *
 *
 * 事务，读写分离的设置（连接组，更新主，选择从，多主，事务主）
 *
 *
 */
/*
 * 关系数据库的访问管理类
 *
 * php总是需要访问关系型数据库的。
 * 当前环境，php + (mysql/mariaDB) 已经成为经典，其他的关系型数据库，如PostgreSQL等，也在蓬勃发展中。
 * 虽然，php提供了pdo（PHP Data Objects）兼容不同的关系型数据库，但php源码在连接、获取数据、操作数据等方面还是比较繁杂的。
 * 而且，关于数据连接信息、连接管理、数据缓存等，也需要我们封装起来，以便于我们在此类问题上有比较大的操作空间。
 * 这便是Database类产生的原因。
 *
 * 该类具有以下一些特点。
 * 1. 设置get、connect两个接口。这样，一次connect后便可以在任意地方，通过get方式获取到该连接。
 * 2. 用名字去标识一条连接，方便识别连接。
 *
 *该类中采用的命名是基于以下考虑：
 * 1. Database 此命名比Connection更能体现关系数据库的特性，在如今多种类型数据库横行的年代，我更愿意去选择具体的命名，
 *    而且由于它在数据库领域占领时间长，比较经典，就暂时占用了database的名字了
 * 2. 连接提供execute、fetchAll、fetchColumn等形式，是为了和源码的接口尽可能的相似
 */

class DB
{
    private static $config = array();
    private static $connection = array();
    private static $defaultName = 'default';

    public static function config($config)
    {
        self::register($config);
    }

    public static function connect($config, $name = null)
    {
        $name = empty($name) ? self::$defaultName : $name;
        self::register($config, $name);
        return self::connection($name);
    }

    public static function connection($name = null)
    {
        $name = empty($name) ? self::$defaultName : $name;
        assertOrException(isset(self::$config[$name]), 'not has connection config:' . $name);
        if (!isset(self::$connection[$name])) {
            self::$connection[$name] = new PdoDatabaseInstance(self::$config[$name]);
        }
        return self::$connection[$name];
    }

    public static function table($table, $name = null)
    {
        $name = empty($name) ? self::$defaultName : $name;
        assertOrException(isset(self::$config[$name]), 'not has connection config or default config:' . $name);
        return new DB_Query($table, $name);
    }

    // 绑定事务区域
    public static function transaction(callable $method, $name = null)
    {
        try {
            self::connection($name)->beginTransaction();
            $method();
            self::connection($name)->commit();
        } catch (Exception $exception) {
            self::connection($name)->callback();
        }
    }

    public static function register($config, $name = 'default')
    {
        // 多数据库配置特殊处理
        if (isset(current($config)['database'])) {
            foreach ($config as $itemName=>$itemConfig) {
                self::register($itemConfig, $itemName);
            }
            return true;
        }

        // 单数据库配置
        assertOrException(isset($config['host']) && isset($config['database']), 'no host or database set');
        self::$config[$name] = self::formatConfig($config);
        if (isset($config['isDefault']) && $config['isDefault'] == true) {
            self::$defaultName = $name;
        }
        return true;
    }

    private static function formatConfig($config)
    {
        if (!isset($config['driver'])) {
            $config['driver'] = 'mysql';
        }
        if (!isset($config['port'])) {
            $config['port'] = 3306;
        }
        if (!isset($config['charset'])) {
            $config['charset'] = 'utf8';
        }
        if (!isset($config['username'])) {
            $config['username'] = 'root';
        }
        if (!isset($config['password'])) {
            $config['password'] = '';
        }
        $read = array();
        if (isset($config['read'])) {
            $read = $config['read'];
            unset($config['read']);
        }
        $servers = array('read'=>array(), 'write'=>array($config));
        $servers['read'] = array();
        if (!empty($read)) {
            foreach ($read as $host) {
                $servers['read'][] = array_merge($config, array('host'=>$host));
            }
        }
        return $servers;
    }
}

class DB_Query
{
    public $connection = 'default';
    public $columns = '*';
    public $mainTable = null;
    public $join = '';
    public $where = array();
    public $groupBy = '';
    public $having ='';
    public $orderBy = '';
    public $limit = '0, 100'; // 默认限制，最大一百条

    public function __construct($table, $connection)
    {
        $this->mainTable = $table;
        $this->connection = $connection;
    }

    /*
     * 连续查询的相关方法
     */
    public function select($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    public function where($where)
    {
        $this->where = $where;
        return $this;
    }

    public function orderBy($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function groupBy($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    public function having($having)
    {
        $this->having = $having;
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function partBy($column, callable $method)
    {
        $pattern = '/' . $column . ' = (\d+)/i';
        $hasColumn = preg_match($pattern, $this->where, $matches);
        assertOrException($hasColumn, 'can not find part column ' . $column);
        $partId = $matches[1];
        $suffix = $method($partId);
        $this->mainTable .= '_' . $suffix;
        return $this;
    }

    /*
     * 获取数据的相关方法
     */
    public function fetchRow()
    {
        $this->limit = '0,1';
        list($sql, $params) = $this->generateQuery();
        return DB::connection($this->connection)->fetchRow($sql, $params);
    }

    public function fetchAll()
    {
        list($sql, $params) = $this->generateQuery();
        return DB::connection($this->connection)->fetchAll($sql, $params);
    }

    public function fetchColumn()
    {
        $this->limit = '0,1';
        list($sql, $params) = $this->generateQuery();
        return DB::connection($this->connection)->fetchColumn($sql, $params);
    }

    public function fetchColumns($column)
    {
        $this->select($column);
        $list = $this->fetchAll();
        $return = array();
        foreach ($list as $record) {
            $return[] = $record[$column];
        }
        return $return;
    }

    public function count($column = '*')
    {
        $this->select('count('.$column.') as num');
        return $this->fetchColumn();
    }

    public function max($column)
    {
        $this->select('max('.$column.') as num');
        return $this->fetchColumn();
    }

    public function min($column)
    {
        $this->select('min('.$column.') as num');
        return $this->fetchColumn();
    }

    public function avg($column)
    {
        $this->select('avg('.$column.') as num');
        return $this->fetchColumn();
    }

    public function sum($column)
    {
        $this->select('sum('.$column.') as num');
        return $this->fetchColumn();
    }

    public function exists()
    {
        $num = $this->count('*');
        return empty($num) ? false : true;
    }

    public function generateQuery()
    {
        $sql = 'select ' . $this->columns . ' from ' . $this->mainTable . $this->join;
        list($where, $queryParams) = DB_Where::parse($this->where);
        if (!empty($where)) {
            $sql .= ' where ' . $where;
        }
        if (!empty($this->groupBy)) {
            $sql .= ' group by ' . $this->groupBy;
        }
        if (!empty($this->groupBy) && !empty($this->having)) {
            $sql .= ' having ' . $this->having;
        }
        if (!empty($this->orderBy)) {
            $sql .= ' order by ' . $this->orderBy;
        }
        $sql .= ' limit ' . $this->limit;
        return array($sql, $queryParams);
    }

    /*
     * join连接的相关方法
     */
    public function leftJoin($table)
    {
        return new DB_Join($this, $table, 'left');
    }

    public function rightJoin($table)
    {
        return new DB_Join($this, $table, 'right');
    }

    public function innerJoin($table)
    {
        return new DB_Join($this, $table, 'inner');
    }

    public function fullJoin($table)
    {
        return new DB_Join($this, $table, 'full');
    }

    /*
     * 操作数据的相关方法
     */
    public function insert($data)
    {
        $columns = implode(',', array_keys($data));
        $values = implode(',', array_fill(0, count($data), '?'));
        $sql = sprintf('insert into %s (%s) values (%s)', $this->mainTable, $columns, $values);
        DB::connection($this->connection)->execute($sql, array_values($data));
        return DB::connection($this->connection)->lastInsertId();
    }

    public function update($data)
    {
        assertOrException(!empty($this->where), 'please set where when update');
        list($where, $queryParams) = DB_Where::parse($this->where);
        list($updateSql, $updateParams) = $this->parseUpdateData($data);
        $sql = 'update ' . $this->mainTable . ' set ' . $updateSql . ' where ' . $where;
        $params = array_merge($updateParams, $queryParams);
        DB::connection($this->connection)->execute($sql, $params);
        return DB::connection($this->connection)->lastAffectRows();
    }

    public function increment($column, $num = 1)
    {
        return $this->update(array(
            $column . ' = ' . $column . ' + ' . $num
        ));
    }

    public function decrement($column, $num = 1)
    {
        return $this->increment($column, -$num);
    }

    public function delete()
    {
        assertOrException(!empty($this->where), 'please set where when delete');
        list($where, $queryParams) = DB_Where::parse($this->where);
        $sql = sprintf('delete from %s where %s;', $this->mainTable, $where);
        DB::connection($this->connection)->execute($sql, $queryParams);
        return DB::connection($this->connection)->lastAffectRows();
    }


    private function parseUpdateData($data)
    {
        $sql = array();
        $params = array();
        foreach ($data as $key => $value) {
            if (is_int($key)) {
                $sql[] = $value;
            } else {
                $sql[] = $key . '=?';
                $params[] = $value;
            }
        }
        return array(implode(',', $sql), $params);
    }
}

/*
 * Join链接对象，用于提供Join方法
 */
class DB_Join
{
    private $query;
    private $joinTable;
    private $joinType;
    public function __construct(DB_Query $query, $joinTable, $joinType)
    {
        $this->query = $query;
        $this->joinTable = $joinTable;
        $this->joinType = $joinType;
    }

    public function on($on)
    {
        $this->query->join = sprintf(' %s %s join %s on %s', $this->query->join, $this->joinType, $this->joinTable, $on);
        return $this->query;
    }

    public function using($column)
    {
        $this->query->join = sprintf(' %s %s join %s using(%s)', $this->query->join, $this->joinType, $this->joinTable, $column);
        return $this->query;
    }
}

/*
 * 条件解析
 * 用于把条件数组转化为sql语句
 */
class DB_Where
{
    public static function parse(array $where)
    {
        $sql = array();
        $params = array();
        foreach ($where as $key => $value) {
            list($itemSql, $itemParams) = self::parseWhere(trim($key), $value);
            $sql[] = '(' . $itemSql . ')';
            $params = array_merge($params, $itemParams);
        }
        $sql = implode(' and ', $sql);
        return array($sql, $params);
    }

    private static function parseWhere($key, $value)
    {
        if (is_int($key)) { // 无参数
            return array($value, array());
        }
        $value = !is_array($value) ? array($value) : $value; // 参数都转化为数组，方便参数间的合并
        $where = self::parseItemWhere($key, $value);
        assertOrException(substr_count($where, '?') == count($value), 'param num error:' . $where. json_encode($value));
        return array($where, $value);
    }

    private static function parseItemWhere($key, $value)
    {
        if (strpos($key, '?')) { // 源码方式
            return $key;
        }
        list($column, $action) = explode(' ', $key . ' ', 2);
        $action = self::formatAction($action, $value);
        if ($action == 'in' || $action == 'not in') {
            $query = implode(',', array_fill(0, count($value), '?'));
            return sprintf('%s %s (%s)', $column, $action, $query);
        }
        if ($action == 'between') {
            return sprintf('%s between ? and ?', $column);
        }
        return sprintf('%s %s ?', $column, $action);
    }

    private static function formatAction($action, $value)
    {
        $action = trim($action);
        if ($action == '' && count($value) > 1) {
            return 'in';
        }
        if ($action == '') {
            return '=';
        }
        return $action;
    }
}

abstract class DatabaseInstance
{
    abstract public function __construct($connectConfig);
    abstract public function execute($sql, $params = array());
    abstract public function fetchRow($sql, $params = array());
    abstract public function fetchAll($sql, $params = array());
    abstract public function fetchColumn($sql, $params = array());
    abstract public function lastAffectRows();
    abstract public function lastInsertId();
}

class PdoDatabaseInstance extends DatabaseInstance
{
    private $config = array();
    private $connectionPool = array();
    private $lastSql = '';
    private $lastParams = array();
    private $lastInsertId = 0;
    private $lastAffectRows = 0;
    public $useWriteServers = false;
    public $hasReadonlyServers = false;

    public function __construct($config)
    {
        $this->config = $config;
        if (!empty($config['read'])) {
            $this->hasReadonlyServers = true;
        }
    }

    public function beginTransaction()
    {
        $this->onlyUseWriteServers();
        $this->connection('write')->beginTransaction();
    }

    public function commit()
    {
        $this->connection('write')->commit();
        $this->cancelForceWriteServers();
    }

    public function rollBack()
    {
        $this->connection('write')->rollBack();
        $this->cancelForceWriteServers();
    }

    public function execute($sql, $params = array())
    {
        try {
            $this->resetQueryStatus($sql, $params);
            $connection = $this->connection('write');
            $statement = $connection->prepare($sql);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->execute($params);
            $this->lastInsertId = $connection->lastInsertId();
            $this->lastAffectRows = $statement->rowCount();
        }catch(PDOException $e){
            throw new Exception('sql error:' . $this->lastSql . PHP_EOL . json_encode($this->lastParams));
        }
    }

    public function fetchRow($sql, $params = array())
    {
        return $this->fetchData($sql, $params, 'fetch');
    }

    public function fetchAll($sql, $params = array())
    {
        return $this->fetchData($sql, $params, 'fetchAll');
    }

    public function fetchColumn($sql, $params = array())
    {
        return $this->fetchData($sql, $params, 'fetchColumn');
    }

    public function lastAffectRows()
    {
        return $this->lastAffectRows;
    }

    public function lastInsertId()
    {
        return $this->lastInsertId;
    }

    public function lastSql()
    {
        return array($this->lastSql, $this->lastParams);
    }

    public function onlyUseWriteServers()
    {
        $this->useWriteServers = true;
    }

    public function cancelForceWriteServers()
    {
        $this->useWriteServers = false;
    }

    private function fetchData($sql, $params = array(), $fetchType)
    {
        try {
            $this->resetQueryStatus($sql, $params);
            $connectType = $this->useWriteServers ? 'write' : 'read';
            $statement = $this->connection($connectType)->prepare($sql);
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $statement->execute($params);
            return call_user_func(array($statement, $fetchType));
        }catch(PDOException $e){
            throw new Exception('sql error:' . $this->lastSql . PHP_EOL . json_encode($this->lastParams));
        }
    }

    private function resetQueryStatus($sql, $params)
    {
        $this->lastSql = $sql;
        $this->lastParams = $params;
        $this->lastInsertId = 0;
        $this->lastAffectRows = 0;
    }

    private function connection($connectType = 'write')
    {
        // 不存在read服务器则全部使用write服务器
        if ($connectType == 'read' && $this->hasReadonlyServers == false) {
            $connectType = 'write';
        }
        if (!isset($this->connectionPool[$connectType])) {
            $config = $this->selectConnectConfig($connectType);
            $dsn = sprintf('%s:host=%s;dbname=%s;port=%s;',$config['driver'], $config['host'], $config['database'], $config['port']);
            $connection = @new \PDO($dsn, $config['username'], $config['password']);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connection->exec("SET NAMES '{$config['charset']}'");
            $this->connectionPool[$connectType] = $connection;
        }
        return $this->connectionPool[$connectType];
    }

    private function selectConnectConfig($server)
    {
        $config = $this->config[$server];
        $randIndex = mt_rand(0, count($config) - 1);
        return $config[$randIndex];
    }
}