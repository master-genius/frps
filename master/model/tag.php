<?php
namespace master\action;

use \core\DB;
use \filter\filter;
use \errcode\errinfo;

class tag {

    private $table = 'resource_tag';
    
    private function errExit($res) {
        $err = errinfo::retErr('common', 'VAL_ERR');
        $err['errinfo'] .= ';' . get_sys_error();
        return api_ret($res, $err);
    }

    public function add($req, $tag) {
        $tag = request_data_table([
            ['post', 'tag_name', ''],
            ['post', 'group_id', 0],
        ]);
        
        if (!filter::check('tag', $tag)) {
            return $this->errExit($res);
        }


        $total = DB::instance()->count($this->table);

        if ($total >= MAX_TAGS_LIMIT) {
            $err = errinfo::retErr('master', 'TAG_OUTM');
            return api_ret($res, $err);
        }

        $r = DB::instance()->insert($this->table, $tag);
        
        if ($r->rowCount() <= 0) {
            return api_ret($res, errinfo::retErr('master', 'TAG_DBFAIL'));
        }

        return api_ret($res, [
            'status'    => 0,
            'tag_id'    => DB::instance()->id()
        ]);
    }

    public function remove($req, $res, $id) {
        $tag = [
            'id'    => $id
        ];

        if (!filter::check('tag', $tag)) {
            return $this->errExit($res);
        }

        $total = DB::instance()->count('resource', [
            'rs_tag[~]' => $id
        ]);

        if ($total > 0) {
            return api_ret($res, errinfo::retErr('master', 'TAG_RSUSE'));
        }
        
        $r = DB::instance()->delete($this->table, $tag);
        if ($r->rowCount() <= 0) {
            return api_ret($res, errinfo::retErr('common', 'DB_UPD'));
        }
        
        return api_ret($res, [
            'status'    => 0,
            'info'      => 'ok'
        ]);
    }

    public function update($req, $res, $id) {
        
        $tag = request_data_table([
            ['post', 'tag_name', ''],
            ['post', 'group_id', 0]
        ]);

        if (!filter::check('tag', $tag)){
            return $this->errExit($res);
        }
        
        $r = DB::instance()->update($this->table,$tag, [
            'id'    => $id
        ]);

        if ($r->rowCount() <= 0) {
            return api_ret($res, errinfo::retErr('common', 'DB_UPD'));
        }
        return api_ret($res, [
            'status'    => 0,
            'info'      => 'ok'
        ]);
    }

    public function get($req, $res, $id) {
        $cond = [
            'id'    => $id
        ];

        if (!filter::check('tag', $cond)) {
            $this->errExit($res);
        }
        
        $fields = ['id', 'tag_name', 'group_id'];
        $tag = DB::instance()->get($this->table, $fields, $cond);
        if (empty($tag)) {
            $err = errinfo::retErr('common', 'DB_NOTFD');
            return api_ret($res, $err);
        }

        return api_ret($res, [
            'status' => 0,
            'tag'    => $tag
        ]);
    }

    public function getByName($req, $res, $tag_name) {

    }

    public function tagList($req, $res) {
        
        $fields = ['id', 'tag_name', 'group_id'];

        $tag_list = DB::instance()->select($this->table, $fields);
        
        return api_ret($res, [
            'status'    => 0,
            'tag_list'  => $tag_list
        ]);
    }

}

