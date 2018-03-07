<?php
/**
 * 虚拟表
 *
 * 隐藏掉实体表的表名，数据库等信息，
 * 虚拟表都可以是几张实体表的Join链接
 *
 * 问题：
 * 1、虚字段的不对应
 * 2. 默认字段的扩展
 */

class Dao
{
    /**
     * 静态管理方法
     */

    // 虚拟表工厂
    public static function newConfig($table = '')
    {
        $daoConfig =  new DaoConfig($table);
        return $daoConfig;
    }

    /**
     * 对外访问接口
     */
    protected $queryTable = '';
    protected $daoConfig = '';
    private $tableFields = array();

    public function __construct(DaoConfig $daoConfig = null)
    {
        // 没有config的则通过load加载子类初始化
        $this->daoConfig = $daoConfig;
        if (empty($this->daoConfig)) {
            $this->daoConfig = new DaoConfig();
            $this->load();
        }
        // 测试配置文件是否可用
        if (!$this->daoConfig->canWork()) {
            throw new Exception('dao config not enough');
        }
        // 初始化dao类变量
        $this->queryTable = table($this->daoConfig->table, $this->daoConfig->database);
        return true;
    }

    protected function load()
    {
        return false;
    }

    /****************************
     * 虚拟表结构相关接口
     ****************************/

    // 获取表字段
    public function getAllFields($hasInfo = false)
    {
        return $hasInfo ? $this->daoConfig->fields : array_keys($this->daoConfig->fields);
    }

    public function getBaseFields($hasInfo = false)
    {
        $reverseHidden = array_flip($this->daoConfig->detailFields);
        $baseFields = array_diff_key($this->daoConfig->fields, $reverseHidden);
        return $hasInfo ? $baseFields : array_keys($baseFields);
    }

    public function isReadonly($field)
    {
        return isset($this->daoConfig->readonlyFields[$field]);
    }

    public function getDetailFields($hasInfo = false)
    {
        $reverseHidden = array_flip($this->daoConfig->detailFields);
        $extractFields = array_intersect_key($this->daoConfig->fields, $reverseHidden);
        return $hasInfo ? $extractFields : array_keys($extractFields);
    }

    // 获取表主键
    public function primaryKey()
    {
        return $this->daoConfig->primaryKey;
    }

    // 获取所有字段的过滤器
    public function getFilters()
    {
        return $this->daoConfig->filters;
    }


    /*************************
     * 设置查询字段
     *************************/

    // 多次存入，一次取出
    public function with($args = null)
    {
        $args = is_array($args) ? $args : func_get_args();
        if (empty($args)) {
            return $this;
        }
        $fields = array();
        $tables = array_unique(explode(',', implode(',', $args)));
        foreach ($tables as $table) {
            $fields = array_merge($fields, $this->tableFields($table));
        }
        return $this->field($fields);
    }

    // 获取所有字段
    public function withAll()
    {
        $tables = array_keys($this->daoConfig->joinTables);
        return call_user_func_array(array($this, 'with'), $tables);
    }

    // 获取特定字段
    public function field($args = null)
    {
        static $fields = array();
        $args = is_array($args) ? $args : func_get_args();
        if (!empty($args)) {
            $args = explode(',', implode(',', $args));
            foreach ($args as $field) {
                if (!isset($this->daoConfig->fields[$field])) {
                    throw new Exception('field not exist:'.$field);
                }
                $fields[] = $field;
            }
            return $this;
        } else {
            $tmp = $fields;
            $fields = array();
            return $tmp;
        }
    }


    /**************************
     * 虚拟表数据获取相关接口
     **************************/

    // 查找数据列表
    public function search($offset, $length, $where = array(), $order = '') // 提供单独的子类可覆盖搜索接口
    {
        return $this->getList($offset, $length, $where, $order);
    }

    // 获取大于某主键id值的列表数据
    public function listAfterId($id, $length, $where = array(), $order = '')
    {
        $where[$this->primaryKey() . ' gt'] = $id;
        return $this->getList(0, $length, $where, $order);
    }

    // 获取小于某主键id值的列表数据
    public function listBeforeId($id, $length, $where = array(), $order = '')
    {
        $where[$this->primaryKey() . ' lt'] = $id;
        return $this->getList(0, $length, $where, $order);
    }

    public function paging($pageNum, $pageSize, $where = array(), $order = '')
    {
        $totalNum = $this->queryTable->count($this->daoConfig->primaryKey, $this->covertCondition($where));
        if ($totalNum == 0) {
            return array(
                'list'=>array(),
                'totalNum'=>0,
            );
        }
        $start = ($pageNum - 1) * $pageSize;
        $list = $this->getList($start, $pageSize, $where, $order);
        return array(
            'list'=>$list,
            'totalNum'=>ceil($totalNum / $pageSize),
        );
    }

    // 根据主键id获取单条记录
    public function infoById($id)
    {
        $data = $this->getList(0, 1, array($this->daoConfig->primaryKey=>$id));
        return (!empty($data)) ? $data[0] : array();
    }

    // 根据某个字段的值获取单条记录
    public function infoByField($value, $fieldName)
    {
        $data = $this->getList(0, 1, array($fieldName=>$value));
        return (!empty($data)) ? $data[0] : array();
    }

    public function detail($id)
    {
        $data = $this->withAll()->getList(0, 1, array($this->daoConfig->primaryKey=>$id));
        return (!empty($data)) ? $data[0] : array();
    }

    private function getList($offset, $length, $where = array(), $order = '')
    {
        $where['limit'] = $offset . ',' . $length;
        if (empty($order)) {
            $order = isset($this->daoConfig->defaultQuery['order']) ? $this->daoConfig->defaultQuery['order'] : $this->primaryKey() . ' desc';
        }
        $where['order'] = $order;

        // 查询字段处理
        $fields = $this->tableFields($this->daoConfig->table);
        $fields = array_unique(array_merge($fields, $this->field()));
        $condition = $where;
        $condition['field'] = implode(',', $this->toTrueFields($fields));

        // 表对象处理
        $table = $this->queryTable;
        $selectTables = $this->fieldsTables($fields);
        foreach ($selectTables as $selectTable) {
            if ($selectTable == $this->daoConfig->table) { // join查询跳过主表
                continue;
            }
            if (!isset($this->daoConfig->joinTables[$selectTable])) { // 检查是否存在join table的配置
                throw new Exception('no join table config:'.$selectTable);
            }
            $join = $this->daoConfig->joinTables[$selectTable];
            $tableName = $join['table'] . ' as ' . $selectTable;
            $on = sprintf('%s.%s = %s.%s', $this->daoConfig->table, $join['mainField'], $selectTable, $join['joinField']);
            $table = $table->leftJoin($tableName)->on($on);
        }

        // 查询条件处理
        $condition = $this->covertCondition($condition);
        $condition = array_merge($this->daoConfig->defaultQuery, $condition);
        if (!empty($this->daoConfig->softDeleteField)) {
            $condition[$this->daoConfig->softDeleteField] = 0;
        }

        // 查询
        $data = $table->where($condition)->fetchAll();
        return $data;
        return DaoPipeline::iteration($data, $this->daoConfig, 'toShow');
    }

    public function appendInfoByIds($fields, array &$data, $dataField = null, $joinField = null)
    {
        if (empty($dataField)) {
            $dataField = $this->daoConfig->primaryKey;
        }
        if (empty($joinField)) {
            $joinField = $this->daoConfig->primaryKey;
        }

        // 检查是否有字段重名
        $fieldsArray = explode(',', $fields);
        foreach ($fieldsArray as $field) {
            if (isset($data[0][$field])) {
                throw new Exception('name exist');
            }
        }

        foreach ($data as $key=>$value) {
            if (!isset($value[$dataField])) {
                throw new Exception('filed not exist');
            }
            $record = $this->queryTable->getRow(array(
                $joinField=>$value[$dataField],
            ));
            $data[$key] = $value + $record;
        }
        return true;
    }

    /*****************************
     * 数据更新相关接口
     *****************************/

    // 更新信息
    public function update($update)
    {

        $update = DaoPipeline::iteration($update, $this->daoConfig, 'toDb');
        return $this->queryTable->update($update);
    }

    // 根据主键id更新信息
    public function updateById($id, $update)
    {
        $update = DaoPipeline::iteration($update, $this->daoConfig, 'toDb');
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
        if (!$this->dataIsSafe(array($key=>$value), $message)) {
            throw new Exception($message);
        }
        return $this->updateById($id, array(
            $key=>$value,
        ));
    }

    // 添加新记录
    public function insert($insert)
    {
        $insert = array_merge($this->getDefaultValues(), $insert);
        if (!$this->dataIsSafe($insert, $message)) {
            throw new Exception($message);
        }
        $insert = DaoPipeline::iteration($insert, $this->daoConfig, 'toDb');
        $insert = $this->groupByTables($insert);
        $mainInsert = $insert[$this->daoConfig->table];
        $mainInsert = array_merge($mainInsert, $this->daoConfig->defaultQuery);
        $mainId = $this->queryTable->insert($mainInsert);
        unset($insert[$this->daoConfig->table]);
        foreach ($insert as $table=>$data) {
            if (!isset($this->daoConfig->joinTables[$table])) { // join表转化为真表
                throw new Exception('not set join info'.$table);
            }
            $join = $this->daoConfig->joinTables[$table];
            $data[$join['joinField']] = $mainId;
            table($join['table'])->insert($data);
        }
        return $mainId;
    }

    // 根据Id更新和替换
    public function replaceById($id, $refresh)
    {
        $hasId = $this->getRow(array(
            $this->primaryKey=>$id,
        ));
        if ($hasId) {
            return $this->updateById($id, $refresh);
        } else {
            return $this->insert($refresh);
        }
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
            return $this->queryTable->delete(array(
            $this->daoConfig->primaryKey=>$id,
            ));
        }
    }

    public function setDefaultQuery($condition)
    {
        $this->daoConfig->defaultQuery = $condition;
    }

    private function dataIsSafe(array $values, &$message = '')
    {
        foreach ($values as $field=>$value) {
            // 字段属性检测
//            if (isset($this->get)) disable过滤

            // 过滤器合法检测
            if (isset($this->daoConfig->filters[$field])) {

            }
        }
        return true;
    }



    /**************************************
     * 缓存计划
     **************************************/
    public function cache()
    {

    }

    public function cancelCache()
    {

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

    private function fieldsTables(array $fields)
    {
        $tables = array();
        foreach ($fields as $field) {
            if (!isset($this->daoConfig->fields[$field])) {
                throw new Exception('field config not set:'.$field);
            }
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

    private function covertCondition($condition)
    {
        $others = array('field'=>'', 'order'=>'', 'group'=>'', 'limit'=>'');
        foreach ($condition as $field=>$params) {
            if (isset($others[$field])) {
                continue;
            }
            list($trueField, $tip) = explode(' ', $field . ' ');
            $trueField = trim($trueField);$tip = trim($tip);
            $trueField = trim($trueField);$tip = trim($tip);
            $trueField = $this->trueField($trueField);
            $trueField = empty($tip) ? $trueField : $trueField . ' ' . $tip;
            unset($condition[$field]);
            $condition[$trueField] = $params;
        }
        $condition = array_merge($this->daoConfig->defaultQuery, $condition);
        return $condition;
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
            $isLegal = DaoFilter::get($filter)->check($value, $params);
            if (!$isLegal) {
                $message = DaoFilter::get($filter)->errorMessage();
                throw new Exception($message);
            }
        }
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
    public $detailFields = array(); // 简要信息中不展示
    public $readonlyFields = array(); // 不可被外部修改的字段
    public $virtualField = array(); // 虚拟字段（外部可见，但内部其实没有）
    public $filters = array(); // 数据过滤器
    public $processing = array(); // 数据处理器
    public $joinTables = array(); // 连接从表
    public $softDeleteField = false; // 软删除标识字段，默认为不存在


    private static $baseFieldType = array(
        'int'=>'number',
        'tinyint'=>'number',
        'smallint'=>'number',
        'bigint'=>'number',
        'varchar'=>'string',
        'text'=>'string',
        'char'=>'string',
        'datetime'=>'date',
        'timestamp'=>'date',
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

    public function setDatabase(array $database)
    {
        $this->database = $database;
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
        $this->fields[$primaryKey] = array(
            'name'=>'id',
            'type'=>'int',
            'unsigned'=>true,
            'primaryKey'=>true,
            'pipeline'=>'int',
            'isVirtual'=>false,
        );
    }

    public function setField($field, $args)
    {
        $args = func_get_args();
        $field = array_shift($args);
        if (isset($this->fields[$field])) {
            throw new Exception('field all ready set:'.$field);
        }
//        $this->fields[$field]['trueField'] = $field;
        $this->analyzeFieldArgs($field, $args);
        $this->safeCheck($field);
        $this->appendDefaultFilters($field, $this->fields[$field]);
        if (isset($this->pipeline[$this->fields[$field]['type']])) {
            $pipeline = $this->pipeline[$this->fields[$field]['type']];
            $this->fields[$field]['pipeline'] = $pipeline;
        }
    }

    public function setFieldEnum($field, $enum)
    {
        if (!isset($this->fields[$field])) {
            throw new Exception('not set field:'.$field);
        }
        $this->fields[$field]['enum'] = $enum;
        $this->fields[$field]['type'] = 'enum';
    }

    public function setFieldMap($field, $map)
    {
        $nameField = $field . 'Name';
        $nameFieldName = $this->fields[$field]['name'] . '名';
//        $this->setField($nameField, 'varchar', 255, $nameFieldName);
        $this->virtualField[$nameField] = array(
            'type'=>'map',
            'map'=>$map,
        );
    }

    public function setFieldHtml($field)
    {
           $this->setFilter($field, 'html');
    }

    public function setFieldDefault($field, $value)
    {
        if (!isset($this->fields[$field])) {
            throw new Exception('not set field:'.$field);
        }
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

    // 设置自定义过滤器
    public function setPipeline(DaoPipeline $Pipeline)
    {

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

    public function setFilter($field, $type, $params = '') // email、mobile、json、
    {
        $this->filters[$field][$type] = $params;
    }

    public function getRegex($regexKey) {
        $this->validation = empty($this->validation) ? Config::get('validation') : $this->validation;
        $regexArr = explode('[', $regexKey);
        $regex = $regexArr[0];
        $range = isset($regexArr[1]) ? rtrim($regexArr[1], ']') : '';

        $regexstr = '';
        $message = '';
        if (empty($range) || !$this->validation[$regex]['isLength']) {
            return $this->validation[$regex];
        } elseif (strpos($range, ':') === false) {
            $regexstr = $this->validation[$regex]['regex'] . '{' . $range . '}$';
            $message = $this->validation[$regex]['message'] . ',并且长度必须为' . $range . '位';
        } elseif (strpos($range, ':') !== false) {
            list($min, $max) = explode(':', $range);
            $regexstr = $this->validation[$regex]['regex'] . '{' . (int)$min . ','. $max. '}$';
            $message = !empty($max) && !empty($min) ? ',长度应该为' . $min . '-' .$max. '位' : '';
            $message = empty($max) ? ',长度最少为' . $min . '位' : $message;
            $message = empty($min) ? ',长度最多为' . $max . '位' : $message;
            $message = $this->validation[$regex]['message'] . $message;
        }
        return array('regex'=>$regexstr, 'message'=>$message);
    }

    public function canWork()
    {
        if (empty($this->table)) {
            throw new Exception('not set table');
        }
        if (empty($this->primaryKey)) {
            throw new Exception('primary key not set');
        }
        return true;
    }

    public function setSoftDelete($field)
    {
        if (!isset($this->fields[$field])) {
            throw new Exception('soft delete field not exist:' . $field);
        }
        $this->softDeleteField = $field;
    }

    // 解析字段参数
    public function analyzeFieldArgs($field, $args)
    {
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
                if (is_word($arg)) {
                    $this->fields[$field]['name'] = $arg;
                    continue;
                }
                // 真实字段识别
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
            if (!isset($params['length'])) {
                throw new Exception('varchar mast set length:'.$field);
            }
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
            if (!isset(self::$defaultMethod[$action])) {
                throw new Exception('not has action:' . $action);
            }
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

abstract class DaoPipeline
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

class IntPipeline extends DaoPipeline
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


class StringPipeline extends DaoPipeline
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

class TextPipeline extends DaoPipeline
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


class HtmlPipeline extends DaoPipeline
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

class JsonPipeline extends DaoPipeline
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

class TimestampPipeline extends DaoPipeline
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