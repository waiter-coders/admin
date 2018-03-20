<?php
/**
 * 虚拟表
 *
 * 暂时只支持mysql字段
 *
 */

class Dao
{
    //装载一个表对象
    public static function load($table, callable $callback)
    {
        $daoConfig =  new DaoConfig($table);
        $callback($daoConfig);
        return new self($daoConfig);
    }

    // 生成配置对象，以便实例化Dao
    public static function newConfig()
    {
        return new DaoConfig();
    }

    /**
     * 对外访问接口
     */
    protected $daoConfig = '';

    public function __construct(DaoConfig $daoConfig)
    {
        $this->daoConfig = $daoConfig;
        assertOrException($this->daoConfig->canWork(), 'dao config not enough');
        return true;
    }


    /****************************
     * 虚拟表结构相关接口
     ****************************/

    public function table()
    {
        return table($this->daoConfig->table, $this->daoConfig->database);
    }

    // 获取表主键
    public function primaryKey()
    {
        return $this->daoConfig->primaryKey;
    }

    // 获取表字段
    public function getAllFields()
    {
        return array_keys($this->daoConfig->fields);
    }

    public function getMainFields()
    {
        $fields = array();
        foreach ($this->daoConfig->fields as $field=>$params) {
            if (!isset($params['sub']) || empty($params['sub'])) {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    public function getSubFields($subName)
    {
        $fields = array();
        foreach ($this->daoConfig->fields as $field=>$params) {
            if (isset($params['sub']) && $params['sub'] == $subName) {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    public function isReadonly($field)
    {
        return isset($this->daoConfig->fields[$field]['readonly']) && ($this->daoConfig->fields[$field]['readonly'] == true);
    }

    public function getFieldsParams(array $fields)
    {
        $fields = array_flip($fields);
        return array_intersect_key($this->daoConfig->fields, $fields);
    }


    /**************************
     * 虚拟表数据获取相关接口
     **************************/

    // 获取大于某主键id值的列表数据
    public function listAfterId($id, $length, $fields = '*', $where = array(), $order = '')
    {
        $where[$this->primaryKey() . ' >'] = $id;
        return $this->getList($fields, $where, $order, 0, $length);
    }

    // 获取小于某主键id值的列表数据
    public function listBeforeId($id, $length, $fields = '*', $where = array(), $order = '')
    {
        $where[$this->primaryKey() . ' <'] = $id;
        return $this->getList($fields, $where, $order, 0, $length);
    }


    // 根据主键id获取单条记录
    public function infoById($id, $fields = '*')
    {
        $data = $this->getList($fields, array($this->daoConfig->primaryKey=>$id), '', 0, 1);
        return (!empty($data)) ? $data[0] : array();
    }

    // 根据某个字段的值获取单条记录
    public function infoByField($fieldName, $value, $fields = '*')
    {
        $data = $this->getList($fields, array($fieldName=>$value), '', 0, 1);
        return (!empty($data)) ? $data[0] : array();
    }


    public function detail($id, $subTables = '*')
    {
        $fields = (empty($subTables) || $subTables == '*') ? $this->getAllFields() : array_merge($this->getMainFields(), $this->getSubFields($subTables));
        return $this->infoById($id, $fields);
    }

    public function getList($fields = '*', $where = array(), $order = '', $offset = 0, $length = 100)
    {
        // 查询字段处理
        $fields = (empty($fields) || $fields == '*') ? $this->getMainFields() : explode(',', $fields);

        // 表对象处理
        $tableDao = $this->table();
        $tables = $this->selectTables($fields);
        foreach ($tables as $table) {
            if ($table == $this->daoConfig->table) { // join查询跳过主表
                continue;
            }
            // 检查是否存在join table的配置
            assertOrException(isset($this->daoConfig->joinTables[$table]), 'no join table config:'.$table);
            $join = $this->daoConfig->joinTables[$table];
            $tableName = $join['table'] . ' as ' . $table;
            $on = sprintf('%s.%s = %s.%s', $this->daoConfig->table, $join['mainField'], $table, $join['joinField']);
            $tableDao = $tableDao->leftJoin($tableName)->on($on);
        }

        // 查询条件处理
        $where = $this->toTrueWhere($where);
        $where = array_merge($this->daoConfig->defaultQuery, $where);
        if (!empty($this->daoConfig->softDeleteField)) {
            $where[$this->daoConfig->softDeleteField] = 0;
        }

        // 查询
        $fields = implode(',', $this->toTrueFields($fields));
        $defaultOrder = isset($this->daoConfig->defaultQuery['order']) ? $this->daoConfig->defaultQuery['order'] : $this->primaryKey() . ' desc';
        $order = empty($order) ? $defaultOrder : $order;
        return $tableDao->select($fields)->where($where)->orderBy($order)->limit($offset . ',' . $length)->fetchAll();
//        return RowPipeline::iteration($data, $this->daoConfig, 'toShow');
    }


    /*****************************
     * 数据更新相关接口
     *****************************/

    // 更新信息
    public function update($update, $where)
    {

        $update = RowPipeline::iteration($update, $this->daoConfig, 'toDb');
        return $this->table()->where($where)->update($update);
    }

    // 根据主键id更新信息
    public function updateById($id, $update)
    {
        $update = RowPipeline::iteration($update, $this->daoConfig, 'toDb');
        $update = $this->groupByTables($update);
        foreach ($update as $table=>$data) {
            $idField = $this->daoConfig->primaryKey;
            if (isset($this->daoConfig->joinTables[$table])) { // join表转化为真表
                $idField = $this->daoConfig->joinTables[$table]['joinField'];
                $table = $this->daoConfig->joinTables[$table]['table'];
            }
            table($table)->where(array($idField=>$id))->update($data);
        }
        return true;
    }

    // 根据某一字段值更新信息
    public function updateField($id, $key, $value)
    {
        assertOrException($this->dataIsSafe(array($key=>$value), $message), $message);
        return $this->updateById($id, array(
            $key=>$value,
        ));
    }

    // 添加新记录
    public function insert($insert)
    {
        $insert = array_merge($this->getDefaultValues(), $insert);
        assertOrException($this->dataIsSafe($insert, $message), $message);
        $insert = RowPipeline::iteration($insert, $this->daoConfig, 'toDb');
        $insert = $this->groupByTables($insert);
        $mainInsert = $insert[$this->daoConfig->table];
        $mainInsert = array_merge($mainInsert, $this->daoConfig->defaultQuery);
        $mainId = $this->table()->insert($mainInsert);
        unset($insert[$this->daoConfig->table]);
        foreach ($insert as $table=>$data) {
            assertOrException(isset($this->daoConfig->joinTables[$table]), 'not set join info'.$table);
            $join = $this->daoConfig->joinTables[$table];
            $data[$join['joinField']] = $mainId;
            table($join['table'])->insert($data);
        }
        return $mainId;
    }

    // 根据Id更新和替换
    public function replaceById($field, $value, $refresh)
    {
        $currentDao = $this;
        DB::transaction(function() use($currentDao, $field, $value, $refresh){
            $hasId = $currentDao->table()->select($currentDao->primaryKey())->where(array(
                $field=>$value,
            ))->fetchColumn();
            if ($hasId) {
                return $currentDao->update($refresh, array(
                    $field=>$value,
                ));
            } else {
                $refresh[$field] = $value;
                return $currentDao->insert($refresh);
            }
        });
    }

    // 删除新纪录
    public function delete($id)
    {
        // 软删除
        if (!empty($this->daoConfig->softDeleteField)) {
            return $this->updateById($id, array(
                $this->daoConfig->softDeleteField=>1,
            ));
        }
        // 硬删除
        else {
            return $this->table()->where(array(
                $this->daoConfig->primaryKey=>$id,
            ))->delete();
        }
    }


    /**
     * 私有函数
     */
    private function getDefaultValues()
    {
        $values = array();
        foreach ($this->daoConfig->fields as $field=>$params) {
            if (isset($params['default'])) {
                $values[$field] = $params['default'];
            }
        }
        return $values;
    }

    private function tableFields($table)
    {
        if (empty($this->tableFields)) {
            $mainTable = $this->daoConfig->table;
            foreach ($this->daoConfig->fields as $field=>$params) {
                $tableName = isset($params['table']) ? $params['table'] : $mainTable;
                $this->tableFields[$tableName][] = $field;
            }
        }
        return isset($this->tableFields[$table]) ? $this->tableFields[$table] : array();
    }

    private function selectTables(array $fields)
    {
        $tables = array();
        foreach ($fields as $field) {
            assertOrException(isset($this->daoConfig->fields[$field]), 'field config not set:'.$field);
            $config = $this->daoConfig->fields[$field];
            if (isset($config['table'])) {
                $tables[$config['table']] = 1;
            }
        }
        return array_keys($tables);
    }

    private function toTrueFields(array $fields)
    {
        $trueFields = array();
        foreach ($fields as $field) {
            $trueField = $this->trueField($field);
            if ($trueField) {
                $trueFields[] = $trueField . ' as ' . $field;
            }
        }
        return $trueFields;
    }

    private function trueField($field)
    {
        $params = $this->daoConfig->fields[$field];
        if (isset($params['isVirtual']) &&  $params['isVirtual'] == true) {
            return null;
        }
        $table = isset($params['table']) ? $params['table'] : $this->daoConfig->table;
        return isset($params['trueField']) ? $params['trueField'] : $table . '.' . $field;
    }

    private function toTrueWhere($where)
    {
        foreach ($where as $field=> $params) {
            list($trueField, $tip) = explode(' ', $field . ' ');
            $trueField = trim($trueField);$tip = trim($tip);
            $trueField = trim($trueField);$tip = trim($tip);
            $trueField = $this->trueField($trueField);
            $trueField = empty($tip) ? $trueField : $trueField . ' ' . $tip;
            unset($where[$field]);
            $where[$trueField] = $params;
        }
        return $where;
    }

    private function groupByTables($data)
    {
        $dataByTable = array();
        foreach ($data as $field=>$value) {
            $table = $this->daoConfig->table;
            if (isset($this->daoConfig->fields[$field]['trueField'])) {
                list($table, $field) = explode('.', $this->daoConfig->fields[$field]['trueField']);
            }
            $dataByTable[$table][$field] = $value;
        }
        return $dataByTable;
    }

    public function iteration($values, $config, $direction)
    {
        // 空值处理
        if (empty($values)) {
            return array();
        }

        // 多条记录处理
        if (isset($values[0]) && is_array($values[0])) {
            foreach ($values as $key=>$value) {
                $values[$key] = $this->iteration($value, $config, $direction);
            }
            return $values;
        }

        // 单条记录处理
        foreach ($values as $field=>$value) {
            $class = $this->check($field, $config->fields[$field]);
            $class->$direction($values, $field);
        }

        // 虚字段处理
        foreach ($config->fields as $field=>$params) {
            if ($params['isVirtual'] == true) {
                $class = $this->check($field, $params);
                $class->$direction($values);
            }
        }

        return $values;
    }

    public function check($value, $filters)
    {
        foreach ($filters as $filter=>$params) {
            $isLegal = DaoFilter::check($filter, $value, $params);
//            assertOrException($isLegal, DaoFilter::get($filter)->errorMessage());
        }
        return true;
    }

    private function dataIsSafe()
    {
        return true;
    }
}


/*
 * 虚拟表数据构造类
 */
class DaoConfig
{
    public $table; // 表名
    public $primaryKey; // 主键
    public $database = null;
    public $fields = array(); // 字段基础信息
    public $defaultQuery = array(); // 默认查询
    public $readonlyFields = array(); // 不可被外部修改的字段
    public $filters = array(); // 数据过滤器
    public $format = array(); // 数据返回至标准化
    public $joinTables = array(); // 连接从表
    public $softDeleteField = false; // 软删除标识字段，默认为不存在

    private static $baseFieldType = array(
        'int'=>'number',
        'tinyint'=>'number',
        'smallint'=>'number',
        'bigint'=>'number',
        'varchar'=>'string',
        'char'=>'string',
        'text'=>'string',
        'datetime'=>'datetime',
        'date'=>'date',
        'timestamp'=>'datetime',
        'enum'=>'',
    );

    private static $extendFieldType = array(
        'html'=>array('type'=>'text', 'filter'=>'string', 'params'=>'html'),
        'json'=>array('type'=>'varchar', 'filter'=>'string', 'params'=>'json'),
        'email'=>array('type'=>'varchar', 'filter'=>'regex', 'params'=>'\w+@\w(\.\w+)+'),
    );

    private $defaultFilters = array(
        'empty'=>array('action'=>'regex', 'errorMessage'=>'不能为空！', 'regex'=>'[\w|\W]{1,}'),
        'length'=>array('action'=>'regex', 'errorMessage'=>'长度应该在@min～@max之间!', 'regex'=>'[\w|\W]{@min,@max}', 'min'=>1, 'max'=>255),
        'number'=>array('action'=>'regex', 'errorMessage'=>'必须为数字', 'regex'=>'\d+'),
        'email'=>array('action'=>'regex', 'errorMessage'=>'邮箱格式错误', 'regex'=>'\w+@\w(\.\w+)+'),
    );

    private $pipeline = array(
        'timestamp'=>'timestamp',
    );

    public function __construct($table = '')
    {
        $this->setTable($table);
    }

    public function setDatabase($database)
    {
        $this->database = $database;
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function setPrimaryKey($primaryKey, $name = 'id', $type = 'int', $unsigned = true)
    {
        $this->primaryKey = $primaryKey;
        $this->fields[$primaryKey] = array(
            'name'=>$name,
            'group'=>'base',
            'type'=>$type,
            'unsigned'=>$unsigned,
            'primaryKey'=>true,
        );
    }

    public function setField($field, $args)
    {
        $args = func_get_args();
        $field = array_shift($args);
        assertOrException(!isset($this->fields[$field]), 'field all ready set:'.$field);
        $this->fields[$field] = $this->extractFieldParams($args);
        $this->safeCheck($field);
        $this->appendDefaultFilters($field, $this->fields[$field]);
        if (isset($this->pipeline[$this->fields[$field]['type']])) {
            $pipeline = $this->pipeline[$this->fields[$field]['type']];
            $this->fields[$field]['pipeline'] = $pipeline;
        }
    }

    public function setFieldMap($field, callable $callback, $nameField = '', $nameFieldName = '')
    {
        $nameField = empty($nameField) ? $field . 'Name' : $nameField;
//        $nameFieldName = empty($nameFieldName) ? $this->fields[$field]['name'] . '名' : $nameFieldName;
        $this->setRowFilter(function(&$row) use ($field, $nameField, $callback) {
            $row[$nameField] = $callback($row[$field]);
        });
    }

    public function setFieldEnum($field, array $enum)
    {

    }

    public function setFieldDefault($field, $value)
    {
        assertOrException(isset($this->fields[$field]), 'not set field:'.$field);
        $this->fields[$field]['default'] = $value;
    }

    // 设置join表
    public function setJoinTable($joinTable, $mainField, $joinField = null)
    {
        list($table, $tableLabel) = explode(' ', $joinTable . ' ', 2);
        $table = trim($table);$tableLabel = trim($tableLabel);
        $tableLabel = empty($tableLabel) ? $table : $tableLabel;
        $joinField = empty($joinField) ? $mainField : $joinField;
        $this->joinTables[$tableLabel] = array('table'=>$table, 'mainField'=>$mainField, 'joinField'=>$joinField);
    }

    // 设置默认查询条件
    public function setDefaultQuery(array $condition)
    {
        $this->defaultQuery = $condition;
    }

    // 设置过滤器
    public function setRowFilter(callable $callback)
    {
        $this->filters[] = $callback;
    }

    // 设置返回值处理
    public function setRowFormat(callable $callback)
    {
        $this->format[] = $callback;
    }

    // 获取字段过滤器，带参数则直接返回对象
    public function getPipeline($field, $PipelineName = '')
    {

    }

    // 设置字段为基本信息不可见
    public function setFieldsIsDetail($fields)
    {
        $fields = func_get_args();
        $fields = implode(',', $fields);
        $fields = explode(',', $fields);
        $this->detailFields = array_merge($this->detailFields, $fields);
    }

    // 设置信息不可以被修改
    public function setFieldsReadonly($fields)
    {
        $fields = func_get_args();
        $fields = implode(',', $fields);
        $fields = explode(',', $fields);
        $fields = array_flip($fields);
        $this->readonlyFields = array_merge($this->readonlyFields, $fields);
    }

    public function canWork()
    {
        assertOrException(!empty($this->table), 'not set table');
        assertOrException(!empty($this->primaryKey), 'primary key not set');
        return true;
    }

    public function setSoftDelete($field)
    {
        assertOrException(isset($this->fields[$field]), 'soft delete field not exist:' . $field);
        $this->softDeleteField = $field;
    }

    // 解析字段参数
    public function extractFieldParams($args)
    {
        $params = array();
        while (!empty($args)) {
            $arg = array_shift($args);

            // 字符串类型
            if (is_string($arg)) {
                // 基础字段类型识别
                if (isset(self::$baseFieldType[$arg])) {
                    $this->fields[$field]['type'] = $arg;
                    continue;
                }
                // 扩展字段类型识别
                if (isset(self::$extendFieldType[$arg])) {
                    $this->fields[$field]['type'] = self::$extendFieldType[$arg]['type'];
                    $this->filters[$field][self::$extendFieldType[$arg]['filter']] = self::$extendFieldType[$arg]['params'];
                    continue;
                }
                // unsigned识别
                if ($arg == 'unsigned') {
                    $this->fields[$field]['unsigned'] = true;
                    continue;
                }
                // 含有中文的被认为是字段名
                if (preg_match("/[\x7f-\xff]/", $arg)) { // 暂时只是utf8
                    $this->fields[$field]['name'] = $arg;
                    continue;
                }
                // join字段
                if ($pos = strpos($arg, '.')) {
                    $this->fields[$field]['trueField'] = $arg;
                    $table = substr($arg, 0, $pos);
                    $this->fields[$field]['table'] = $table;
                    continue;
                }

                // 前置下划线被认为是过滤器
                if ($arg[0] == '_') {
                    $this->filters[$field]['regex'] = ltrim($arg, '_');
                    continue;
                }
            }
            // 数字认为是长度
            if (is_int($arg)) {
                $this->fields[$field]['length'] = $arg;
                continue;
            }
            // 数据被认为是map
            if (is_array($arg)) {
                $this->fields[$field]['range'] = $arg;
            }
            // 可调用函数
            if (is_callable($arg)) {
                $params['type'] = '';
                $params['pipeline'] = 'DisplayPipeline';
                $params['call'] = $arg;
                $params['isVirtual'] = true;
                continue;
            }

            throw new Exception('no look:' . $arg);
        }
    }

    private function safeCheck($field)
    {
        $params = $this->fields[$field];
        if ($params['type'] == 'varchar') {
            assertOrException(isset($params['length']), 'varchar mast set length:'.$field);
        }
    }

    private function appendDefaultFilters($field, $params)
    {
        if (!isset($params['default'])) {
            $this->filters[$field][] = $this->defaultFilters['empty'];
        }
        if (isset($params['length']) && $params['type'] == 'varchar') {
            $filter = $this->defaultFilters['length'];
            $filter['max'] = $params['length'];
            $filter = $this->replaceTemplateArgs($filter);
            $this->filters[$field][] = $filter;
        }
        if ($params['type'] == 'int') {
            $this->filters[$field][] = $this->defaultFilters['number'];
        }
//        if ($params['type'] == 'varchar' && !isset($this->filters[$field]['string'])) {
//            $filter = ($params['length'] > 255) ? 'text' : 'string';
//            $this->filters[$field]['string'] = $filter;
//        }
    }

    private function replaceTemplateArgs($filter)
    {
        $response = array();
        foreach ($filter as $key=>$value) {
            $response[$key] = $value;
            foreach ($filter as $replaceKey=>$replaceValue) {
             $response[$key] = str_replace('@' . $replaceKey, $replaceValue, $response[$key]);
            }
        }
        return $response;
    }
}

class DaoFilter
{
    private static $defaultMethod = array('regex'=>1);

    public static function check($action, $value, $params)
    {
        if (is_string($action)) {
            assertOrException(isset(self::$defaultMethod[$action]), 'not has action:' . $action);
            return call_user_func(array('DaoFilter', $action), $value, $params);
        }
        if (is_callable($action)) {
            return $action($value, $params);
        }
        return false;
    }

    public static function regex($value, $params)
    {
        $pattern = $params['regex'];
        return preg_match($pattern, $value);
    }
}

abstract class RowPipeline
{
    abstract public function toShow($value);
    abstract public function toDb($value);

    public static function iteration($values, $config, $direction)
    {
        // 空值处理
        if (empty($values)) {
            return array();
        }
        // 多条记录处理
        if (isset($values[0]) && is_array($values[0])) {
            foreach ($values as $key=>$value) {
                $values[$key] = self::iteration($value, $config, $direction);
            }
            return $values;
        }
        // 单条记录处理
        foreach ($values as $field=>$value) {
            if (isset($config->fields[$field]['pipeline'])) {
                $class = self::fieldInstance($config->fields[$field]['pipeline']);
                $values[$field] = $class->$direction($value);
            }
        }
        return $values;
    }

    private static function fieldInstance($pipeline)
    {
        static $classes = array();
        if (!isset($classes[$pipeline])) {
            $Pipeline = ucfirst($pipeline) . 'Pipeline';
            $classes[$pipeline] = new $Pipeline();
        }
        return $classes[$pipeline];
    }
}

class IntPipeline extends RowPipeline
{
    public function toShow($value)
    {
        return $value;
    }

    public function toDb($value)
    {
       return (int)$value;
    }
}


class StringPipeline extends RowPipeline
{
    public function toShow($value)
    {
        return preg_replace("/[\r|\n]+/", '', stripslashes($value));
    }

    public function toDb($value)
    {
        $value = addslashes($value);
        return trim($value);
    }
}

class TextPipeline extends RowPipeline
{
    public function toShow($value)
    {
        return stripslashes($value);
    }

    public function toDb($value)
    {
        return trim($value);
    }
}


class HtmlPipeline extends RowPipeline
{
    public function toShow($value)
    {
        return stripslashes($value);
    }

    public function toDb($value)
    {
        $value = addslashes($value);
        return trim($value);
    }
}

class JsonPipeline extends RowPipeline
{
    public function toShow($value)
    {
        return json_decode($value, true);
    }

    public function toDb($value)
    {
        return json_encode($value);
    }
}

class TimestampPipeline extends RowPipeline
{
    public function toDb($value)
    {
        return $value;
    }

    public function toShow($value)
    {
        return $value;
    }
}