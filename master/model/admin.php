<?php
namespace master\model;

use \core\DB;
use \errcode\errinfo;
use \filter\filter;

class admin {
    
    private $table = 'admin';

    private $is_root = 0;
    private $admin_id = 0;
    private $auth = null;

    function __construct() {
        $this->is_root = \uauth\admin::isRoot();
        $this->admin_id = \uauth\admin::adminId();
        $this->auth = new \uauth\admin;
    }

    public function adminInfo($cond, $fields = []) {
        if (empty($fields)) {
            $fields = [
                'username',
                'role',
                'email',
                'author_name',
                'is_root'
            ];
        }
        $a = DB::instance()->get($this->table, $fields, $cond);
        if (empty($a)) {
            return errinfo::defErr('admin not found');
        }
        return [
            'status'        => 0,
            'admin_info'    => $a
        ];
    }

    private function preCheck($adm) {
        if (filter::check('admin', $adm) === false) {
            $err = errinfo::retErr('common', 'VAL_ERR');
            $err['errinfo'] .= ';' . get_sys_error();
            return $err;
        }
        return true;
    }

    public function add($adm) {
        $r = $this->preCheck($adm);
        if ($r !== true) {
            return $r;
        }
        $adm['salt']   = $this->auth->genSalt();
        $adm['passwd'] = $this->auth->hashPasswd($adm['passwd'], $adm['salt']);

        $r = DB::instance()->insert($this->table, $adm);
        if ($r->rowCount() <= 0) {
            return errinfo::defErr('failed add admin : ' . $r->errorInfo()[2]);
        }

        return [
            'status'    => 0,
            'info'      => 'ok',
            'id'        => DB::instance()->id()
        ];
    }

    public function update($id, $adm) {
        $cond = [
            'id'    => $id
        ];

        if (!$this->is_root && $id != $this->admin_id) {
            return errinfo::defErr('Error: permission deny!');
        }

        if ($id == 0 && $this->is_root) {
            $cond['id'] = $this->admin_id;
        }

        $r = $this->preCheck($adm);
        if ($r !== true) {
            return $r;
        }

        $r = DB::instance()->update($this->table, $adm, ['id' => $id]);
        if ($r->rowCount() <= 0) {
            return errinfo::defErr('not update : ' . $r->errorInfo()[2]);
        }
        return [
            'status'    => 0,
            'info'      => 'ok'
        ];
    }

    public function remove($id) {
        if (!$this->is_root) {
            return errinfo::defErr('permission deny!');
        }
        if ($this->is_root && $id == $this->admin_id) {
            return errinfo::defErr('Error: permission deny!');
        }

        $r = DB::instance()->delete($this->table, ['id' => $id]);
        if ($r->rowCount() <= 0) {
            return errinfo::defErr($r->errorInfo()[2]);
        }

        return [
            'status'    => 0,
            'info'      => 'ok'
        ];
    }

    public function updPasswd($id, $pass) {
        $am = new \uauth\admin;
        $salt = $am->genSalt();
        $passwd = $am->hashPasswd($pass, $salt);

        $data = [
            'salt'      => $salt,
            'passwd'    => $passwd
        ];
        return $this->update($id, $data);
    }

    public function updName($id, $name) {
        return $this->update($id, ['username'   => $name]);
    }

    public function updAuthorName($id, $author) {
        $data = [
            'author_name'   => $author
        ];

        return $this->update($id, $data);
    }

    public function list($cond, $fields=[]) {
        if (empty($fields)) {
            $fields = [
                'id',
                'username',
                'author_name',
                'role',
                'is_forbid'
            ];
        }
        return DB::instance()->select($this->table, $fields, $cond);
    }

    public function adminList($cond = []) {
        if (empty($cond)) {
            $cond = [
                'AND'   => [
                    'is_root'   => 0,
                    'id[!]'     => $this->admin_id
                ]
            ];
        }

        return [
            'status'    => 0,
            'admin_list'=> $this->list($cond)
        ];
    }

}

