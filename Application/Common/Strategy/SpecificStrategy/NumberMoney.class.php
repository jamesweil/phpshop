<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <13052079525>
// +----------------------------------------------------------------------
namespace Common\Strategy\SpecificStrategy;

use Common\Strategy\AbstractStrategy;
use Think\Exception;
use Home\Model\FreightModeModel;
use Common\TraitClass\NoticeTrait;

/**
 * 减价优惠 类
 * @author 王强
 * @version 1.0.0
 */
class NumberMoney extends AbstractStrategy
{
    use NoticeTrait;   
    public function __construct( array $receive)
    {
        $this->receive = $receive;
    }
    
    /**
     * {@inheritDoc}
     * @see \Home\Strategy\AbstractStrategy::acceptCash()
     */
    public function acceptCash()
    {
        // TODO Auto-generated method stub
        
        $goodsMoney = $_SESSION['user_goods_monery'];
        
        $data = $this->receive;
        
        if (empty($data)) {
            throw new \Exception('运费错误');
        }
        
        return $this->algMoney($data);
        
    }

    /**
     * @param array $data
     */
    private function algMoney (array $data)
    {

        $this->promptPjax($_SESSION['user_goods_number'], '商品重量错误');
         
        $data = $this->receive;
        
        $this->promptPjax($data, '运费数据错误');
        // 总件数
        $totalNumber = $_SESSION['user_goods_number'];
    
        //首件
        $fristThing = (int)$data[FreightModeModel::$firstThing_d];
    
        //续件
        $continuedThing = (int)$data[FreightModeModel::$continuedThing_d];
    
        //首费
        $fristMoney = (int)$data[FreightModeModel::$fristMoney_d];
    
        //续费
        $continuedMonery = (float)$data[FreightModeModel::$continuedMoney_d];
    
        $unitThing = ($totalNumber-$fristThing) ;
    
        $unitThing = $unitThing < 0 ? 0 : $unitThing;
    
//                 showData($totalNumber);// 12.5
    
//                 showData($fristThing);// 1
    
//                 showData($continuedThing); //1
    
//                 showData($continuedMonery);// 6
    
//                 showData($fristMoney); // 8   8+ (11.5/1)*6
    
//                 showData($unitThing, 1); // 11.5
    
        $money =  sprintf("%.2f", (($fristMoney + ($unitThing/$continuedThing)*$continuedMonery) * $this->discount)/100 );
    
        $money = ceil($money);
    
        return $money;
    }
}