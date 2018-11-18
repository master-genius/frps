<?php

namespace weixin;

class wxJSTicket
{
    //ticket保存路径
    private static $ticket_path = 'js_ticket.save';
    
    public static function getTicket()
    {
        //读取ticket保存文件，没有则创建文件
        $ticket = '';
        if (!file_exists(self::$ticket_path)) {
            file_put_contents(self::$ticket_path,'');
        } else {
            $ticket = file_get_contents(self::$ticket_path);
        }
        
        //如果文件数据为空则重新获取
        if (empty($ticket)){
            return self::refreshTicket();
        }
        $t = json_decode($ticket,true);
        if (empty($t)) {
            return self::refreshTicket();
        }
        
        //如果ticket过期则重新获取
        if(($t['expires_in']+$t['get_time']-500)<=time()){
            return self::refreshTicket();
        }

        return $t['ticket'];
    }
    
    public static function refreshTicket()
    {
        $token = wxToken::getToken();
        again_refresh:;
        //设置获取jsapi_ticket接口参数
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $token . '&type=jsapi';
        $ch = curl_init($url);
        if (!$ch) {
            return false;
        }
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        $ret = curl_exec($ch);
        curl_close($ch);
        
        //解析JSON格式数据为PHP关联数组
        $t = json_decode($ret,true);
        if (!empty($t) && $t['errcode']) {
            if ($t['errcode']==40001) {
                $token = wxToken::refreshToken();
                goto again_refresh;
            }
            return false;
        }
        
        //加入获取时间并保存文件
        $t['get_time'] = time();
        file_put_contents(self::$ticket_path, json_encode($t));
        return $t['ticket'];
    }
    
    public static function createSignature($url)
    {
        //获取ticket
        $ticket = self::getTicket();
        if ( empty($ticket) ) {
            return false;
        }

        //要生成随机字符串的可选字符
        $rand_str = 
        'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $str_len = strlen($rand_str)-1;

        /*
            生成签名：包括生成随机字符串，获取时间，字典排序，sha1加密

        */

        $sign_arr=[];
        //生成随机字符串
        $sign_arr['noncestr'] = '';
        for ($i=0;$i<10;$i++) {
            $sign_arr['noncestr'] .= $rand_str[mt_rand(0,$str_len)];
        }

        //时间戳
        $sign_arr['timestamp'] = time();

        $sign_arr['url'] = $url;

        //设置参数为已获取的ticket
        $sign_arr['jsapi_ticket'] = $ticket;

        //对数组按照键值排序
        ksort($sign_arr);

        //生成URL参数格式的字符串
        $sign_str = '';
        foreach ($sign_arr as $k=>$v) {
            $sign_str .= $k . '=' . $v . '&';
        }
        //去除最后的&字符
        $sign_str = rtrim($sign_str,'&');

        //sha1加密生成签名
        $signature = sha1($sign_str);

        return ['sign_arr'=>$sign_arr,'signature'=>$signature,'sign_str'=>$sign_str];
    }

}
