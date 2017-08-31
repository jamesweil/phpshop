<?php
namespace Common\Model;

use Think\Model;
use Think\Exception;
use Think\Page;
use Think\Hook;
use Common\TraitClass\MethodModel;
use Common\Tool\Tool;
use Common\TraitClass\ModelToolTrait;
use Common\TraitClass\ColumShowHTMLTrait;
use Common\Tool\Event;
use Common\Tool\Extend\parseString;
use Common\Behavior\AddLogData;

/**
 * 数据操作 控制
 * @author 王强
 * @version 1.0.2
 */
abstract class BaseModel extends Model
{
    use MethodModel;
    
    use ModelToolTrait;
    
    use InterFacePropertyClass;
    
    use ColumShowHTMLTrait;
    // 数据库字段显示页面 操作【添加、编辑】
    private static $colums = array();

    private static $obj = array();
    
    // 当前插入编号
    protected static $insertId = 0;

    protected $isOpenTranstion = false;
    
    // 不检测搜索的键
    protected $noValidate;

    protected static $find = 'public static function getInitnation()';

    protected $findWhere = null;

    const DESC = ' DESC ';

    const ASC = ' ASC ';

    const desc = 'desc';
    
    const BETWEEN = ' between ';
    
    // 总钱数
    protected static $totalMonery = 0.0;
    // 商品数量
    protected static $number = 0;
    
    // //////////////////////////////////////////日志相关操作
    const LOG_INERT = 0x00;
    // |操作类型：0新增1修改2删除
    const LOG_UPDATE = 0x01;
    // |
    const LOG_DELETE = 0x02;
    // |
    protected $isLogObj = FALSE;
    // 是否是日志模型 是 不添加日志数据
    protected $solaveData = [];
    // 日志从表数据赋值
    protected static $deleteDataLog;
    // 删除时保存的日志数据
    protected static $cloneObj;
    // 要添加日志的模型对象
    // ///////////////////////////////////////////////////////
    const asc = 'asc';

    const DBAS = ' as ';

    const SUFFIX = '_d';
    
    // 是否提交事务
    protected $isCommit = FALSE;

    protected $split;
    
    // where条件
    protected $where;
    // 排序
    protected $order;
    
    // 审核用户是否通过
    protected static $approvalPuss = FALSE;
    
    // 日志表里 操其他表作的主键
    protected static $logPrimaryKey;
    
    // 适用于模糊搜索的键
    protected $buildWhereByKey;
    
    // 搜索时日期的键
    protected $searchCreateTimeKey;

    /**
     * 获取搜索日期的字段
     * @return the $searchCreateTimeKey
     */
    public function getSearchCreateTimeKey()
    {
        return $this->searchCreateTimeKey;
    }

    /**
     * 设置搜索日期的字段
     * @param string $searchCreateTimeKey            
     */
    public function setSearchCreateTimeKey($searchCreateTimeKey)
    {
        $this->searchCreateTimeKey = $searchCreateTimeKey;
    }

    /**
     * 获取 模糊搜索的字段
     * @return the $buildWhereByKey
     */
    public function getBuildWhereByKey()
    {
        return $this->buildWhereByKey;
    }

    /**
     * 设置模糊搜索的字段
     * @param field_type $buildWhereByKey            
     */
    public function setBuildWhereByKey($buildWhereByKey)
    {
        $this->buildWhereByKey = $buildWhereByKey;
    }

    /**
     * 获取提交事务状态
     * @return the $isCommit
     */
    public function getIsCommit()
    {
        return $this->isCommit;
    }

    /**
     * 设置提交事务状态
     * @param boolean $isCommit            
     */
    public function setIsCommit($isCommit)
    {
        $this->isCommit = $isCommit;
    }

    /**
     *
     * @return the $approvalPuss
     */
    public function getApprovalPuss()
    {
        return self::$approvalPuss;
    }

    /**
     *
     * @param boolean $approvalPuss            
     */
    public function setApprovalPuss($approvalPuss)
    {
        self::$approvalPuss = $approvalPuss;
    }

    /**
     *
     * @return the $where
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     *
     * @return the $order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     *
     * @param field_type $where            
     */
    public function setWhere($where)
    {
        $this->where = $where;
    }

    /**
     *
     * @param field_type $order            
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     *
     * @return the $split
     */
    public function getSplit()
    {
        return $this->split;
    }

    /**
     *
     * @param field_type $split            
     */
    public function setSplit($split)
    {
        $this->split = $split;
    }

    /**
     *
     * @return the $number
     */
    public function getNumber()
    {
        return self::$number;
    }

    /**
     *
     * @param number $number            
     */
    public function setNumber($number)
    {
        self::$number = $number;
    }

    /**
     *
     * @return the $totalMonery
     */
    public function getTotalMonery()
    {
        return self::$totalMonery;
    }

    /**
     *
     * @param number $totalMonery            
     */
    public function setTotalMonery($totalMonery)
    {
        self::$totalMonery = $totalMonery;
    }

    /**
     *
     * @return the $noValidate
     */
    public function getNoValidate()
    {
        return $this->noValidate;
    }

    /**
     * 不检测搜索的键
     *
     * @param array $noValidate            
     */
    public function setNoValidate(array $noValidate)
    {
        $isPuss = Tool::checkPost($noValidate);
        
        if ($isPuss) {
            return;
        }
        
        $this->noValidate = $noValidate;
    }

    /**
     * 取得子类的实例【用父类实例化子类】
     */
    public static function getInstance($className, $metheds = 'getInitnation')
    {
        if (empty(self::$obj[$className])) {
            self::$obj[$className] = $className::$metheds();
        }
        return self::$obj[$className];
    }

    /**
     * 获取表数据 用于组装搜索条件
     *
     * @param array $array            
     * @return array;
     */
    public function getDataByUseWhere(array $data, $findKey)
    {
        if (empty($data[$this->buildWhereByKey])) {
            return array();
        }
        
        $param = array();
        
        $param[$findKey] = array(
            'like',
            $data[$this->buildWhereByKey] . '%'
        );
        
        $idArray = $this->where($param)->getField(static::$id_d . ',' . $findKey);
        return $idArray;
    }

    /**
     * 根据其他表数据组装搜索条件
     */
    public function getWhereByData(array &$data, $findKey)
    {
        $idArray = $this->getDataByUseWhere($data, $findKey);
        
        if (empty($idArray)) {
            return array();
        }
        
        $param = array();
        
        $inWhere = implode(',', array_keys($idArray));
        
        $param[$this->buildWhereByKey] = array(
            'in',
            [
                $inWhere
            ]
        );
        unset($data[$this->buildWhereByKey]);
        return $param;
    }

    /**
     * 组装搜索条件
     *
     * @param array $data
     *            搜索条件数组
     * @return array;
     */
    public function buildSearch(array $data, $isLike = false, array $likeSearch = array())
    {
        if (! is_array($data) || empty($data)) {
            return array();
        }
        // 处理查询条件
        $orderBy = Tool::buildActive($data);
        
        if (empty($orderBy)) {
            return array();
        }
        
        $noValidate = $this->noValidate;
        
        if (! empty($noValidate)) { // 不参与搜索的键
            foreach ($orderBy as $key => $value) {
                if (! in_array($key, $noValidate, true)) {
                    continue;
                }
                unset($orderBy[$key]);
            }
        }
        
        $where = $this->create($orderBy);
        
        $timeWhere = array();
        // 处理日期
        if (! empty($this->searchCreateTimeKey)) {
            
            $timeWhere = $this->parseTimeWhere($data[$this->searchCreateTimeKey]);
        }
        
        if (! empty($timeWhere)) {
            $where[$this->searchCreateTimeKey] = $timeWhere;
        }
        
        if ($isLike && empty($likeSearch)) {
            $queryWhere = array();
            foreach ($where as $key => $value) {
                $queryWhere[$key] = array(
                    'like',
                    $value . '%'
                );
            }
            return $queryWhere;
        } else 
            if ($isLike && ! empty($likeSearch)) {
                foreach ($likeSearch as $key => $value) {
                    if (! array_key_exists($value, $where)) {
                        continue;
                    }
                    $where[$value] = array(
                        'like',
                        $where[$value] . '%'
                    );
                }
            }
        return $where;
    }

    /**
     * 去除不查询的字段
     *
     * @param array $fields
     *            要去除查询的字段
     * @return array;
     */
    public function deleteFields(array $fields)
    {
        $fieldsDb = $this->getDbFields();
        if (empty($fields)) {
            return array();
        }
        foreach ($fieldsDb as $key => $name) {
            if (in_array($name, $fields)) {
                unset($fieldsDb[$key]);
            }
        }
        return $fieldsDb;
    }

    /**
     * 根据其他模型数据 获取相应的数据 适应于一对多关系
     * @param array $data 其他模型数据
     * @param string $id  以那个字段拼接数据
     * @param array $field 字段
     * @param mixed $where 筛选条件
     * @return array
     */
    public function getDataByOtherModel(array $data, $id, array $field, $where)
    {
        if (! $this->isEmpty($data) || ! $this->isEmpty($field) || empty($id) || empty($where)) {
            return $data;
        }
        
        $dbFields = $this->getDbFields();
        
        if (! in_array($where, $dbFields)) {
            return $data;
        }
        
        $idString = str_replace('"', null, Tool::characterJoin($data, $id));
        
        if (empty($idString)) {
            return $data;
        }
        // order by instr('3,2,3,12,1',concat(',',id,',')
        $getData = $this->field($field)
            ->where($where . ' in (' . $idString . ')' . $this->findWhere)
            ->order('SUBSTRING_INDEX("' . $idString . '",' . $where . ', 1)')
            ->select();
        
        if (empty($getData)) {
            return $data;
        }
        
        foreach ($getData as $key => &$value) {
            
            if (! array_key_exists($where, $value)) {
                continue;
            }
            
            $getData[$key][$id] = $value[$where];
            
            if ($id === $where) {
                unset($getData[$key][$where]);
            }
        }
      
        $data = Tool::oneReflectManyArray($getData, $data, static::$id_d, $id);
        return $data;
    }

    /**
     * 获取商品属性数据
     */
    public function getAttribute($options, $isNoSelect = false, $default = 'select')
    {
        if (empty($options['field'])) {
            return array();
        }
        if ($isNoSelect) {
            $options['field'] = $this->deleteFields($options['field']);
        }
        return $this->$default($options);
    }

    /**
     * 分页读取数据
     */
    public function getDataByPage(array $options, $pageNumer = 10, $isNoSelect = false, $pageObj = Page::class)
    {
        if (empty($options) || ! is_int($pageNumer)) {
            return array();
        }
        
        if (! empty($_SESSION['where']) && is_array($_SESSION['where'])) {
            $count = $this->where($_SESSION['where'])->count();
            
            $_SESSION['where'] = null;
        } else {
            $count = ! empty($options['where']) ? $this->where($options['where'])->count() : $this->count();
        }
        
        $page = new $pageObj($count, $pageNumer);
        $param = empty($_POST) ? $_GET : $_POST;
        Hook::listen('Search', $param);
        
        $page->parameter = $param;
        
        $options['limit'] = $page->firstRow . ',' . $page->listRows;
        
        $data = $this->getAttribute($options, $isNoSelect);
        
        $array['data'] = $data;
        $array['page'] = $page->show();
        
        return $array;
    }

    /**
     * 重写添加
     * {@inheritDoc}
     * @see \Think\Model::add()
     */
    public function add($data = '', $options = array(), $replace = false)
    {
        if (empty($data)) {
            return false;
        }
        $data = $this->create($data);
        $insertId = parent::add($data, $options, $replace);
        
        if ($this->isLogObj) { // /是否是 日志模型 日志模型 不需要 添加日志
            
            return $insertId;
        }
        
        if (MODULE_NAME === 'Home') {
            return $insertId;
        }
        
        self::$cloneObj = clone $this;
        
        $this->solaveData = $data;
        
        // 写入日志主表
        $param = $this->addLogFlag($insertId);
        $param['type'] = self::LOG_INERT;
        Hook::add('insertLog', AddLogData::class);
        
        Hook::listen('insertLog', $param);
        
        return $insertId;
    }

    /**
     * 删除前的回调方法
     * {@inheritdoc}
     * @see \Think\Model::_before_delete()
     */
    protected function _before_delete($options)
    {
        $field = $this->deleteFields([
            'create_time',
            'update_time',
            'id'
        ]);
      
        self::$deleteDataLog = (array)$this->field($field)->find($options);
    }

    /**
     * 删除后的回调方法
     * {@inheritdoc}
     * @see \Think\Model::_after_delete()
     */
    protected function _after_delete($data, $options)
    {
       
        if (empty($data)) {
            return FALSE;
        }
        
        if ($this->isLogObj) { // 屏蔽日志模型
            return true;
        }
        
        if (MODULE_NAME === 'Home') {
            return true;
        }
        self::$cloneObj = clone $this;
        
        $this->solaveData = self::$deleteDataLog;
       
        $param = $this->addLogFlag($data[static::$id_d]);
        
        $param['type'] = self::LOG_DELETE;
        
        Hook::add('deleteDataInsertLog', AddLogData::class);
        
        Hook::listen('deleteDataInsertLog', $param);
    }

    /**
     * 日志操作辅助函数
     * @param integer $insertId            
     * @return array
     */
    protected function addLogFlag($insertId)
    {
        if (($insertId = (int) $insertId) === 0 || ! $this->isEmpty($this->solaveData)) {
            return array();
        }
        $tabName = $this->getTableName();
        
        $param = [
            'table_name' => $tabName,
            'admin_id' => $_SESSION['aid'],
            'table_id' => $insertId,
            'comment' => $this->getAllTableNotes($tabName), // 表注释
            'edit_colums' => $this->solaveData
        ]; // 数据

        return $param;
    }

    /**
     * save 保存 更新 及其 日志操作
     * {@inheritDoc}
     * @see \Think\Model::save()
     */
    public function save($data = '', $options = array())
    {
        if (empty($data)) {
            return false;
        }
        $data = $this->create($data);
        
        ! $this->isOpenTranstion ?: $this->startTrans();
        
        if (MODULE_NAME === 'Home' || $this->isLogObj) { // 屏蔽日志模型
            
            $status = parent::save($data, $options);
            if ($status === false) {
                $this->rollback();
                return false;
            }
            return $status;
        }
        
        self::$cloneObj = clone $this;
        
        // 获取表名
        $tabName = $this->getTableName();
        
        // 保存现在的值
        $this->solaveData = array_keys($data);
      
        // 获取主键
        $pk = $this->getPk();
        
        // 判断主键
        $id = is_array($pk) ? array_shift($pk) : $pk;
        
        // 日志表 保存的操作表的主键
        self::$logPrimaryKey = $id;
      
        // 组装插入日志表数据
        $param = $this->addLogFlag(empty($data[$id]) ? 0 : $data[$id]);
      
        // 数据操作类型
        $param['type'] = self::LOG_UPDATE;
        
        // 开始行为获取之前的值
        Hook::listen('UpdateLogStart', $param);
        
        // 更新数据
        $status = parent::save($data, $options);
        
        if ($status === false) {
            $this->rollback();
            return false;
        }
        
        // 写入日志内容
        $content = [
            'edit_colums' => $data
        ];
        
        Hook::listen('UpdateLogEnd', $content);
        
        return $status;
    }

    /**
     * 查看事务
     * @return array
     * @author 王强<2272597637@qq.com>
     */
    protected function currentTranstation()
    {
        return $this->query('SHOW ENGINE INNODB STATUS');
    }

    /**
     * 查看是否有事务
     * @return boolean [false 没有 TRUE 有]
     */
    public function isHaveTranstation()
    {
        $data = $this->currentTranstation();
        return empty($data) ? false : true;
    }

    /**
     * 获取数据表全部表注释 并缓存
     * @param string $key 表名
     * @return array|string
     */
    public function getAllTableNotes($key = null)
    {
        $notes = S('Table_NOTES');
        
        if (! empty($notes)) {
            return empty($key) ? $notes : $notes[$key];
        }
        
        $data = $this->query('SELECT TABLE_COMMENT,TABLE_NAME FROM INFORMATION_SCHEMA.TABLES  WHERE TABLE_SCHEMA = "' . C('DB_NAME') . '"');
        
        if (empty($data)) {
            return null;
        }
        $notes = array();
        foreach ($data as $name => $value) {
            $notes[$value['table_name']] = $value['table_comment'];
        }
        
        S('Table_NOTES', $notes, 800);
        
        return empty($key) ? $notes : $notes[$key];
    }

    /**
     * 是否开启事务
     * @param bool $isOpen  是否开启
     */
    public function setIsOpenTranstion($isOpen)
    {
        if (! is_bool($isOpen)) {
            throw new \Exception('已经开启 防御状态， 将看到意想不到的页面');
        }
        $this->isOpenTranstion = $isOpen;
    }

    /**
     * 开启事务添加
     */
    public function addByOpenTranstion(array $post)
    {
        if (empty($post) || ! is_array($post)) {
            return false;
        }
        
        $this->startTrans();
        
        return $this->add($post);
    }

    /**
     * 添加包邮地区
     */
    public function addArea(array $post, $id)
    {
        if (! $this->isEmpty($post[static::$mailArea_d]) || ($id = intval($id)) === 0) {
            $this->rollback();
            return false;
        }
        
        $idString = null;
        
        $area = array();
        foreach ($post[static::$mailArea_d] as $key => $value) {
            $area[$value][static::$mailArea_d] = $value;
            $area[$value][static::$freightId_d] = $id;
        }
        
        $status = $this->where(static::$freightId_d . ' = ' . $id)->delete();
        // showData($status);
        // showData($this->getLastSql());
        if ($status === false) {
            $this->rollback();
        }
        sort($area);
        // showData($area);
        $status = $this->addAll($area);
        
        if (empty($status)) {
            $this->rollback();
            return false;
        }
        $commit = $this->commit();
        // showData($commit, 1);
        return $status;
    }

    /**
     * 获取模板数据
     */
    public function getTemplateDataByMode(array $data, $split, $field)
    {
        if (! $this->isEmpty($data) || ! is_string($split) || ! is_string($field)) {
            return array();
        }
        $idString = Tool::characterJoin($data, $split);
        
        if (empty($idString)) {
            return $data;
        }
        $temp = $this->where(static::$id_d . ' in (' . $idString . ')')->getField($field);
        
        if (empty($temp)) {
            return $data;
        }
        foreach ($data as $key => & $value) {
            if (array_key_exists($value[$split], $temp)) {
                $value[$split] = $temp[$value[$split]];
            }
        }
        return $data;
    }

    /**
     * 获取发票相关信息
     *
     * @param string $key
     *            缓存键
     * @return array
     */
    public function getOpenInvoice($key)
    {
        $data = S($key);
        
        if (empty($data)) {
            
            $data = $this->field([
                static::$updateTime_d,
                static::$createTime_d
            ], true)
                ->where(static::$status_d . ' = 1')
                ->select();
            
            if (empty($data)) {
                return array();
            }
            S($key, $data, 10);
        }
        
        return $data;
    }

    /**
     * 根据字段显示添加编辑页面
     */
    public function showColumInHTML(array $colum)
    {
        if (! $this->isEmpty($colum)) {
            $this->error = '数据库没有字段';
            return array();
        }
        
        showData($colum, 1);
    }

    /**
     * 事务添加
     */
    public function addTranstaion(array $post)
    {
        if (! $this->isEmpty($post)) {
            return false;
        }
        
        $this->startTrans();
        
        $status = $this->add($post);
        
        if (! $this->traceStation($status)) {
            return false;
        }
        
        if ($this->isCommit) {
            $this->commit();
        }
        
        return $status;
    }

    /**
     * 事务消息
     */
    public function traceStation($status, $message = '更新失败')
    {
        if ($status === false) {
            $this->rollback();
            $this->error = $message;
            return false;
        }
        return true;
    }

    /**
     * 获取表 字段信息
     */
    public function getColum()
    {
        $table = $this->getTableName();
        if (! empty(self::$colums[$table])) {
            return self::$colums[$table];
        }
        
        $filed = 'COLUMN_NAME, DATA_TYPE, COLUMN_COMMENT';
        Event::listen('colum_info', $filed); // 扩展事件
        
        self::$colums[$table] = $this->query('select ' . $filed . ' from information_schema.`COLUMNS` where TABLE_SCHEMA="' . C('DB_NAME') . '"  and TABLE_NAME="' . $table . '"');
        
        return self::$colums[$table];
    }

    /**
     * 修改时 判断 改名称 是否与其他重复
     * 
     * @param string $title
     *            要修改的字段
     * @param string $key
     *            字段名
     */
    public function editIsOtherExit($key, $title)
    {
        $dbField = $this->getDbFields();
        
        if (! in_array($key, $dbField, true)) {
            return false;
        }
        $count = $this->where($key . ' = "%s"', $title)->count();
        
        return $count >= 2 ? true : false;
    }

    /**
     * 获取设置搜索 条件
     */
    public function getSearch(array $colum)
    {
        if (! $this->isEmpty($colum)) {
            $this->error = '空数据';
            return array();
        }
        // 设置要查询的注释
        $this->setComment($colum);
        // 获取字段注释
        $comment = $this->getComment();
        return $comment;
    }

    /**
     * 重组字段信息
     */
    public function buildColumArray(array $hidden)
    {
        if (! $this->isEmpty($hidden)) {
            return array();
        }
        
        $colum = $this->getColum();
        
        if (! $this->isEmpty($colum)) {
            return array();
        }
        $parseArray = array();
        
        foreach ($colum as $key => &$value) {
            if (in_array($value['column_name'], $hidden, true) || $value['data_type'] === 'tinyint') {
                unset($colum[$key]);
            }
            if (false !== ($start = mb_strpos($value['column_comment'], '【'))) {
                $start = mb_strpos($value['column_comment'], '【');
                $value['column_comment'] = mb_substr($value['column_comment'], 0, $start);
            }
        }
        return $colum;
    }

    /**
     * 获取统计数据
     */
    public function getAnalysis(array $data, $field)
    {
        if (! $this->isEmpty($data) || ! in_array($field, $this->getDbFields(), true)) {
            return array();
        }
        return $this->where(static::$id_d . ' in (' . implode(',', array_values($data)) . ')')->getField(static::$id_d . ',' . $field);
    }

    /**
     * 获取搜索条件
     * @param array $post
     * @param str $searchKey
     * @return string[][]|string[][]|mixed[][]
     */
    public function getSearchByData(array $post, $searchKey)
    {
        if (! $this->isEmpty($post)) {
            return array();
        }
        $userWhere = $this->buildSearch($post, true);
        
        if (empty($userWhere)) {
            return array();
        }
        
        $userIdArray = $this->getAttribute([
            'field' => [
                static::$id_d
            ],
            'where' => $userWhere
        ]);
        
        if (empty($userIdArray)) {
            return [
                $searchKey => [
                    'in',
                    '0'
                ]
            ];
        }
        
        $idString = (new parseString(null))->characterJoin($userIdArray, static::$id_d);
        
        return [
            $searchKey => [
                'in',
                str_replace('"', null, $idString)
            ]
        ];
    }

    /**
     * 重写 构造方法
     */
    public function __construct($name = '', $tablePrefix = '', $connection = '')
    {
        parent::__construct($name, $tablePrefix, $connection);
        
        // 实现自动添加代码[静态属性]
        $this->autoAddProp($this);
        // 数据字段赋值 【用父类 实例化子类】$this 代指 子类的实例
        $this->setDbFileds($this);
    }

    /**
     * 获取当天 操作数据量
     */
    public function getTodayDataNumber ()
    {
        $today = date('Y-m-d', time());
    
        $start = $today.' 00:00:00';
    
        $end  = $today.' 23:59:59';
    
        $count = $this->where( static::$createTime_d.self::BETWEEN.' UNIX_TIMESTAMP("'.$start.'") and UNIX_TIMESTAMP("'.$end.'")')->count();
    
        return $count;
    }
    
    /**
     * @return the $insertId
     */
    public static function getInsertId()
    {
        return self::$insertId;
    }
}