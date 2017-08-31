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

namespace Common\Tool;

use Think\Exception;

/**
 * 工具类
 * @author 王强
 * @version 1.0.1
 */
class Tool implements \Serializable
{
    protected  static $handler ;
    protected  static $curret;
    
    protected static $imageOption;
    
    protected $imageFilePath = '';
     
    protected $errorFile = '';
    
    /**
     * 正则匹配img src
     */
    
    protected static $partten = array(
        'imgSrc' => '/<img.*?src="(.*?)".*?>/is',//匹配imag src
    );
    

    /**
     * 检查参数列表
     * @param  array      &$post         待检查参数列表
     * @param  array      $notCheck      设置数字检测列表,和忽略字段 例子:必须 ['is_numeric'=>['age','mobile'], 'nickname']
     * @param  boolean    $isCheckNumber 是否检查数字
     * @param  array|null $validate      需要检测的参数
     * @return boolean
     */
    public static function checkPost(
        array &$post, 
        array $notCheck = array('is_numeric' => array()), 
        $isCheckNumber  = false, 
        array $validate = null)
    {
        if (empty($post) || !is_array($post)) return false;
        static $flag = 0;
        //必须存在的键适用于一维数组
        if (!empty($validate))
        {
            foreach ($validate as $key => $value)
            {   //检验建名是否是$post中的建名
                if (!array_key_exists($value, $post)) {
                    return false;
                }
            }
        }
        
        foreach ($post as $key => &$value)
        {
            if (in_array($key, $notCheck)){//屏蔽不检测的键
                $flag++;
                continue;
            }
            
            if (is_array($value))
            {
                return self::checkPost($value, $notCheck, $isCheckNumber);
            }
            else
            {
                if ($isCheckNumber === true 
                    && !is_numeric($value) 
                    && isset($notCheck['is_numeric']) 
                    && in_array($key, $notCheck['is_numeric'], true)) {
                    return false;
                }
                if (in_array($key, $notCheck)){//屏蔽不检测的键
                    $flag++;
                    continue;
                }
                if ((!in_array($key, $notCheck) && empty($value))) {
                    if ($value == 0 ) {
                        $flag++;
                    } else {
                        return false;
                    }
                } else {
                    $value = addslashes(strip_tags($value));
                    $flag++;
                }
            }
        }
        return $flag === 0 ?  false : true;
    }
    
    /**
     * 截取汉字 
     * @param string $sourcestr 要截取的汉字
     * @param int    $cutlength 截取的长度
     */
     public static function cut_str($sourcestr,$cutlength,  $isAdd = false) 
     {
        $returnstr='';
        $i=0;
        $n=0;
        $str_length=strlen($sourcestr);//字符串的字节数
    
        while (($n<$cutlength) and ($i<=$str_length))
        {
            $temp_str=substr($sourcestr,$i,1);
            $ascnum=Ord($temp_str);//得到字符串中第$i位字符的ascii码
            if ($ascnum>=224) //如果ASCII位高与224，
            {
                //根据UTF-8编码规范，将3个连续的字符计为单个字符
                $returnstr=$returnstr.substr($sourcestr,$i,3);
                $i=$i+3; //实际Byte计为3
                $n++; //字串长度计1
            }
            else if ($ascnum>=192) //如果ASCII位高与192，
            {
                //根据UTF-8编码规范，将2个连续的字符计为单个字符
                $returnstr=$returnstr.substr($sourcestr,$i,2);
                $i=$i+2; //实际Byte计为2
                $n++; //字串长度计1
            }
            else if ($ascnum>=65 && $ascnum<=90) //如果是大写字母，
            {
                $returnstr=$returnstr.substr($sourcestr,$i,1);
                $i=$i+1; //实际的Byte数仍计1个
                $n++; //但考虑整体美观，大写字母计成一个高位字符
            }
            else //其他情况下，包括小写字母和半角标点符号，
            {
                $returnstr=$returnstr.substr($sourcestr,$i,1);
                $i=$i+1; //实际的Byte数计1个
                $n=$n+0.5; //小写字母和半角标点等与半个高位字符宽...
            }
        }
        if ($str_length>$cutlength && $isAdd) {
            $returnstr = $returnstr . "...";//超过长度时在尾处加上省略号
        }
        return $returnstr;
    }
    
    /**
     * 赋默认值
     * @param array  $array     要设置的数组
     * @param array  $setKey    要设置的键
     * @param mixed  $default   默认值
     * @param string $isDiffKey 特殊的键
     * @return array
     */
    Public static function isSetDefaultValue(array &$array, array $setKey, $default = null, $isDiffKey = 'page')
    {
        if (empty($setKey))
        {
            return null;
        }
        $key = $flag = null;
        foreach ($setKey as $name => $value)
        {
            $key = !is_numeric($name) ? $name : $value;
            
            if (!array_key_exists($key, $array) && $key != $isDiffKey)
            {
                $flag = ($default === null) ? $value : $default;
                $array[$key] = $flag;
            }
            elseif (!isset($array[$key]))
            {
                $array[$key] = 1;
            }
        }
        return $array;
    }
    

    /**
     * 截取字符串无乱码
     * @param string $str 要截取字符串你
     * @param int    $len 截取长度
     * @return string;
     */
    public static  function utf8sub($str,$len) {
        if($len <= 0) {
            return '';
        }
        $length = strlen($str); //待截取的字符串字节数
        // 先取字符串的第一个字节,substr是按字节来的
        $offset = 0; // 这是截取高位字节时的偏移量
        $chars = 0; // 这是截取到的字符数
        $res = ''; // 这是截取的字符串
        while($chars < $len && $offset < $length) { //只要还没有截取到$len的长度,就继续进行
            $high = decbin(ord(substr($str,$offset,1))); // 重要突破,已经能够判断高位字节
            if(strlen($high) < 8) {
                // 截取1个字节
                $count = 1;
            } else if(substr($high,0,3) == '110') {
                // 截取2个字节
                $count = 2;
            } else if(substr($high,0,4) == '1110') {
                // 截取3个字节
                $count = 3;
            } else if(substr($high,0,5) == '11110') {
                // 截取4个字节
                $count = 4;
            } else if(substr($high,0,6) == '111110') {
                // 截取5个字节
                $count = 5;
            } else if(substr($high,0,7) == '1111110') {
                // 截取6个字节
                $count = 6;
            }
            $res .= substr($str,$offset,$count);
            $chars += 1;
            $offset += $count;
        }
        return $res;
    }
    
    public static  function isMobile(){
        $useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';
       
        $mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
        $mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');
    
        $found_mobile=self::CheckSubstrs($mobile_os_list,$useragent_commentsblock) ||
        self::CheckSubstrs($mobile_token_list,$useragent);
    
        return ($found_mobile) ? true : false;
    }
    private static function CheckSubstrs($substrs,$text)
    {
        foreach($substrs as $substr)
            if(false!==strpos($text,$substr)){
                return true;
        }
        return false;
    }
    
    
    /**
     * 判断数据是否已经序列化 
     * @param string $data 需要判断的数据
     * @return bool
     */
    public static function isSerialized( $data ) 
    {
        $data = trim( $data );
        if ( 'N;' == $data )
            return true;
        if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
            return false;
        switch ( $badions[1] ) {
            case 'a' :
            case 'O' :
            case 's' :
                if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
                    return true;
                    break;
            case 'b' :
            case 'i' :
            case 'd' :
                if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
                    return true;
                    break;
        }
        return false;
    }
    
    
    /**
     * 匹配 img src
     * @param Object $type 类库类型
     */
    public static function  partten($data, $type, $key= 'imgSrc')
    {
      
        if (empty($data))
        {
            return false;
        }
        
        $typeObj = self::$imageOption[$type];
        
        if (empty($typeObj)) {
            $typeObj = new $type($data);
            self::$imageOption[$type] = $typeObj;
        }
        
        $status = $typeObj->delPicture();
        return $status;
    }
    
    /**
     * 最后一个扩展 其余的 写在子类 【不允许在添加方法】
     * @param array $array 要处理的数组
     * @return array 二维数组
     */
    public static function parseArray(array &$array)
    {
        if (empty($array))
        {
            return array();
        }
        static $arr;
        $flag = array();
        foreach ($array as $key => $value)
        {
            is_array($value)?   self::parseArray($value) : $flag[$key]= $value;
        }
        if (!empty($flag))
        {
            $arr[] = $flag;
        }
        unset($flag);
        return $arr;
    }
    /**
     * 连接子类引擎 
     */
    public static function connect($className, $args = null) 
    {
        $classObj = 'Common\\Tool\\Extend\\'.$className;
        try {
           $args = ($className == 'ArrayParse' || $className == 'ArrayChildren') ? (array)$args : $args;
           self::$handler[$className] =  empty(self::$handler[$classObj])? new $classObj($args) : self::$handler[$className];
           self::$curret = self::$handler[$className];
           return self::$curret;
        } catch (\Exception $e) {
            die(json_encode(array(
                'code'    => 400,
                'message' => '系统发生异常',
                'data'    => null,
            )));
        }
    }
    /**
     * 静态调用子类的方法 
     */
    public static function __callstatic($methods, $args)
    {
       return  method_exists(self::$curret, $methods) ? call_user_func_array(array(self::$curret, $methods), $args) : E('该类【'.get_class(self::$curret).'】，没有该方法【'.$methods.'】');
    }
    /**
     * {@inheritDoc}
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        // TODO Auto-generated method stub
        
    }
    public static function command($serialized, $validate = 'taocan_name')
    {
        return self::unserialize($serialized, $validate);
    }
    /**
     * {@inheritDoc}
     * @see Serializable::unserialize()
     */
    public  function unserialize($serialized, $validate = 'taocan_name')
    {
        // TODO Auto-generated method stub
        if (empty($serialized) || !is_array($serialized))
        {
            return $serialized;
        }
        
        foreach ($serialized as $key => &$value)
        {
            if (!self::isSerialized($value[$validate]))
            {
                continue;
            }
            $value[$validate] = unserialize($value[$validate]);
        }
        return $serialized;
    }
    
    /**
     * @desc 替换中文
     * @param unknown $search
     * @param unknown $replace
     * @param unknown $subject
     * @return string[]|string[][]|string
     */
    public static function mb_str_replace($search, $replace, $subject) {
         
        if(is_array($subject) && !empty($subject)) {
            $ret = array();
            foreach($subject as $key => $val) {
                $ret[$key] = self::mb_str_replace($search, $replace, $val);
            }
            return $ret;
        }
    
        foreach((array) $search as $key => $s) {
            if($s == '') {
                continue;
            }
            $r = !is_array($replace) ? $replace : (array_key_exists($key, $replace) ? $replace[$key] : '');
            $pos = mb_strpos($subject, $s, 0, 'UTF-8');
            while($pos !== false) {
                $subject = mb_substr($subject, 0, $pos, 'UTF-8') . $r . mb_substr($subject, $pos + mb_strlen($s, 'UTF-8'), 65535, 'UTF-8');
                $pos = mb_strpos($subject, $s, $pos + mb_strlen($r, 'UTF-8'), 'UTF-8');
            }
        }
        return $subject;
    }
    
}