<?php
namespace master\model;

use \weixin\wxMaterial;
use \weixin\UserManage;
use \errcode\errinfo;
use \core\DB;
use \filter\filter;
use \master\model\group;
use \msqserv\msq;
use \uauth\admin;

class resource {

    private $table = 'resource';

    public function __construct() {

    }

    private function errExit() {
        $err = errinfo::retErr('common', 'VAL_ERR');
        $err['errinfo'] .= ';' . get_sys_error();
        return $err;
    }

    public function adupPre() {
    
        $rs = request_data_table([
            ['post', 'rs_title', ''],
            ['post', 'rs_content', ''],
            ['post', 'rs_keywords', ''],
            ['post', 'image_list', '[]'],
            ['post', 'rs_group', 0],
            ['post', 'rs_cover', ''],
            ['post', 'description', '']
        ]);

        foreach($rs as $k=>$v) {
            //$rs[$k] = htmlentities($v);
            if ($k == 'rs_group') {
                continue;
            }
            $rs[$k] = str_replace('<script>', '', $rs[$k]);
            $rs[$k] = str_replace('<SCRIPT>', '', $rs[$k]);
            $rs[$k] = str_replace('<script', '', $rs[$k]);
            $rs[$k] = str_replace('<SCRIPT', '', $rs[$k]);
            $rs[$k] = str_replace('</script>', '', $rs[$k]);
        }

        $rs['rs_keywords'] = str_replace('，', ',', $rs['rs_keywords']);
        if (!filter::check('resource', $rs)) {
            return $this->errExit();
        }

        return $rs;
    }


    public function add() {
        
        $rs = $this->adupPre();
        if (isset($rs['errinfo'])) {
            return $rs;
        }

        $rs['add_time'] = time();
        $rs['update_time'] = time();
        $rs['admin_id'] = admin::adminId();

        $r = DB::instance()->insert('resource', $rs);

        if ($r->rowCount() == 0) {
            return errinfo::retErr('master', 'RS_SETE');
        }

        return [
            'status' => 0,
            'rs_id'  => DB::instance()->id()
        ];

    }

    public function update($rsid) {
        $rs = $this->adupPre();
        if (isset($rs['errinfo'])) {
            return $rs;
        }
        
        $id = post_data('id', 0);

        if ($rsid != $id) {
            return errinfo::retErr('master', 'RS_BADID');
        }

        $rs['update_time'] = time();
        $rs['version[+]'] = 1;
        
        $r = DB::instance()->update($this->table, $rs, ['id' => $rsid]);
        if ($r->rowCount() <= 0) {
            return errinfo::retErr('master', 'RS_SETE');
        }

        return [
            'status'    => 0,
            'info'      => 'ok'
        ];
    }

    public function get($rsid, $fields = [], $with_group=true) {
        if (empty($fields)) {
            $fields = [
                'id',
                'rs_title',
                'rs_keywords',
                'rs_group',
                'rs_content',
                'description',
                'is_delete',
                'is_article',
                'media_id',
                'media_url',
                'version',
                'wx_version',
                'image_list',
                'rs_cover'
            ];
        }

        $rs = DB::instance()->get('resource', $fields, [
            'id' => $rsid
        ]);

        if (empty($rs)) {
            return errinfo::retErr('master', 'RS_RNOTF');
        }
        if (! $with_group) {
            return [
                'status'    => 0,
                'resource'  => $rs
            ];
        }

        $group_list = (new group)->list();
        return [
            'status' => 0,
            'resource' => $rs,
            'group_list' => $group_list
        ];
    }

    public function rsList($cond, $fields=[]) {
        if (empty($fields)) {
            $fields = [
                'id',
                'rs_title',
                'rs_keywords',
                'rs_group',
                'is_article',
                'media_id',
                'media_url',
                'add_time',
                'update_time'
            ];
        }

        return DB::instance()->select($this->table, $fields, $cond);
    }

    public function remove($rsid, $real = false) {
        if ($real === false) {
            $r = DB::instance()->update('resource', [
                'is_delete' => 1
            ], [
                'id' => $rsid
            ]);

            if ($r->rowCount() <= 0) {
                return errinfo::retErr('master', 'RS_SETE');
            }
        } else {
            $r = DB::instance()->delete('resource', ['id' => $rsid]);
            if ($r->rowCount() <= 0){
                return errinfo::retErr('master', 'RS_RDEL');
            }
        }

        return [
            'status' => 0,
            'info'   => 'ok' 
        ];
    }

    public function removeBatch($idlist, $real = false) {
        $total = count($idlist);
        if ($total <= 0) {
            return errinfo::defErr('id list is empty');
        }
        if ($real) {
            $r = DB::instance()->delete($this->table, [
                'id'    => $idlist
            ]);
            if ($r->rowCount() <= 0) {
                return errinfo::defErr('failed to complete delete resource');
            }
        } else {
            $r = DB::instance()->update($this->table, [
                'is_delete' => 1
            ], [
                'id' => $idlist
            ]);

            if ($r->rowCount() <= 0) {
                return errinfo::defErr('failed to delete resource');
            }
        }

        return [
            'status'    => 0,
            'info'      => 'ok'
        ];
    }

    public function updData($d, $cond) {
        $r = DB::instance()->update($this->table, $d, $cond);
        if ($r->rowCount() <= 0) {
            return false;
        }
        return true;
    }

    public function totalStats($pagesize, $cond = ['is_delete' => 0]) {
        $total = DB::instance()->count($this->table, $cond);
        $total_page = total_page($total, $pagesize);

        return [
            'total'     => $total,
            'total_page'=> $total_page
        ];
    }
    
    /*
        生成微信图文，处理过程要涉及到检测内容涉及到的图片是否已经上传到微信服务器，
        没有上传的媒体资源要进行上传并替换掉所有的链接，然后再上传图文。
        这个过程十分复杂并且很耗时，所以设计成异步操作，此函数仅仅是把请求放到一个
        处理此任务的消息队列中，并立即返回结果。
    */
    public function makeWeixinNews($rsid) {
        $q = new msq;
        if (is_numeric($rsid)) {
            $q->send('create-weixin-news', $rsid);
        } else if (is_array($rsid)) {
            foreach ( $rsid as $d) {
                $q->send('create-weixin-news', $d);
            }
        }

        return [
            'status'    => 0,
            'info'      => 'request is accepted'
        ];
    }
    
}

