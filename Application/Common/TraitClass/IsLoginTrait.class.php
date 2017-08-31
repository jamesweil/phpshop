<?php
namespace Common\TraitClass;

trait IsLoginTrait {

    /**
     * 是否登录
     */
    private function isLogin()
    {
        if (empty($_SESSION['user_id'])) {
            $this->controllerObj->ajaxReturnData([
                'url' => U('Public/login')
            ], 0, '请登录');
        }
    }
}