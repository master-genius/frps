<?php
namespace master\action;
use \core\view;

class index {

    private $vars = [];

    public function __construct() {
        if (isset($GLOBALS['role_menu'])) {
            $this->vars['role_menu'] = $GLOBALS['role_menu'];
        } else {
            $this->vars['role_menu'] = true;
        }
    }

    public function inner($req, $res) {
        (new view)->render('master/index.html', $this->vars);
    }

    public function login() {
        (new view)->render('master/login.html');
    }

    public function rsAdd($req, $res) {
        (new view)->render('master/rsadd.html', $this->vars);
    }

    public function rsEdit($req, $res, $rsid) {
        $this->vars['is_edit'] = 1;
        $this->vars['rs_id'] = $rsid;
        
        (new view)->render('master/rsedit.html', $this->vars);
    }

    public function rsList($req, $res) {
        (new view)->render('master/rslist.html', $this->vars);
    }


    public function group($req, $res) {
        (new view)->render('master/group.html', $this->vars);
    }

    public function media($req, $res) {
        (new view)->render('master/media.html', $this->vars);
    }

    public function admin($req, $res) {
        (new view)->render('master/admin.html', $this->vars);
    }

}
