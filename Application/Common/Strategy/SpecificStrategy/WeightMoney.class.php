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
namespace Home\Strategy\SpecificStrategy;


use Common\Strategy\AbstractStrategy;
use Home\Model\FreightConditionModel;
use Home\Model\FreightModeModel;
use Common\TraitClass\NoticeTrait;

/**
 * 买就送代金券 类
 * @author 王强
 * @version 1.0.0
 */
class WeightMoney extends AbstractStrategy
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
        
        $this->promptPjax($_SESSION['user_goods_weight'], '商品重量错误');
       
        $data = $this->receive;
        
        $this->promptPjax($data, '运费数据错误');
        
        if ( !empty($data[FreightConditionModel::$mailArea_monery_d]) && $goodsMoney >= $data[FreightConditionModel::$mailArea_monery_d]) { //江浙沪大于1000包邮
            
            return 0;
        }
      
        $money = $this->algMoney($data);
        
        return $money;
        
    }
    
    /**
     * @param array $data
     */
    private function algMoney (array $data)
    {
      
        // 总重
        $heavy = $_SESSION['user_goods_weight'];
        
        //首重
        $fristHeavy = (float)$data[FreightModeModel::$firstWeight_d];
        
        //续重
        $continuedHeavy = (float)$data[FreightModeModel::$continuedHeavy_d];
        
        //首费
        $fristMoney = (float)$data[FreightModeModel::$fristMoney_d];
        
        //续费
        $continuedMonery = (float)$data[FreightModeModel::$continuedMoney_d];
        
        $unitWeight = ($heavy-$fristHeavy) ;
        
        $unitWeight = $unitWeight < 0 ? 0 : $unitWeight;
        
//         showData($heavy);// 12.5
        
//         showData($fristHeavy);// 1
        
//         showData($continuedHeavy); //1
        
//         showData($continuedMonery);// 6
        
//         showData($fristMoney); // 8   8+ (11.5/1)*6
        
//         showData($unitWeight, 1); // 11.5
        
        $money =  sprintf("%.2f", (($fristMoney + ($unitWeight/$continuedHeavy)*$continuedMonery) * $this->discount)/100 );
        
        $money = ceil($money);
        
        return $money;
    }
}