<?php
namespace uauth;

use \uauth\admin;

class perm {

    private $perm_table = [];

    public function __construct() {
        $this->perm_table = include(CONFIG_PATH . '/permtable.php');
    }

    public function permCheck($req) {
        $role = admin::adminRole();
        $is_root = admin::isRoot();
        if ($is_root) {
            return true;
        }
        if (!isset($this->perm_table[$role])) {
            return false;
        }

        $rp = $this->perm_table[$role];

        if ($req->isGet()) {
            if (!isset($rp['GET'])) {
                return false;
            }
            if ($rp['GET'] == '*') {
                return true;
            }
            return $this->matchUri($req->getUri()->getPath(), 
                $rp['GET']);
        } elseif($req->isPost()){
            if (!isset($rp['POST'])) {
                return false;
            }

            if ($rp['POST'] == '*') {
                return false;
            }
            return $this->matchUri($req->getUri()->getPath(),
                $rp['POST']);
        } else {
            return false;
        }
        
        return true;
    }

    public function matchUri($path, $api_list) {
        if (array_search($path, $api_list) !== false) {
            return true;
        }
        $ulen = strlen($path);
        $tlen = 0;
        foreach($api_list as $api) {
            $tlen = strlen($api);
            if (strncmp($api, $path, $tlen) == 0) {
                return true;
            }
        }
        return false;
    }    

}

