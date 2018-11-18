<?php
namespace master\action;


class group {

    private $model = null;

    public function __construct() {
        $this->model = new \master\model\group;
    }

    public function add($req, $res) {
        $grp = request_data_table([
            ['post', 'group_name', ''],
        ]);
        return api_ret($res, $this->model->add($grp));
    }
    
    public function remove($req, $res, $id) {
        return api_ret($res, $this->model->remove($id));
    }

    public function update($req, $res, $id) {
        $grp = [
            'id'            => post_data('id', 0),
            'group_name'    => post_data('group_name', '')
        ];
        return api_ret($res, $this->model->update($id, $grp));
    }

    public function get($id) {
        return api_ret($res, $this->model->get($id));
    }


    public function groupList($req, $res) {
        return api_ret($res, $this->model->groupList());
    }

}

