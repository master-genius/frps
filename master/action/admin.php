<?php
namespace master\action;

use \errcode\errinfo;

class admin {

    private $model = false;

    public function __construct() {
        $this->model = new \master\model\admin;
    }


    public function adminInfo($req, $res) {
        $cond = [
            'id'    => \uauth\admin::adminId()
        ];

        return api_ret($res, $this->model->adminInfo($cond));
    }
    
    public function add($req, $res) {
        $adm = request_data_table([
            ['post', 'username', ''],
            ['post', 'passwd', ''],
            ['post', 'author_name', ''],
            ['post', 'role', 'rs-admin']
        ]);

        return api_ret($res, $this->model->add($adm));
    }

    public function remove($req, $res, $id) {
        return api_ret($res, $this->model->remove($id));
    }

    public function updName($req, $res, $id) {
        $name = post_data('username');
        return api_ret($res, $this->model->updName($id, $name));
    }

    public function updPasswd($req, $res, $id) {
    
    }

    public function updAuthorName($req, $res, $id) {
        
        $author = post_data('author_name');

        return api_ret($res, $this->model->updAuthorName($id, $author));
    }

    public function update($req, $res, $id) {
        $type = get_data('type');
        switch($type) {
            case 'name':
                return $this->updName($req, $res, $id);
            case 'author':
                return $this->updAuthorName($req, $res, $id);
            case 'passwd':
                return $this->updPasswd($req, $res, $id);
            default:
                return api_ret($res, errinfo::defErr('Error: request not be accepted'));
        }
    }

    public function adminList($req, $res) {
        return api_ret($res, $this->model->adminList());
    }

}

