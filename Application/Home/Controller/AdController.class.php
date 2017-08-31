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

namespace Home\Controller;
use Think\Controller;

class AdController extends Controller{
    public function addhit(){
        $id=I('post.id');
        $rs=M('ad')->where(['id'=>$id])->setInc('hit_num',1);
        if($rs){
            $this->ajaxReturn([
                'status'=>1,
                'data'=>'',
                'msg'=>'成功'
            ]);
        }else{
            $this->ajaxReturn([
                'status'=>0,
                'data'=>'',
                'msg'=>'失败'
            ]);
        }
    }
}