<?php
namespace master\action;

use \errcode\errinfo;

use \uauth\admin;

class media {

    private $model  = null;

    private $pagesize = 12;

    public function __construct() {
        $this->model = new \master\model\media;
    }

    public function upload($req, $res) {
        return api_ret($res, 
                    $this->model->upload( $req->getUploadedFiles() )
                );
    }

    public function mediaTotalInfo($req, $res) {
        return api_ret($res, [
            'status'    => 0,
            'stats'     => $this->model->mediaTotalInfo($this->pagesize)
        ]);
    }

    public function mediaList($req, $res){
        
        $cond = [
            'LIMIT' => [
                0, $this->pagesize
            ],

            'AND'   => [
                'is_delete' => 0
            ]
        ];

        $page = get_data('page', 1);
        if (!is_numeric($page) || $page <= 0) {
            $page = 1;
        }

        $cond['LIMIT'] = [($page-1) * $this->pagesize, $this->pagesize];

        $tag = get_data('kwd', '');
        if (!empty($tags)) {
            $cond['AND']['media_tag[~]'] = $tag;
        }

        $wx = get_data('wx', -1);
        if ($wx == 0 || $wx == 1) {
            $cond['AND']['wx_status']  = $wx;
        }

        /*
        if (! admin::isRoot()) {
            $cond['AND']['admin_id'] = admin::adminId();
        }
        */

        $list = $this->model->mediaList($cond);
        $pageinfo = $this->model->mediaPageInfo(['AND' => $cond['AND']], $this->pagesize);

        return api_ret($res, [
            'status'        => 0,
            'media_list'    => $list,
            'cur_page'      => $page,
            'total'         => $pageinfo['total'],
            'total_page'    => $pageinfo['total_page']
        ]);
    }

    public function mediaInfo($req, $res, $id) {
        $mi = $this->model->mediaInfo($id);
        return api_ret($res, $mi);
    }

    public function remove($req, $res, $id) {
        return api_ret($res, $this->model->remove($id));
    }

    public function uploadWXMedia($req, $res, $id) {

        $r = $this->model->uploadWeixin($id);
        if ($r === false) {
            return api_ret($res, 
                errinfo::defErr( sys_get_error() )
            );
        }

        return api_ret($res, [
            'status'    => 0,
            'media_id'  => $r['media_id'],
            'media_url' => $r['url']
        ]);
    }

    public function removeWXMedia($req, $res, $id) {
        return api_ret($res, $this->model->removeWXmedia($id) );
    }

}

