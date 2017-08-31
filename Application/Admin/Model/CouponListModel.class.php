<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>\n
// +----------------------------------------------------------------------

namespace Admin\Model;

use Common\Model\BaseModel;
use Org\Util\RandString;
use Common\Tool\Tool;

class CouponListModel extends BaseModel
{

    private static $obj;

	public static $id_d;

	public static $cId_d;

	public static $type_d;

	public static $userId_d;

	public static $orderId_d;

	public static $useTime_d;

	public static $code_d;

	public static $sendTime_d;

	public static $status_d;

    
    public static function getInitnation()
    {
        $name = __CLASS__;
        return static::$obj = !(static::$obj instanceof $name) ? new static() : static::$obj;
    }
    
  
    /**
     * 重写方法
     * {@inheritDoc}
     * @see \Think\Model::addAll()
     */
    public function addAll($dataList, $options = '', $replace = FALSE)
    {
        if (empty($dataList) || !$dataList = $this->create($dataList))
        {
            return false;
        }
        
        //先看看有没有
        $idString = implode(',', $dataList[static::$id_d]);        
        
        if (empty($idString)) {
            return false;
        }
        
//         $data = $this->field(static::$userId_d.','.static::$cId_d.','.static::$couponNum_d)->where(static::$userId_d . ' in ('.$idString.') and '. static::$cId_d .'= "%s"', $dataList[static::$cId_d])->select();
//         
//         if(empty($data)) {
            $arr = array();
            
            foreach ($dataList[static::$id_d] as $key => &$value) {
                $arr[$key][static::$cId_d]        = $dataList[static::$cId_d];
                $arr[$key][static::$type_d]       = $dataList[static::$type_d];
                $arr[$key][static::$userId_d]     = $value;
                $arr[$key][static::$sendTime_d]   =  time();
                $arr[$key][static::$code_d]       = RandString::randString(9, 0);
            }
            unset($dataList);
            return parent::addAll($arr, $options, $replace);
//         } else {
//             //因为没有saveAll方法 【没有找到更好的方法】
            
//             foreach ($data as $key => & $value) {
//                 $value[static::$couponNum_d] += 1;
//                 $status = parent::save($value, array('where'  => array(
//                         static::$cId_d    => $value[static::$cId_d],
//                         static::$userId_d => $value[static::$userId_d]
//                 )));
//             }
            
            
//             return $status;
//         }
    }
    
    /**
     * 线下发放 
     */
    public function addMakeCoupon(array $data)
    {
        if (empty($data['num']) || !is_array($data)) {
            return $data;
        }
        
        $num = $data['num'];
        
        $codeArray = $this->getField(static::$id_d.','.static::$code_d);
        $code      = null;
        $array = array();
        for($i = 0; $i < $num; $i++) {
            do{
                $code = Tool::getRandStr(9,0,1);//获取随机9位字符串
                $checkExist = in_array($code, $codeArray);
            }while($checkExist);
            $array[$i][static::$code_d]       = $code;
            $array[$i][static::$cId_d]        = $data['id'];
            $array[$i][static::$sendTime_d]   = time();
            $array[$i][static::$type_d]       = $data[static::$type_d];
        }
        
        return parent::addAll($array);
    }
    
    
    /**
     * 根据用户等级发放 
     */
    public function addCouponList(array $post, array $userData)
    {
       if (empty($post) || !is_array($post) || empty($userData) || !is_array($userData)) {
           return array();
       }
      
       foreach ($userData as $key => & $value) {
           $value[static::$cId_d]        = $post[static::$cId_d];
           $value[static::$type_d]       = $post[static::$type_d];
           $value[static::$sendTime_d]   =  time();
           $value[static::$code_d]       = RandString::randString(9, 0);
       }
       
       return parent::addAll($userData);
    }
    
    /**
     * 获取优惠券订单数据 
     */
    public function getUserByOrder($id)
    {
        if (($id = intval($id)) === 0) {
            return array();
        }
        
        $data = $this->getAttribute([
            'field' => [
                static::$orderId_d,
                static::$cId_d
            ],
            'where' => [
                static::$orderId_d => $id
            ]
        ]);
        return $data;
    }

    /**
     * @param $tickets 优惠劵的信息
     * @param $field  返回数组的字段信息
     * @return array
     */
    public function useCouponCount($tickets,$field){
        $useTickets=$this->where(['status'=>1])->select(); //所有的已使用的优惠劵
        $array =array();
        foreach($tickets as $k=>$v){
            foreach($useTickets as $key=>$value){
                if($v['id']==$value['c_id']){
                    $v['use_num']+=1;
                }
            }
            for($i=0;$i<count($field);$i++){
                $array[$k][$field[$i]]=$v[$field[$i]];
            }
        }
        return $array;
    }
}