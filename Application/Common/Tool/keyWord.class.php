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

/**
 * 关键词获取 
 */
class KeyWord
{
    public static function search_word_from() 
    {
        $referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
       
        if(strstr( $referer, 'baidu.com')){ //百度
            preg_match( "|baidu.+wo?r?d=([^\\&]*)|is", $referer, $tmp );
            $keyword = urldecode( $tmp[1] );
            $from = 'baidu';
        }elseif(strstr( $referer, 'google.com') or strstr( $referer, 'google.cn')){ //谷歌
            preg_match( "|google.+q=([^\\&]*)|is", $referer, $tmp );
            $keyword = urldecode( $tmp[1] );
            $from = 'google';
        }elseif(strstr( $referer, 'so.com')){ //360搜索
            preg_match( "|so.+q=([^\\&]*)|is", $referer, $tmp );
            $keyword = urldecode( $tmp[1] );
            $from = '360';
        }elseif(strstr( $referer, 'sogou.com')){ //搜狗
            preg_match( "|sogou.com.+query=([^\\&]*)|is", $referer, $tmp );
            $keyword = urldecode( $tmp[1] );
            $from = 'sogou';
        }elseif(strstr( $referer, 'soso.com')){ //搜搜
            preg_match( "|soso.com.+w=([^\\&]*)|is", $referer, $tmp );
            $keyword = urldecode( $tmp[1] );
            $from = 'soso';
        }else {
            $keyword ='';
            $from = '';
        }
        return array('keyword'=>$keyword,'from'=>$from);
    }
}