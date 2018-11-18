<?php
namespace first\action;

//use \master\model\resource;
use \errcode\errinfo;
use \master\model\group;

class resource {

    private $model = false;
    private $pagesize = 12;

    public function __construct() {
        $this->model = new \master\model\resource;
    }

    public function get($req, $res, $rsid) {
        if (!is_numeric($rsid) || $rsid <= 0) {
            return api_ret($res, errinfo::defErr('Bad data : illegal resource id'));
        }

        $fields = [
            'id',
            'rs_title',
            'rs_content',
            'rs_cover',
            'rs_content',
            'add_time',
            'update_time',
            'description'
        ];
        return api_ret($res, $this->model->get($rsid, $fields, false));
    }

    public function rsList($req, $res) {
        $fields = [
            'id',
            'rs_title',
            'add_time',
            'update_time',
            'rs_cover',
            'description'
        ];

        $cond = [
            'AND'   => [
                'id[>]'     => 0,
                'is_delete' => 0,
            ],

            'LIMIT'  => [
                0, $this->pagesize
            ]
        ];
        $page = get_data('page', 1);
        if (!is_numeric($page) || $page < 1) {
            $page = 1;
        }
        if ($page > 1) {
            $cond['LIMIT'][0] = ($page - 1) * $this->pagesize;
        }

        $group = get_data('group', '');
        if (!empty($group) && is_numeric($group) && $group >= 0) {
            $cond['AND']['rs_group'] = $group;
        }

        $kwd = get_data('kwd', '');
        if (!empty($kwd)) {
            $cond['AND'] = [
                'OR' => [
                    'rs_keywords[~]'    => $kwd,
                    'description[~]'    => $kwd,
                    'rs_title[~]'       => $kwd
                ]
            ];
        }

        /*
            暂时不支持根据搜索关键字和description字段匹配进行查询，
            但是会把description字段返回至，这个数据可以让前端进行
            部分结果优化处理。
         */
        $ti = $this->model->totalStats($this->pagesize, ['AND' => $cond['AND']]);
        
        $rs_list = $this->model->rsList($cond, $fields);
        $group_list = (new group)->list();
        return api_ret($res, [
            'status'    => 0,
            'rs_list'   => $rs_list,
            'group_list'=> $group_list,
            'cur_page'  => $page,
            'total'     => $ti['total'],
            'total_page'=> $ti['total_page']
        ]);
    }

    public function groupList($req, $res) {
        return api_ret($res, (new group)->groupList());
    }

    public function getSpider($req, $res) {

    }

    public function spiderList($req, $res) {
        
    }

}

