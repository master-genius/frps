<?php
namespace master\model;

use \core\DB;
use \filter\filter;
use \errcode\errinfo;


class group {

    private $table = 'resource_group';

    private function errExit() {
        $err = errinfo::retErr('common', 'VAL_ERR');
        $err['errinfo'] .= ';' . get_sys_error();
        return $err;
    }

    public function add($grp) {

        $r = filter::check('group', $grp);
        if (!$r) {
            return $this->errExit($res);
        }

        $total = DB::instance()->count($this->table, ['id[>]'=>0]);
        if ($total >= MAX_GROUP_LIMIT) {
            return errinfo::defErr('Error: out of max limit');
        }
        
        $r = DB::instance()->insert($this->table, $grp);
        if ($r->rowCount() <= 0) {
            return errinfo::retErr('common', 'DB_INRT');
        }

        return [
            'status'    => 0,
            'group_id'  => DB::instance()->id()
        ];
        
    }
    
    public function remove($id) {
        $cond = [
            'id'    => $id
        ];

        $r = filter::check('group', $cond);
        if (!$r) {
            return $this->errExit();
        }

        $total = DB::instance()->count('resource', [
            'rs_group'  => $id
        ]);

        if ($total > 0) {
            return errinfo::retErr('master', 'GRP_RSUSE');
        }

        $r = DB::instance()->delete($this->table, $cond);
        if ($r->rowCount() <= 0) {
            return errinfo::retErr('common','DB_UPD');
        }

        return [
            'status'    => 0,
            'info'      => 'ok'
        ];
    }

    public function update($id, $grp) {

        $r = filter::check('group', $grp);
        if (!$r) {
            return $this->errExit();
        }
        
        unset($grp['id']);

        $r = DB::instance()->update($this->table, $grp, ['id' => $id]);

        if ($r->rowCount() <= 0) {
            return errinfo::retErr('common', 'DB_UPD');
            //return errinfo::defErr(DB::instance()->errorInfo()[2]);
        }

        return [
            'status'    => 0,
            'info'      => 'ok'
        ];
    }

    public function get($id) {
        $cond = [
            'id'    => id
        ];

        if (!filter::check('group', $cond)) {
            return $this->errExit($res);
        }

        $grp = DB::instance()->get($this->table, ['id', 'group_name'], $cond);

        if (empty($grp)) {
            return errinfo::retErr('common', 'DB_NOTFD');
        }

        return [
            'status'    => 0,
            'info'      => 'ok'
        ];

    }

    public function list() {
        $fields = ['id', 'group_name'];
        $group_list = DB::instance()->select($this->table, $fields);
        return $group_list;
    }

    public function groupList() {
        return [
            'status'        => 0,
            'group_list'    => $this->list()
        ];
    }

}

