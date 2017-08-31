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

namespace Common\TraitClass;

/**
 * 数据库 字段 对应 添加页面 显示
 */
trait ColumShowHTMLTrait
{

    protected static $typeData = [
        'tinyint' => 'radio',
        'varchar' => 'text',
        'char' => 'text',
        'decimal' => 'text',
        'float' => 'text',
        'int' => [
            'select',
            'text',
            'hidden',
            'checkbox'
        ],
        'text' => 'textarea'
    ];

    protected $errorMessage = null;

    protected $isMerge = true;

    protected $showHtmlType = array();

    /**
     *
     * @return the $type
     */
    public function getType()
    {
        return self::$typeData;
    }

    /**
     * 字段信息【处理并显示页面】
     */
    public function getCoulumShowHTML(array $colum)
    {
        if (empty($colum)) {
            return array();
        }
        
        foreach ($colum as $key => & $value) {
            if (! array_key_exists($value['data_type'], self::$typeData)) {
                continue;
            }
            $value['data_type'] = self::$typeData[$value['data_type']];
        }
        
        return $colum;
    }

    /**
     *
     * @param
     *            multitype:string multitype:string $type
     */
    public function setType($name, $type)
    {
        if (! $this->isMerge && isset($name)) {
            $this->errorMessage = '已存在改属性';
            return;
        }
        
        self::$typeData[$name] = $type;
    }

    public function __isset($name)
    {
        return isset(self::$typeData[$name]);
    }

    /**
     * 重新 设置类型 [字段在页面上展示的模样]
     * 
     * @param
     *            array 字段
     */
    public function setColum(array $colum)
    {
        $this->showHtmlType = $colum;
    }

    /**
     * 重新组装内容
     */
    public function buildHTML(array $content)
    {
        if (empty($content)) {
            return array();
        }
        
        $temp = array();

        
        foreach ($this->showHtmlType as $key => $value) {
            
            if (! array_key_exists($key, $content)) {
                throw new \Exception('请添加类型');
            }
            
            $temp[$key] = [
                $key => $value,
            ];
            $temp[$key] = array_merge($temp[$key], $content[$key]);
        }
        
        return $temp;
    }
}