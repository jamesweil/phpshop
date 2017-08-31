<?php
/**
 * Created by PhpStorm.
 * User: Yisu-Administrator
 * Date: 2017/7/12
 * Time: 13:22
 */

namespace Home\Controller;
use Think\Controller;
class AppUploadController extends Controller{

    public function commentUpload(){
        $user_id=$_POST['user_id'];
        $goods_id=$_POST['goods_id'];
        $order_id=$_POST['order_id'];
        if($_FILES) {
            $filename = $_FILES['img']['name'];
            $tmpname = $_FILES['img']['tmp_name'];
            if (move_uploaded_file($tmpname, './Uploads/show/' . $filename)) {
                $path['path']='/Uploads/show/' . $filename;
                $path['create_time']=time();
                $r=M('images')->add($path);
                $show_pic=M('order_comment')->where([
                    'goods_id'=>$goods_id
                ])->getField('show_pic');
                if(!empty($show_pic)) {
                    $show['show_pic'] = $show_pic . ',' . $r;
                    M('order_comment')->where([
                        'goods_id' => $goods_id,
                        'order_id'=>$order_id,
                        'user_id'=>$user_id
                    ])->save($show);
                }else{
                    $show['show_pic'] = $r;
                    M('order_comment')->where([
                        'goods_id' => $goods_id,
                        'order_id'=>$order_id,
                        'user_id'=>$user_id
                    ])->save($show);
                }
            } else {
                $data = json_encode($_FILES);
                echo $data;
            }
        }
    }

    public function headerUpload(){
        $user_id=$_POST['user_id'];
        if($_FILES) {
            $filename = $_FILES['img']['name'];
            $tmpname = $_FILES['img']['tmp_name'];
            if (move_uploaded_file($tmpname,'./Uploads/header/' . $filename)) {
                $head_img['user_header']='/Uploads/header/' . $filename;
                $head_img['user_id']=$user_id;
                $find = M('user_header')->where(array('user_id' => $user_id))->find();
                if (!empty($find)) {//则数据库里已存在头像
                    M('user_header')->where('user_id=%s',$user_id)->save($head_img);
                } else {//数据库里不存在头像
                    M('user_header')->add($head_img);
                }
                echo json_encode('上传成功');
            } else {
                $data = json_encode($_FILES);
                echo $data;
            }
        }
    }

}