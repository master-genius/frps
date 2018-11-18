<?php

namespace weixin;

class wxBasic
{

    static private $appid = 'wx01935b533f3c3a98';
    static private $secret = 'ea7060168afdd5469139c8db0e6b07f1';
    static private $self_token = 'mytest';

    static public function getAppid()
    {
        return self::$appid;
    }

    static public function getSecret()
    {
        return self::$secret;
    }

    static public function getSelfToken()
    {
        return self::$self_token;
    }

    static public function getConfig()
    {
        return [
            'appid'=>self::$appid,
            'secret'=>self::$secret,
            'self_token'=>self::$self_token
        ];
    }

}
