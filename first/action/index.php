<?php
namespace first\action;

use \core\view;


class index {

    private $view;

    public function __construct() {
        $this->view = new view;
    }
    
    public function rsindex($req, $res) {
        $this->view->render('first/index.html');
    }

    public function test($req, $res) {
        $reg = '/^[A-Za-z0-9]{3,20}@[a-zA-Z0-9]{1,16}\.[a-zA-Z]{1,8}$/';
 
        $data = [
            'sddsf',
            'sdf123@',
            '@saddffg',
            '123adhkds@sdfds',
            'dsfhiwer@sdhfk.sedfs'
        ];
        
        foreach ($data as $d) {
            if (preg_match($reg, $d)) {
                echo 'match : ',$d,'<br>';
            } else {
                echo '!match : ', $d, '<br>';
            }
        }


    }

    public function x($req, $res, $id) {
        $uri = $req->getUri();

        return api_ret($res, $uri->getPath());

    }

}

