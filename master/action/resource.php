<?php
namespace master\action;

use \errcode\errinfo;
use \master\model\rsLog;
use \uauth\admin;

class resource {

    private $model = null;

    private $pagesize = 15;

    public function __construct() {
        $this->model = new \master\model\resource;
    }

    public function add($req, $res) {
        return api_ret($res, $this->model->add());
    }

    public function update($req, $res, $rsid) {
        return api_ret($res, $this->model->update($rsid));
    }

    public function get($req, $res, $rsid) {
        return api_ret($res, $this->model->get($rsid));
    }

    public function remove($req, $res, $rsid) {
        //$real = post_data('is_real', 0);
        return api_ret($res, $this->model->remove($rsid, 0));
    }

    public function removeBatch($req, $res) {
        $id_str = post_data('idlist', '');
        /*
        $idlist = explode(' ', $id_str);
         */
        $idlist = json_decode($id_str, true);
        if (null === $idlist) {
            return api_ret($res, errinfo::defErr('the ID data is illegal : ' . $id_str));
        }
        $idlist_filter = [];
        foreach ($idlist as $id) {
            if (is_numeric($id) && $id > 0) {
                $idlist_filter[] = $id;
            }
        }

        return api_ret($res, $this->model->removeBatch($idlist_filter, 0));
    }

    public function rsList($req, $res) {
        $cond = [
            'AND'   => [
                'is_delete' => 0,
                'id[>]'     => 0,
            ],

            'LIMIT' => [
                0, $this->pagesize
            ]
        ];


        if (!admin::isRoot()) {
            $cond['AND']['admin_id'] = admin::adminId();
        }
        

        $page = get_data('page', 1);
        if (!is_numeric($page) || $page <= 0) {
            $page = 1;
        }

        if ($page > 1) {
            $cond['LIMIT'] = [($page-1)*$this->pagesize, $this->pagesize];
        }

        $tmp = get_data('kwd', '');
        if (!empty($tmp)) {
            $cond['AND']['rs_keywords[~]'] = $tmp;
        }

        $tmp = get_data('group', '');
        if (!empty($tmp) && is_numeric($tmp) && $tmp >= 0) {
            $cond['AND']['rs_group'] = $tmp;
        }

        $stats = $this->model->totalStats($this->pagesize, [
                        'AND' => $cond['AND']
                    ]);

        return api_ret($res, [
            'status'    => 0,
            'rs_list'   => $this->model->rsList($cond),
            'total'     => $stats['total'],
            'total_page'=> $stats['total_page'],
            'cur_page'  => $page
        ]);
    }
    
    /*
        生成微信图文，处理过程要涉及到检测内容涉及到的图片是否已经上传到微信服务器，
        没有上传的媒体资源要进行上传并替换掉所有的链接，然后再上传图文。
        这个过程十分复杂并且很耗时，所以设计成异步操作，此函数仅仅是把请求放到一个
        处理此任务的消息队列中，并立即返回结果。
    */
    public function makeWeixinNews($req, $res, $rsid) {
        $id = post_data('rsid');
        if (is_numeric($rsid) || $id !== $rsid) {
            return api_ret($res, $this->model->makeWeixinNews($rsid));
        } else {
            $idlist = json_decode($rsid, true);
            if (false === $idlist) {
                return api_ret($res,
                    errinfo::defErr('Bad data : failed to decode')
                );
            }
            return api_ret($res, $this->model->makeWeixinNews($rsid));
        }

    }


    public function lastLogStat($req, $res, $rsid, $log_type) {
        return api_ret($res, (new rsLog)->getLastStat($rsid, $log_type));
    }

}

