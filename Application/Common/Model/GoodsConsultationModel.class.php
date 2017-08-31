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

namespace Common\Model;

use Think\AjaxPage;

/**
 * 商品咨询 
 */
class GoodsConsultationModel extends BaseModel
{
    private static $obj;


	public static $id_d;	//商品咨询id

	public static $goodsId_d;	//商品名称编号

	public static $addTime_d;	//咨询时间

	public static $commentType_d;	//1 商品咨询 2 支付咨询 3 配送 4 售后

	public static $content_d;	//咨询内容

	public static $parentId_d;	//回复人编号

	public static $isShow_d;	//是否显示

	public static $userId_d;	//用户名编号

	public static $ip_d;	//ip地址

	public static $who_d;	//向谁咨询，0代表向客户咨询，1代表向商城客服咨询


    public static function getInitnation()
    {
        $name = __CLASS__;
        return self::$obj = !(self::$obj instanceof $name) ? new self() : self::$obj;
    }
    
    /**
     * ajax 分页输出 
     */
    public function getDataByAjax ($limit, $ajax, array $where = null)
    {
        if ( ($limit =intval($limit) ) === 0 || !class_exists($ajax) ) {
            return array();
        }
        
        $map = array();
        
        $map[self::$parentId_d] = 0;
        
        $map = array_merge($map, $where);
        
        $data = $this->getDataByPage(array(
            'field' => $this->getDbFields(),
            'where' => $map,
        ), $limit, false, $ajax);
        return $data;
    }
    /**
     *  获取一行
     */
    public function getFindData ($id, $whereField, $method='find') 
    {
        $colums = $this->getDbFields();
        if ( ($id = intval($id)) === 0 || !method_exists($this, $method) || !in_array($whereField, $colums, true)) {
            return array();
        }
        return $this->getAttribute(array(
            'field' => [
                self::$commentType_d,
            ],
            'where' => [
                $whereField => $id
            ]
        ), true, $method);
    }
    
   
    
    /**
     * 管理员添加回复 
     */
    public function addContent (array $post) 
    {
        if (!$this->isEmpty($post)) {
            return false;
        }
        
        $post[self::$userId_d] = $_SESSION['aid'];
        
        return $this->add($post);
        
    }
    
    /**
     * 获取当前商品的咨询 
     * @param numeric $id 商品编号
     * @return array 咨询数据数组
     */
    public function getConsulation ($id, $number = 15)
    {
        if ( ($id = intval($id)) === 0) {
            return array();
        }
        
        $tableName = $this->getTableName();
        
        $count = S('count');
        
        if (empty($count)) {
            $count = $this
                ->alias('con')
                ->join($tableName.self::DBAS.' gc  ON con.'.self::$id_d.'= gc.'.self::$parentId_d)
                ->where('con.'.self::$goodsId_d .'='.$id.' and con.'.self::$isShow_d .'= 1')->count();
           
            if ($count <= 0) { 
                return array();
            }
            S('count', $count, 5);
        }
       
        
        $pageObj = new AjaxPage($count, $number);
        
        //内联自己的表
        $consulation = $this
                ->field(array(
                    'con.'.self::$goodsId_d,
                    'con.'.self::$id_d,
                    'con.'.self::$content_d,
                    'con.'.self::$addTime_d,
                    'gc.'.self::$content_d .self::DBAS.' reply_content',
                    'gc.'.self::$addTime_d.self::DBAS .' reply_time'
                ))->alias('con')
                ->join($tableName.self::DBAS.' gc  ON con.'.self::$id_d.'= gc.'.self::$parentId_d)
                ->where('con.'.self::$goodsId_d .'='.$id .' and con.'.self::$isShow_d .'= 1')
                ->limit($pageObj->firstRow.','.$pageObj->listRows)   
                ->select();
        $data = array();
        $data['data'] = $consulation;
        $data['page'] = $pageObj->show();
        return $data;
    }
    
    /**
     * 提交咨询 
     */
    public function addConsulation (array $post)
    {
        if (!$this->isEmpty($post)) {
            return false;
        }
        
        if (!empty($_SESSION['user_id'])) {
            $post[self::$userId_d] = $_SESSION['user_id'];
        }
        return $this->add($post);
    }
    
    /**
     * {@inheritDoc}
     * @see \Think\Model::_before_insert()
     */
    protected function _before_insert(& $data, $options) 
    {
        $data[self::$addTime_d] = time();
        $data[self::$isShow_d]  = 0;
        $data[self::$ip_d]      = get_client_ip();
        return $data;
    }
   
    /**
     * 删除问题及其回答 
     */
    public function deleteAllConsulationById ($id)
    {
        if ( ($id = intval($id)) === 0) {
            return false;
        }
        $tableName = $this->getTableName();
        //链表删除
        return $this->execute('DELETE  dg, gc 
                FROM 
                '.$tableName.' as  dg , '.$tableName.' as gc WHERE dg.'.self::$id_d.' = gc.'.self::$parentId_d.' 
                AND (dg.'.self::$id_d.' ='.$id.' OR gc.'.self::$parentId_d.'='.$id.')'
       );
    }
}