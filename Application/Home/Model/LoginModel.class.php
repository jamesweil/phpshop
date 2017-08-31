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

namespace Home\Model;

use Alipay\AopClient;

/**
 * 登陆模块,封装各种等
 */
class LoginModel
{
    // qq
    private $qq_app_id     = '101375673';
    private $qq_app_key    = '4ad9044799da7c9d8c7854af8bafc821';
    private $qq_return_url = null;

    // weibo
    private $wb_app_key    = '751941863';
    private $wb_app_secret = 'bcf972a427842cb2d53b3aefba929233';
    private $wb_return_url = null;

    // wechat
    private $wx_app_id     = '';
    private $wx_app_secret = '';
    private $wx_return_url = null;    

    // alipay
    private $alipay_pid    = '2088521367761152';
    private $alipay_md5    = '8d8lgnajz9tl485bnheu2kl8se9ram8f';
    private $alipay_return = null;
    private $alipay_prikey = null; // 商户私钥路径

    /**
     * 微博登录
     * @param  integer $step 登陆步骤
     * @param  array   $data 
     * @return         
     */
    public function weibo($step = 1, $data = [])
    {
        if (empty($this->wb_return_url)) {
            // $url = 'http://'.$_SERVER['HTTP_HOST'].U('login4WbReturn');
            $url = 'http://ysbg.yisu.cn/index.php/home/public/login4wbreturn.html';
            $this->wb_return_url = strtolower($url);
        }

        // 获取 authorize_code
        if ($step == 1) {
            $authorize = 'https://api.weibo.com/oauth2/authorize';
            $param = [
                'client_id'    => $this->wb_app_key,
                'redirect_uri' => $this->wb_return_url,
            ];
            return $this->http($authorize, $param, 'GET');
        }

        // 获取 access_token
        if ($step == 2) {
            $code  = $data['code'];
            $token = 'https://api.weibo.com/oauth2/access_token';
            $param = [
                'client_id'     => $this->wb_app_key,
                'client_secret' => $this->wb_app_secret,
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => $this->wb_return_url
            ];
            return $this->curl($token, $param, 'POST');
        }

        // 获取用户基本信息
        if ($step == 3) {
            $show = 'https://api.weibo.com/2/users/show.json';
            $param = [
                'access_token' => $data['access_token'],
                'uid'          => $data['uid']
            ];
            return $this->curl($show, $param, 'GET');
        }

        // 取消授权
        if ($step == 4) {
            $remove = 'https://api.weibo.com/oauth2/revokeoauth2';
            return $this->curl($remove, ['access_token' => $data['access_token'], 'GET']);
        }

        return false;
    }


    /**
     * qq登陆
     * @param  integer $step 登陆步骤
     * @param  array   $data 
     */
    public function qq($step = 1, $data = [])
    {
        if (empty($this->qq_return_url)) {
            $url = 'http://'.$_SERVER['HTTP_HOST'].U('login4QQReturn');
            $this->qq_return_url = strtolower($url);
        }

        // Step1：获取Authorization Code
        if ($step == 1) {
            $authorize = 'https://graph.qq.com/oauth2.0/authorize';
            $param = [
                'response_type' => 'code',
                'client_id'     => $this->qq_app_id,
                'redirect_uri'  => $this->qq_return_url,
                'state'         => 'step-1'
            ];
            return $this->http($authorize, $param, 'GET');
        }

        // Step2：通过Authorization Code获取Access Token
        if ($step == 2) {
            $token = 'https://graph.qq.com/oauth2.0/token';
            $param = [
                'grant_type'    => 'authorization_code',
                'client_id'     => $this->qq_app_id,
                'client_secret' => $this->qq_app_key,
                'code'          => $data['code'],
                'redirect_uri'  => $this->qq_return_url,
            ];

            return $this->curl($token, $param, 'GET');
        }

        // Steo3：获取openid
        if ($step == 3) {
            $token = 'https://graph.qq.com/oauth2.0/me';
            $param = [
                'access_token' => $data['access_token'],
            ];
            return $this->curl($token, $param, 'GET');
        }

        // Step4：获取用户信息
        if ($step == 4) {
            $info = 'https://graph.qq.com/user/get_user_info';
            $param = [
                'access_token'       => $data['access_token'],
                'oauth_consumer_key' => $this->qq_app_id,
                'openid'             => $data['openid']
            ];
            return $this->curl($info, $param, 'GET');
        }
        return false;
    }


    /**
     * 微信登陆
     * @param  integer $step 登陆步骤
     * @param  array   $data 
     */
    public function wechat($step = 1, $data)
    {
        // 获取授权码
        if ($step == 1) {
            $code = 'https://open.weixin.qq.com/connect/qrconnect';
            $param = [
                'appid' => $this->wx_app_id,
                'redirect_uri' => $this->wx_return_url,
                'response_type' => 'code',
                'scope' => 'snsapi_login' 
            ];
            return $this->http($code, $param, 'GET');
        }

        // 获取access_token
        if ($step == 2) {
            $token = 'https://api.weixin.qq.com/sns/oauth2/access_token';
            $param = [
                'appid'      => $this->wx_app_id,
                'secret'     => $this->wx_app_secret,
                'code'       => $data['code'],
                'grant_type' => 'authorization_code'
            ];
            return $this->curl($token, $param, 'GET');
        }

        // 获取user info
        if ($step == 3) {
            $info = 'https://api.weixin.qq.com/sns/userinfo';
            $param = [
                'access_token' => $data['access_token'],
                'openid'       => $data['openid']
            ];
            return $this->curl($info, $param, 'GET');
        }
        return false;
    }


    /**
     * 支付宝 快捷登录
     * @param  integer $step 登录步骤
     * @param  array   $data 数据
     * @return array
     */
    public function alipay($step = 1)
    {
        if (empty($this->alipay_prikey)) {
            $url = 'http://'.$_SERVER['HTTP_HOST'].U('UserData/Alipaynotice');
            $this->alipay_return = $url;
        }

        // 请求
        if ($step == 1) {
            $config['service']        = 'alipay.auth.authorize';
            $config['partner']        = $this->alipay_pid;
            $config['target_service'] = 'user.auth.quick.login';
            $config['return_url']     = $this->alipay_return;
            $config['_input_charset'] = 'UTF-8';

            $param = $this->alipay_sign($config);
            $url   = 'https://mapi.alipay.com/gateway.do';
            return $this->http($url, $param, 'GET');
        }

        // 验证信息
        if ($step == 2) {
            $param = $_GET;
            $url   = 'https://mapi.alipay.com/gateway.do';
            return $this->alipay_valid($url, $param);
        }
    }

    /**
     * alipay签名
     * @param  array $config 配置参数
     * @return array
     */
    private function alipay_sign($config)
    {
        // 1.筛选
        unset($config['sign_type'], $config['sign']);

        // 2.排序
        ksort($config); reset($config);

        // 3.拼接,http_build_query会将参数URLencode,但是支付宝不需要URLencode
        $str = http_build_query($config);
        $str = urldecode($str);

        // 4.MD5签名
        $config['sign']      = md5($str.$this->alipay_md5);
        $config['sign_type'] = 'MD5';

        return $config;
    }

    /**
     * 验证返回参数
     * @param  string $url  验证notify_id的网关
     * @param  array  $data 返回参数
     * @return boolean
     */
    public function alipay_valid($url, $data)
    {
        // 1.筛选
        $sign      = $data['sign'];
        $sign_type = $data['sign_type'];
        unset($data['sign_type'], $data['sign']);

        // 2.排序
        ksort($data); reset($data);

        // 3.拼接,http_build_query会将参数URLencode,但是支付宝不需要URLencode
        $str = http_build_query($data);
        $str = urldecode($str);


        // 4.MD5签名验证
        if (md5($str.$this->alipay_md5) != $sign) {
            return false;
        }

        // 4.1 验证是否是支付宝发送的信息 notify_id
        $param = [
            'service'   => 'notify_verify',
            'partner'   => $this->alipay_pid,
            'notify_id' => $data['notify_id']
        ];
        $ret = $this->curl($url, $param, 'POST');
        if (empty($ret) || $ret != 'true') {
            return false;
        }

        return $data;
    }


    /**
     * 构建浏览器提交
     * @param  string $url    请求网址
     * @param  string $method GET/POST
     * @param  array  $data   传输数据
     * @return null
     */
    public function http($url, $data = [], $method = 'GET')
    {
        $html = '<form action="'.$url.'" method="'.$method.'" id="formid" >';
        foreach ($data as $key => $value) {
            $html .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
        }
        $html .= '</form><script type="text/javascript">';
        $html .= 'document.getElementById("formid").submit();</script>';
        echo $html;

        return true;
    }


    /**
     * 服务器获取信息
     * @param  string $url    请求网址
     * @param  array  $data   数据
     * @param  string $method 方法
     * @return null
     */
    public function curl($url, $data = [], $method = 'GET')
    {
        $ch     = curl_init();
        $method = strtoupper($method);

        if ($method != 'GET') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            $url .= '?'.http_build_query($data);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $output = curl_exec($ch);
        if(curl_errno($ch)) {
            echo 'error: ' . curl_error($ch);
        }
        curl_close($ch);
        return json_decode($output, true);
    }
}
