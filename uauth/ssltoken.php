<?php
namespace uauth;

class ssltoken {

    private $iv = 'braveximinmaster';

    private $method = 'AES-256-CBC';

    private $time_out = 3600;

    private $key_pre = 'xm_';

    public function genToken($u, $key){
        $token = openssl_encrypt(serialize($u),$this->method,$key,0,$this->iv);
        return $token;
    }

    public function decryptToken($token, $key) {
        $u = openssl_decrypt($token, $this->method, $key, 0, $this->iv);
        return unserialize($u);
    }

}

