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

namespace Common\Tool\Extend;

use Common\Tool\Tool;
use Think\Exception;

/**
 * 正则验证
 * @author 王强
 * @version 1.0.1
 */
class ParttenTool extends Tool 
{
    protected $parrten = array(
        'mobile' => '/^((13[0-9])|(15[^4])|(18[0-9])|(17[0-8])|(147,145))\\d{8}$/',
        //'mobile' => '/^(1(([35][0-9])|(47)|[8][01236789]))\d{8}$/',
    ) ;
   
    
    public function __construct(array $options = null)
    {
        $this->parrten = empty($options) ? $this->parrten : array_merge($this->parrten, $options);
    }
    
    public function addPartten($name, $value)
    {
        $this->parrten[$name] = $value;
    }
    
    
    public function validateData($data, $key ='idCard')
    {
        if (empty($this->parrten[$key]))
        {
           throw new \Exception('没有待验证'.$key.'的正则表达式，请添加', 500, null);
        }

        return (preg_match($this->parrten[$key],$data)) ? true : false;
    }
    
    /**
     * 循环验证 
     */
    public function checkPartten (array $rule) 
    {

        $isArray = !is_array($rule) || empty($rule);
        
        if ($isArray) {
            return false;
        }
        $status = false;
        foreach ($rule as $key => $value) {
            $status = $this->validateData($value, $key);
            if (!$status) {
                echo'<pre>';print_r( $key.'--->'.$value );die;
                return false;
            }
        }
        return $status;
    }
}