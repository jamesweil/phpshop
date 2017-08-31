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

trait NoticeTrait 
{

    /**
     * 提示client
     * 
     * @param array $data
     *            要检测的数据
     * @param string $checkKey
     *            要检测的键
     * @param string $message
     *            信息
     * @param bool $isValidate
     *            是否检测建
     */
    public function prompt ($data, $url = '', $checkKey = null, $message = '暂无数据，请添加', 
            $isValidate = FALSE)
    {
        if (empty($data)) {
            $this->error($message, $url);
        } elseif (is_array($data) && empty($data[$checkKey]) && $isValidate) {
            $this->error($message, $url);
        }
        return true;
    }
    
    public function promptParse ($data,  $message = '暂无数据，请添加', $url = '')
    {
        if (empty($data)) {
            $this->error($message, $url);
        } 
        return true;
    }
    
    public function isSucess ($status, $url, $message = '添加成功') {

        if (empty($status)) {
            $this->error($message);
        } else {
            $this->success($message, $url);
        }
    }
    
    /**
     * 提示client
     * 
     * @param array $data
     *            要检测的数据
     * @param string $checkKey
     *            要检测的键
     * @param string $message
     *            信息
     * @param bool $isValidate
     *            是否检测建
     */
    public function promptPjax ($data, $message = '暂无数据，请添加', $checkKey = null, 
            $isValidate = FALSE)
    {
        if (empty($data)) {
            $this->ajaxReturnData(null, 0, $message);
        } elseif (is_array($data) && empty($data[$checkKey]) && $isValidate) {
            $this->ajaxReturnData(null, 0, $message);
        }
        return true;
    }

    public function alreadyInData ($data, $message = '已存在该数据')
    {
        if (! empty($data)) {
            $this->error($message);
        }
        return true;
    }

    public function alreadyInDataPjax ($data, $message = '已存在该数据')
    {
        if (! empty($data)) {
            $this->ajaxReturnData(null, 0, $message);
        }
        return true;
    }
    
    /**
     * ajax 返回数据
     */
    public function ajaxReturnData($data, $status= 1, $message = '操作成功')
    {
        $this->ajaxReturn(array(
                'status'  => $status,
                'message' => $message,
                'data'    => $data
        ));
        die();
    }
    
    public function updateClient($insert_id, $message)
    {
        $status    = empty($insert_id) ? 0 : 1;
        $message   = empty($insert_id) ? $message.'，失败' : $message.'，成功';
        $this->ajaxReturnData($insert_id, $status, $message);
    }
    
    public function addClient($insert_id)
    {
        $status    = empty($insert_id) ? 0 : 1;
        $message   = empty($insert_id) ? '添加失败' : '添加成功';
        $this->ajaxReturnData($insert_id, $status, $message);
    }
    
    /**
     * 判断数字编号
     * @param int $id 数字编号
     */
    public function errorNotice(& $id)
    {
        if (( $id = intval($id) ) === 0) {
            $this->error('灌水机制已打开');
        }
        return true;
    }
}