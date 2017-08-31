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
namespace Common\TraitClass;

trait SkuCheckTrait
{
    protected $skuCountBySku = [];
    
    /**
     * 更新时检测是否存在
     */
    protected function checkUpdate ()
    {
        $array = $this->skuCountBySku;
        
        $idString = null;
        
        foreach ($array as $key => $value) {
            
            
            if ($value <= 2) {
                continue;
            }
            
            $idString .= ','.$key;
        }
       
        return $this->skuStringIsNull($idString);
    }
    
    private function skuStringIsNull ($idString)
    {
        if ($idString === null) {
            return false;
        }
        $this->error = $idString;
        return true;
        
    }
    
    /**
     * 添加时检测是否存在
     */
    protected function checkAdd ()
    {
        $array = $this->skuCountBySku;
        
        $idString = null;
        
        foreach ($array as $key => $value) {
            
            
            if ($value < 2) {
                continue;
            }
            
            $idString .= ','.$key;
        }
       
        return $this->skuStringIsNull($idString);
    }
}