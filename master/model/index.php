<?php
namespace master\action;
use \core\view;

class index {

    public function inner($req, $res) {
        (new view)->render('master/index.html');
    }

    public function rsAdd($req, $res) {
        $vars = [
            'is_edit' => 0,
            'rs_id' => 0
        ];
        (new view)->render('master/rs_edit.html', $vars);
    }

    public function rsEdit($req, $res, $rsid) {
        $vars = [
            'is_edit' => 1,
            'rs_id' => $rsid
        ];
        (new view)->render('master/rs_edit.html', $vars);
    }

    public function rsList($req, $res) {

    }

    public function tagList($req, $res) {

    }

    public function tagAdd($req, $res) {

    }

    public function tagEdit($req, $res) {

    }

    public function group($req, $res) {
        (new view)->render('master/group.html');
    }

    public function groupList($req, $res) {

    }

    public function groupAdd($req, $res) {

    }

    public function groupEdit($req, $res) {

    }



}
