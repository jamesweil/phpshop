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

namespace Home\Model;
use Common\Model\BaseModel;

class UserLevelModel extends BaseModel
{
    private static $obj;

    public static $id_d;

    public static $levelName_d;

    public static $integralSmall_d;

    public static $integralBig_d;

    public static $discountRate_d;

    public static $status_d;

    public static $description_d;

    public static function getInitnation()
    {
        $class = __CLASS__;
        return  self::$obj= !(self::$obj instanceof $class) ? new self() : self::$obj;
    }
    
    /**
     * 根据积分 确定 等级 
     */
    public function getUserLevelByLevelId ($integral) 
    {
        if ( !is_numeric($integral)) {
            return false;
        }
        $level = S('levelData');
        if (empty($level)) {
            $level = $this->where(self::$integralSmall_d.' <= '.$integral.' and '.self::$integralBig_d .'>='.$integral .' and '.self::$status_d .'= 1')->find();
            S('levelData', $level, 3);
        }
        return $level;
    }
}