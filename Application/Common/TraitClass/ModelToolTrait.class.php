<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>
// +----------------------------------------------------------------------

namespace Common\TraitClass;

use Think\Model;
use Common\Tool\Event;
use Common\Tool\Extend\ArrayChildren;

/**
 * 模型工具类 
 */
trait ModelToolTrait
{
    protected $fieldUpdate = 'id';
    
    //以。。。合并数组
    protected $mergeKey = null;
    
    
    protected $byNameSplit ;
    
    /**
     * @return the $fieldUpdate
     */
    public function getFieldUpdate()
    {
        return $this->fieldUpdate;
    }
    
    /**
     * @param string $fieldUpdate
     */
    public function setFieldUpdate($fieldUpdate)
    {
        $this->fieldUpdate = $fieldUpdate;
    }
    
    /**
     * 验证 数组以及是否为空
     */
    public function isEmpty (array $post)
    {
        return is_array($post) && (new \ArrayObject($post))->count();
    }
    
    /**
     * id 转换为key 
     */
    public function covertKeyById (array $data, $keyId)
    {
        if (!$this->isEmpty($data) || !is_string($keyId)) {
            return array();
        }
        
        $newValue = array();
        
        foreach ($data as $key => $value) {
            
            if (!array_key_exists($keyId, $value)) {
                continue;
            }
            $newValue[$value[$keyId]] = $value;
        }
        unset($data);
        return $newValue;
    }
    
    /**
     * 获取最下级分类
     */
    protected static function flag($data, $forKey)
    {
        $flag = 0;
        foreach ($data[$forKey] as $key => $value) {
            if(!empty($value)) {
                $flag = $value;
                continue;
            }
            unset($data[$forKey][$key]);
        }
        return $flag;
    }
    
    /**
     * 是否 还有下级分类 
     */
    protected function isHaveSon ( &$data, $id)
    {
        if (empty($id)) {
            return ;
        }
       
        $data[$id] = $this->dataClass[$id];
        
        foreach ($this->dataClass as $name => $class)
        {
             if(!empty($id) && $class[static::$fid_d] == $id)
             {  
                 $this->isHaveSon($data, $class[static::$id_d]);
                 
                 $data[$id]['hasSon'] = 1;
             }
        }            
    }
    
    /**
     * 从数组中去除字段
     * @return array
     */
    public function getSplitUnset(array $array, $split='_d')
    {
        if (empty($array))
        {
            return array();
        }
    
        foreach ($array as $key => & $value)
        {
            if (false === strpos($key, $split))
            {
                unset($array[$key]);
            }
        }
    
        return $array;
    }
    
    /**
     * @desc 筛选出不同的字段【核心】
     * @param array $array 要筛选的数据
     * @param int   $classPropNum 类中数据表字段的数量
     * @param int   $dbFieldNumber 数据表的字段数量
     * @return array
     */
    public function screenField(array $array, $classPropNum, $dbFieldNumber)
    {
        if (empty($array) || !is_array($array) || !is_int($classPropNum) || !is_int($dbFieldNumber))
        {
            return array();
        }
    
        $sub = $dbFieldNumber - $classPropNum;
    
        //开始循环的地方
        $start = $dbFieldNumber - $sub;
         
        $parseDbField = array();
        $i = 0;
        for ($i = $start; $i < $dbFieldNumber; $i++) {
            $parseDbField[$i] = $array[$i];
        }
        unset($dbFieldNumber);
    
        return $parseDbField;
    }
    
    /**
     * @desc 指定行 插入代码【核心】
     * @param <resource>$source</resource> 资源
     * @param string $addByThis 要添加得代码
     * @param int    $iLine     要添加到的行数
     * @param int    $index     为第几个字符之前，默认0
     * @return array
     */
    private  function insertContent($source, $addByThis, $iLine, $index = 0)
    {
        if (!is_file($source)) {
            return array();
        }
         
        $file_handle = fopen($source, "r");
        $i = 0;
        $arr = array();
        while (! feof($file_handle)) {
            $line = fgets($file_handle);
            ++ $i;
            if ($i == $iLine) {
                if ($index == strlen($line) - 1)
                    $arr[] = substr($line, 0, strlen($line) - 1) . $addByThis;
                    else
                        $arr[] = substr($line, 0, $index) . $addByThis . substr($line, $index);
            } else {
                $arr[] = $line;
            }
        }
        fclose($file_handle);
        return $arr;
    }
    
    /**
     * 获取某段内容的行号【核心】
     * @param string $filePath 文件路径
     * @param string $target   待查找字段
     * @param bool   $first    是否再匹配到第一个字段后退出
     * @return array
     */
    private function getLineNum($filePath, $target, $first = false)
    {
        self::isFile($filePath);
    
        $fp = fopen($filePath, "r");
        $lineNumArr = array();
        $lineNum = 0;
        $flag = 0;
        while (! feof($fp)) {
            $lineNum ++;
            $lineCont = fgets($fp);
            if (strstr($lineCont, $target)) {
                $flag = 1;
                if ($first) {
                    return $lineNum;
                } else {
                    $lineNumArr[] = $lineNum;
                }
            }
        }
        // 或者这里 抛出 找不到数据所在行  $flag  标记变量
        if (empty($lineNumArr) || $flag === 0) {
            throw new \Exception('没有找到 数据所在行');
        }
        return  $lineNumArr;
    }
    
    /**
     * 文件是否存在
     */
    private static function isFile($file)
    {
        if (!is_file($file)) {
            throw new \Exception('文件不存在');
        }
    }
    
    /**
     * @desc 重写文件【核心】
     * @param string $file 文件路径
     * @param array  $fileContent 要写入的内容；
     * @return bool;
     */
    private function rewriteFile($file, array $fileContent)
    {
        self::isFile($file);
    
        if (empty($fileContent) || !is_array($fileContent)) {
            throw new \Exception('内容不能为空，且只能是数组');
        }
        
        //清空
        file_put_contents($file, null);
        $status = false;
    
        foreach($fileContent as $value)
        {
            $status = file_put_contents($file, $value, FILE_APPEND);
        }
    
        return $status;
    }
    
    /**
     * 批量更新 组装sql语句【核心】
     * @param array $parseData 要更新的数据【已经解析好的】
     * @param array $keyArray  要修改的键
     * @param string 表名
     * @return $sql
     */
    public function buildUpdateSql( array $parseData, array $keyArray, $table)
    {
        if (empty($parseData) || !is_array($parseData) || empty($table)) {
            return array();
        }
    
        $sql = 'UPDATE '.$table.'  SET ';
        
        Event::listen('sql_update', $sql);//监听开端
        
        $flag = 0;
    
        $coulumValue = null;
    
        foreach ($keyArray as $k => $v) {
            $sql .=  '`'.$v.'`' .'= CASE '. '`'.$this->fieldUpdate.'`';
            foreach ($parseData as $a => $b)
            {
                $coulumValue = $this->isString ? '"'.$b[$flag].'"' : $b[$flag];
    
                $sql .= sprintf(" WHEN %s THEN %s \t\n ", $a, $coulumValue);
            }
            $flag++;
            $sql .='END,';
        }
    
        $sql = substr($sql, 0, -1);
        
        $where = ' WHERE `'.$this->fieldUpdate.'` in('.implode(',', array_keys($parseData)).')';
        //监听条件
        Event::listen('sql_update_where', $where);
        $sql .= $where;
    
        return $sql;
    }
    
    /**
     * 实现 类的静态属性添加【代码】【核心】
     */
    private final function autoAddProp(Model $model, $suffix = '_')
    {
        $this->throwError($model);
    
        try {
            $obj = new \ReflectionObject($model);
    
            $staticProp = $obj->getStaticProperties();
    
            $addByThisModel = array();
    
            $dbField = $model->getNotes();
    
            $this->error($dbField, $model);
    
            $filePathName = $obj->getFileName();
    
    
            //截取子类模型数据库属性字段 【因为 子类可能有其他的属性字段】
    
            $dbFileds  = $this->getSplitUnset($staticProp);
            if (!empty($dbFileds))
            {
                $dbFieldNumber = count($dbField);
                 
                $classPropNumber = count($dbFileds);
    
                //是否有新添加得字段
                $diff    = $dbFieldNumber - $classPropNumber;
                 
                if ($diff === 0) {
                    return  false; //不用添加
                } else {
                    // 由于索引 从 0开始
                    // 筛选 要添加得字段
                    $addByThisModel = $this->screenField($dbField, $classPropNumber, $dbFieldNumber);
                     
                }
            } else {
                return self::rewriteModel($filePathName, $dbField, $suffix);
            }
             
            $status = false;
            if (!empty($addByThisModel)) {
                $status = self::rewriteModel($filePathName, $addByThisModel, $suffix);
            }
            return $status;
             
        } catch (\Exception $e) {
            $e->getTrace();
        }
    }
    
    /**
     * @desc 写文件 不允许外部任何文件调用【核心】
     */
    private final static function rewriteModel($filePathName, array $addByThisModel, $suffix = '_')
    {
         
        $line = self::getLineNum($filePathName, self::$find, true);
        $classData = array();
        $startString = "\n\tpublic static \$";
        $status = false;
        $i = -2;
        $newString = $noString = null;
    
        $length = false;
        //倒序排序
        $addByThisModel = (new ArrayChildren($addByThisModel))->rsort();
         
        foreach ($addByThisModel as $key => & $value) {
    
            if (empty($value['field'])) {
                throw new \Exception('在崩溃的边缘');
            }
    
            $length = strpos($value['field'], $suffix);
            $i++;
            if ($length !== false) {
                $endString   = ucfirst(substr($value['field'], $length+1)).self::SUFFIX.";\t//".$value['comment']."\n\n";
    
                $newString = $startString.strchr($value['field'], $suffix, true);
                 
                $classData = self::insertContent($filePathName, $newString.$endString, $line + $i);
                $i--;
                $status =self::rewriteFile($filePathName, $classData);
            } else {
    
                $noString = $startString.$value['field'].self::SUFFIX.";\t//".$value['comment']."\n\n";
    
                $classData = self::insertContent($filePathName, $noString, $line + $i);
                $i--;
                $status = self::rewriteFile($filePathName, $classData);
            }
        }
    
        return $status;
    }
     
    /**
     * 为子类中的数据库属性字段赋值【核心】
     * @param Model   $model  子类模型
     * @param string  $suffix 数据表字段后缀
     * @return
     */
    private final function setDbFileds(Model $model, $suffix = '_d')
    {
        $this->throwError($model);
    
        try{
            // 反射类中的数据库属性
            $reflection         =  new \ReflectionObject($model);
            $staticProperties   =  $reflection->getStaticProperties();
    
            if (!empty($staticProperties))
            {
                //截取子类模型数据库属性字段
    
                $dbFileds  = $this->getSplitUnset($staticProperties);
    
                //获取数据库的字段
                $dbData    = $model->getDbFields();
    
                // 如果此数据表没有字段 ，那么抛出异常
                $this->error($dbData, $model);
    
                // 获取字段数量
                $flag = count($dbData);
                // 标记变量
                $i    = 0;
                foreach ($dbFileds as $key => &$value)
                {
                    // 利用了 可变变量的特性
                    $model::$$key = $dbData[$i];
    
                    $i++;
                    //如果 标记变量 大于 数据表的字段数量 就结束循环
                    if ($i > $flag-1)
                    {
                        break;
                    }
                }
            }
        } catch (\Think\Exception $e) {
            throw new \ErrorException('该模型不匹配基类模型');
        }
    }
    
    private function error($data, Model $model)
    {
        if (empty($data))
        {
            throw new \Exception('该模型【'.get_class($model).'】对应的数据表无字段');
        }
    }
    
    /**
     * 抛出异常
     * @param Model $model 基类模型
     * @return \Throwable
     */
    private function throwError(Model $model)
    {
        if (!($model instanceof Model))
        {
            throw new \Exception('模型不匹配');
        }
    }
    
    
    /**
     * 比较两次输入的密码是否相同 
     */
    public function parsePasswordSame(array $post)
    {
        if (!$this->isEmpty($post)) {
            return false;
        }
        
        $flag = null;
        $i = 0;
        foreach ($post as $value)
        {
            if ( ($i > 0 && $flag !== $value) || !$value ) {
                return false;
            }
            $flag = $value;
            $i++;
        }
        return $flag;
    }
    
    /**
     * 循环检测数据类型 
     */
    public function foreachDataTypeIsEmpty (array & $data, $dataType='intval', $numberValue = 0)
    {
        if (!$this->isEmpty($data)) {
            return false;
        }
        
        if (! function_exists($dataType)) {
            return false;
        }
        
        foreach ($data as $key => & $value) {
            if (($value= $dataType($value)) === $numberValue) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 数组是否存在重复的数据 
     * @param array $array
     * @return bool true 有 false 没有
     */
    public function isSameValueByArray (array $array)
    {
        if (count($array) !== count(array_unique($array))) {
            return true;
        }
        return false;
    }
    
    /**
     * 处理时间 搜索条件
     * @param string 时间搜索
     * @return array 
     */
    public function parseTimeWhere($timeParam)
    {
        if (empty($timeParam) || false === strpos($timeParam, ' - ')) {
            return array();
        }
        
        list($startTime, $endTime) = explode(' - ', $timeParam);
        $startTime = strtotime($startTime);
        
        $endTime   = strtotime($endTime);
        
        return ['between', [$startTime, $endTime]];
    }
    
    /**
     * 编辑时 处理图片数据
     * @param array $data
     */
    protected function parsePictureByEdit (array $data)
    {
        if (empty($data)) {
            return array();
        }
        
        $temp = [];
        
        foreach ($data as $key => $value)
        {
            if (!isset($value[$this->mergeKey])) {
                
                $temp[$value[$this->mergeKey]] = $value;
            } else {
                $temp[$value[$this->mergeKey]][] = $value;
            }
        }
        return $temp;
    }
    
    /**
     * 以某个键 值组合成 一维数组
     * @param array $array 时间搜索
     * @return array
     */
    public function parseArrayByArbitrarily(array $array)
    {
        if (empty($array) ) {
            return array();
        }
        
        $temp = [];
        
        foreach ($array as $value)
        {
            if (!isset($value[$this->byNameSplit])) {
                continue;
            }
            $temp[] = $value[$this->byNameSplit];
        }
        return $temp;
    }
    
}