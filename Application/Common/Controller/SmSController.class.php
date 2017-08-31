<?php
namespace Common\Controller;

use Common\Controller\MessageSend;
//单例调用sms短信类
interface
MsmSport{

    public function send_msg($tel,$id);//短信接口发送方法
    public function generate_code($length);//验证码长度或组合验证码类型

}
class SmSController implements MsmSport
{
    private static $instatceof=null;
    final private function __construct(){}

    private function __clone(){}

    static public function getinstance()
    {
        if (self::$instatceof instanceof self)
        {
            return self::$instatceof;
        }

        self::$instatceof=new self();
        return self::$instatceof;
    }

    //单独抽取短信方法
    public function   send_msg($tel,$id)
{
    $config=unserialize(M('SystemConfig')->where(array('parent_key'=>'smsConfig'))->find()['config_value']);
    if($config['IS_START_CONFIG']==0)
    {
        return false;
    }else {
        //id为假是后台短信模板未配置的内容区域
        if(!$id){
            $rand_math = $this->generate_code(6);//随机数
            $template["'message_content'"]='您正进行找回密码ShopsN开源电商系统的身份验证,请填入系统进行找回密码操作！';
            $template["'message_sign'"]='shopSN开源电商';
        }else if(is_array($id))//特殊短信模板(发货短信模板)
        {
            $config_id = M('SystemConfig')->where(array('parent_key' => 'smsConfig'))->find()['id'];
            $template=unserialize(M('TemplateCategory')->where(array('id'=>$id['id'],'template_category_id'=>$config_id))->find()['category_content']);
        }
        else {
            $rand_math = $this->generate_code(6);//随机数
            $config_id = M('SystemConfig')->where(array('parent_key' => 'smsConfig'))->find()['id'];
            $template=unserialize(M('TemplateCategory')->where(array('id'=>$id,'template_category_id'=>$config_id))->find()['category_content']);
        }
        //取得配置参数 账号，秘钥，短信内容标签;
       // $rand_math = $this->generate_code(6);//随机数
        $account = $config['APP_KEY'];                        //改为实际账户名
        $password = $config['SECRET_KEY'];                        //改为实际短信发送密码
        $mobiles = $tel;                //目标手机号码，多个用半角“,”分隔

        if(is_array($id))
        {
            //发货短信内容
            //解析可用变量
            $real_name=$id['real_name'];
            $order_sn_id=$id['order_sn_id'];
            $time=date('Y-m-d H:m:s',time());
            $express_name=$id['express_name'];
            $express_id=$id['express_id'];
            //正则匹配
            $message_content=$this->str_replace($template["'message_content'"]);
            $content=$message_content."【" . $template["'message_sign'"] . "】";
            //dump($content);exit;
        }else{
            //短信验证内容
            $extno = $rand_math;
            $content = "验证码" . $rand_math . "，".$template["'message_content'"]."【" . $template["'message_sign'"] . "】";
        }
        $sendtime = "";
        $result = MessageSend::send($account, $password, $mobiles, $extno, $content, $sendtime);
        //dump($result);exit;
        $xml = simplexml_load_string($result);
        return md5($rand_math);
    }

}

    //随机数长度
    public function generate_code($length) {
        return rand(pow(10,($length-1)), pow(10,$length)-1);
    }
    //正则匹配
    public function str_replace($message_content)
    {
        $match = array();
        preg_match_all('/{\$(.*?)}/', $message_content, $match);
        foreach($match[1] as $key => $value) {
            if(isset($$value)) {
                $message_content = str_replace($match[0][$key], $$value, $message_content);
            }
        }
    return $message_content;
    }
}