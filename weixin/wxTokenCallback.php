<?php

namespace weixin;

class wxTokenCallback
{
    //微信测试号的配置信息
    private static $appid='wx01935b533f3c3a98';//AppId
    private static $secret='ea7060168afdd5469139c8db0e6b07f1';//AppSecret

    //access_token 保存路径
    private static $token_path = 'wx_token.save';
    
    public static function getToken($callback='',$args=[],$mode=false)
    {
        //检查是否存在保存token的文件，存在则读取信息，否则创建一个空文件
        
        $token='';
        if (!file_exists(self::$token_path)) {
            file_put_contents(self::$token_path, '');
        } else {
            $token = file_get_contents(self::$token_path);
        }
        //读取信息后，如果信息为空则重新获取token
        if (empty($token)) {
            return self::refreshToken();
        }

        //如果解析JSON数据失败则重新获取token
        $t = json_decode($token,true);
        if (empty($t)) {
            return self::refreshToken();
        }

        //如果token过期则重新获取token
        if ( ($t['expires_in']+$t['get_time']-1800)<=time() ) {
            return self::refreshToken();
        }

        return $t['access_token'];
    }

    public static function refreshToken()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token';
        $url.='?grant_type=client_credential&appid='.self::$appid.'&secret='.self::$secret;
        $c = curl_init($url);
        if (!$c) {
            return false;
        }
        curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($c,CURLOPT_SSL_VERIFYPEER,false);
        $ret = curl_exec($c);
        curl_close($c);
        //解析获取的JSON数据判断是否成功
        $t = json_decode($ret,true);
        if (isset($t['errcode'])) {
            return false;
        }
        //成功获取token则加入获取时间参数并转换成JSON字符串保存到文件
        $t['get_time'] = time();
        file_put_contents(self::$token_path, json_encode($t));
        return $t['access_token'];
    }

}
