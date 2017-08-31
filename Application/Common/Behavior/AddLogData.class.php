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

namespace Common\Behavior;

use Common\Model\BaseModel;
use Admin\Model\LogModel;
use Admin\Model\LogContentModel;

/**
 * 插入数据数据【操作日志】 
 */
class AddLogData
{
    /**
     * 日志添加 
     */
    public function insertLog(&$param)
    {  
        if (empty($param)) {
            return false;
        }
        $logModel = BaseModel::getInstance(LogModel::class);
        
        $status   = $logModel->addLogByAnyOne($param);
        
        if (empty($status)) {
            $param = false;
            return false;
        }
        
        $logContentModel = BaseModel::getInstance(LogContentModel::class);
        
        $status = $logContentModel->otherTableAddDataByThisAddLog($param);
        
        return $status;
    }
    
    /**
     * 删除数据时 添加日志 
     */
    public function deleteDataInsertLog(& $param)
    {
        if (empty($param)) {
            return false;
        }
        
        $logModel = BaseModel::getInstance(LogModel::class);
        
        $status = $logModel->addLogByAnyOne($param);
        
        if ($status === false) {
            $param = false;
            return false;
        }
        
        $logContentModel = BaseModel::getInstance(LogContentModel::class);
        
        $status = $logContentModel->otherTableAddDataByThisAddLog($param);
        
        return $status;
    }
    
//     /**
//      * 辅助 
//      */
//     public function delete($tbid, $tbname){
//         global $db;
//         //查询表注释
//         $db->query('show table status where name = "'.$tbname.'"');
//         $tb = $db->fetch();
//         //插入日志主表
//         $returnid = $db->insert(0, 2, 'tb_log', array(
//             'adminid = '.$_SESSION['admin']['id'],
//             'type = 3',
//             'tableid = '.$tbid,
//             'tablename = "'.$tbname.'"',
//             'comment = "'.$tb['Comment'].'"',
//             'dt = now()'
//         ));
//         //查询字段注释
//         $db->query('show full columns from '.$tbname);
//         $tb = $db->fetchAll();
//         foreach($tb as $v){
//             $commentArray[$v['Field']] = $v['Comment'];
//         }
//         //查询所有字段信息，插入日志从表
//         $rs = $db->select(0, 1, $tbname, '*', 'and tbid = '.$tbid);
//         $keys = array_keys($rs);
//         $values = array_values($rs);
//         for($i = 0; $i < count($keys); $i++){
//             $db->insert(0, 0, 'tb_log_content', array(
//                 'logid = '.$returnid,
//                 'tbkey = "'.$keys[$i].'"',
//                 'tbvalue = "'.$values[$i].'"',
//                 'comment = "'.$commentArray[$keys[$i]].'"'
//             ));
//         }
//     }
}