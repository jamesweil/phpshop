<?php
namespace Home\Logical\Model;

use Common\Model\BaseModel;
use Common\Model\OrderGoodsModel;
use Home\Model\IntegralUseModel;

class IntegralUseLogic
{
    //订单商品数据
    private $data;

    // 模型对象
    private $model;

    //积分金额比例
    private $payIntegral = 0;
    //用户id
    private $userId;

    public function __construct(array $data, $payIntegral,$userId)
    {
        $this->data = $data;

        $this->payIntegral = $payIntegral;

        $this->userId = $userId;

        $this->model = new IntegralUseModel();
    }

    public function addIntegral()
    {
        $data = $this->data;
        if(empty($data)){
            return false;
        }
        //增加积分

        $pay_integral = $this->payIntegral;

        $time = time();
        foreach($data as $k  => $vo){
            $integral_data[$k] = [
                'user_id' => $this->userId,
                'integral' => ceil($vo['goods_num'] * $vo['goods_price'] * $pay_integral),
                'goods_id'  => $vo['goods_id'],
                'trading_time' =>$time,
                'remarks' => '商品积分',
                'type'  => 1
            ];
        }

        $status = $this->model->addAll($integral_data);

        if ($status === false) {
            $this->model->rollback();
            return false;
        }
        return $status;
    }
}
