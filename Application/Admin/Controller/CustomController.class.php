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

namespace Admin\Controller;

use Common\Controller\AuthController;
use Common\Model\BaseModel;
use Admin\Model\CustomPageModel;
use Admin\Model\CustomPageClassModel;
use Common\Tool\Tool;
use Common\TraitClass\SmsVerification;

/**
 * 自定义页面控制器 
 * @author 王强
 * @version 1.0
 */
class CustomController extends AuthController
{
    use SmsVerification;   
    public function index ()
    {
        $customPage = BaseModel::getInstance(CustomPageModel::class);
        
        //获取显示字段的注释
        
        $columNotes = $customPage->getComment();
       
        $this->assign('colum', $columNotes);
        
        $this->assign('customModel', CustomPageModel::class);
        
        $this->display();
    }
    
    /**
     * 添加自定义页面 
     */
    public function addCustomPage ()
    {
        // 获取要添加的数据
        
        $customPage = BaseModel::getInstance(CustomPageModel::class);
        
        $classCustomModel = BaseModel::getInstance(CustomPageClassModel::class);
        
        //获取option HTML
        $option = $classCustomModel->buildSelectOption();
        
        //添加页面处理
        
        $note = $customPage->parseInsrtHtml($option);
        $this->assign('custoPageModel', CustomPageModel::class);
        
        $this->assign('columNotes', $note);
        
        $this->display();
    }
    
    /**
     * 添加自定义页面 并输出文件 
     */
    public function addCustomPageData ()
    {
        Tool::checkPost($_POST, array('is_numeric' => ['group_id'],'detail'), true, ['group_id', 'name']) ? : $this->ajaxReturnData(null, 0, '参数错误');
        
        
        $classCustomePage = BaseModel::getInstance(CustomPageClassModel::class);
        
        //生成静态文件
        $groupName = $classCustomePage->getGroupNameById ($_POST['group_id']);
        
        $this->promptPjax($groupName, '数据错误');
        
        $customPage = BaseModel::getInstance(CustomPageModel::class);
        
        $_POST['group_name'] = $groupName;
        
        //获取自定义页面所在文件夹
      
        $status = $customPage->buildStaticHtml($_POST, $this->getConfig('custom_page_where'));
        
        $this->promptPjax($status, $customPage->getError());
        
        //插入静态页面数据
        $status = $customPage->add($_POST);
        
        $this->promptPjax($status, '保存失败');
        
        $this->ajaxReturnData([
            'url' => U('index')
        ]);
        
    }
}