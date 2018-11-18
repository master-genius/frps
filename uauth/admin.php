<?php
namespace uauth;

use \core\DB;
use \errcode\errinfo;
use \uauth\ssltoken;
use \filter\filter;

class admin {

    private $max_failed = 10;

    private $table = 'admin';

    private $token_time = 3600;

    private $failed_locked_time = 600;

    public function hashPasswd($pass, $salt) {
        return hash('sha256', md5($pass . $salt));
    }

    public function authStatus() {
        if (isset($_SESSION['user']['is_login'])
            || $_SESSION['user']['is_login']
        ) {
            return true;
        }
        return false;
    }

    public function auth() {
        $u = request_data_table([
            ['post', 'username', ''],
            ['post', 'passwd', '']
        ]);

        if (empty($u['username']) || empty($u['passwd'])) {
            set_sys_error('Bad data : can not be empty');
            return false;
        }

        $fields = [
            'id',
            'is_root',
            'is_forbid',
            'username',
            'passwd',
            'salt',
            'role'
        ];

        $usr = DB::instance()->get($this->table,$fields, [
            'username'  => $u['username']
        ]);
        if (empty($usr)) {
            set_sys_error('login failed');
            return false;
        }

        $failed_ind = 'failed_login_count_' . $u['username'];
        $failed_time = 'failed_time_' . $u['username'];

        if (!isset($_SESSION[$failed_ind])) {
            $_SESSION[$failed_ind] = 0;
        } elseif ($_SESSION[$failed_ind] > $this->max_failed) {
            if ($_SESSION[$failed_time] + $this->failed_locked_time < time() ) {
                unset($_SESSION[$failed_time]);        
            } else {
                set_sys_error('user already by locked');
                return false;
            }
        }

        //$failed_count = (int)$_SESSION[$faile_ind];

        $pass = $this->hashPasswd($u['passwd'], $usr['salt']);
        if ($pass !== $usr['passwd']) {
            $_SESSION[$failed_ind] = (int)$_SESSION[$failed_ind] + 1;
            if ($_SESSION[$failed_ind] >= $this->max_failed) {
                $_SESSION[$failed_time] = time();
            }
            set_sys_error('login failed : ' . $_SESSION[$failed_ind]);
            return false;
        }

        
        $this->setLogin($usr);
        return $_SESSION['user']['api_token'];
    }

    private function genKey() {
        return md5(time() . mt_rand(1000,9999));
    }

    public function setLogin($u) {
        $_SESSION['user']   =   [];

        $_SESSION['user']['is_login']       = 1;
        $_SESSION['user']['username']       = $u['username'];
        $_SESSION['user']['admin_id']       = $u['id'];
        $_SESSION['user']['is_root']        = $u['is_root'];
        $_SESSION['user']['admin_role']     = $u['role'];

        $this->setToken();
    }

    static public function isRoot() {
        if ($_SESSION['user']['is_root']) {
            return true;
        }
        return false;
    }

    static public function adminInfo() {
        return $_SESSION['user'];
    }

    static public function adminId() {
        return $_SESSION['user']['admin_id'];
    }

    static public function adminRole() {
        return $_SESSION['user']['admin_role'];
    }

    public function setToken() {
        $key = $this->genKey();
        
        $token = new ssltoken;
        $_SESSION['user']['api_token']      = $token->genToken($_SESSION['user'], $key);
        $_SESSION['user']['api_token_time'] = time();
        $_SESSION['user']['tkey']           = $key;
    }

    public function checkToken($token) {
        if ($_SESSION['user']['api_token'] !== $token) {
            return false;
        }

        $token_time = $_SESSION['user']['api_token_time'];
        if ($token_time + $this->token_time >= time()) {
            $_SESSION['user']['api_token_time'] = time();
        } else {
            set_sys_error('token timeout');
            return false;
        }

        return true;
    }

    public function logout() {
        unset($_SESSION['user']);
    }

    public function genSalt() {
        $salt_str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $total = strlen($salt_str);
        $salt = '';
        for ($i=0; $i<10; $i++) {
            $salt .= $salt_str[mt_rand(0, $total-1)];
        }

        return $salt;
    }

    public function createAdmin($u) {
        $r = filter::check('admin', $u);
        if (!$r) {
            return errinfo::defErr(get_sys_error());
        }

        $u['salt'] = $this->genSalt();

        $u['passwd']    =   $this->hashPasswd($u['passwd'], $u['salt']);

        $r = DB::instance()->insert($this->table, $u);
        if (!$r) {
            return errinfo::defErr(get_sys_error());
        }

        return [
            'status'    => 0,
            'info'      => 'ok',
            'id'        => DB::instance()->id()
        ];
    }

}

