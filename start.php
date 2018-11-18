<?php
use \core\DB;
use \errcode\errinfo;
use \uauth\admin;
use \uauth\perm;

session_start();
/*
    define system vars for resource control
*/
define('APP_PATH', __DIR__);
define('CONFIG_PATH', APP_PATH . '/config');
define('VIEW_PATH', APP_PATH . '/view');

//最大分组数量限制，只使用组进行大类的划分
define('MAX_GROUP_LIMIT', 20);

/*
    标签用于扁平化分类，不应该有太多，标签的名称体现分类，
    详细的信息应该放在keywords里面
*/
define('MAX_TAGS_LIMIT', 64);
define('MAX_RESOURCE_LIMIT', 10000);
define('MAX_IMAGE_LIMIT', 10000);
define('MAX_AUDIO_LIMIT', 2000);
define('MAX_VIDEO_LIMIT', 1000);

define('MAX_ADMIN_LIMIT', 10);


$cot = new \Slim\Container;
$cot['masterRs'] = function($cot) {
    return new \master\action\resource;
};

$cot['masterMedia'] = function($cot) {
    return new \master\action\media;
};

$cot['masterAdmin'] = function($cot) {
    return new \master\action\admin;
};

$cot['masterGroup'] = function($cot) {
    return new \master\action\group;
};


$app = new \Slim\App($cot);

$app->group('/back/login', function () use ($app) {
    $app->get('/pre', function($req, $res){
        return api_ret($res, [
            'status'    => 0,
            'info'      => 'ok'
        ]);
    });

    $app->any('/admin', function ($req, $res) {
        if ($req->isGet()) {
            return (new \master\action\index)->login();
        } else if ($req->isPost()) {
            $r = (new \uauth\admin)->auth();
            if (false === $r) {
                return api_ret($res, errinfo::defErr(get_sys_error()));
            }

            return api_ret($res, [
                'status'    => 0,
                'api_token' => $_SESSION['user']['api_token']
            ]);
        }
    });

})->add(function($request, $response, $next){
    $response = $next($request, $response);

    return $response;
});

$app->group('/back/cmd', function () use ($app) {
    $app->get('/createroot', function($req, $res){
        $u = request_data_table([
            ['get', 'username',''],
            ['get', 'passwd', '']
        ]);
        $u['is_root'] = 1;
        $u['role'] = 'root';
        
        return api_ret($res, (new \uauth\admin)->createAdmin($u));
    });

})->add(function($request, $response, $next){
    $response = $next($request, $response);

    return $response;
});
/*
    后台页面
*/
$app->group('/backend', function() use ($app){

    $app->get('/test/{id}', function($req, $res, $args) {
        return (new \first\action\index)->x($req, $res, $args['id']);
    });

    $app->get('/manage', function($req, $res) {
        return (new \master\action\index)->inner($req, $res);
    });

    $app->get('/rs/add', function ($req, $res) {
        return (new \master\action\index)->rsAdd($req, $res);
    });
    
    $app->get('/rs/list', function ($req, $res) {
        return (new \master\action\index)->rsList($req, $res);
    });

    $app->get('/rs/edit/{id}', function ($req, $res, $args) {
        return (new \master\action\index)->rsEdit($req, $res, $args['id']);
    });

    $app->get('/group', function($req, $res) {
        return (new \master\action\index)->group($req, $res);
    });
    
    $app->get('/media', function($req, $res) {
        return (new \master\action\index)->media($req, $res);
    });
    
    $app->get('/admin', function($req, $res) {
        return (new \master\action\index)->admin($req, $res);
    });


})->add(function($req, $res, $next) {
    if (! (new \uauth\admin)->authStatus() ) {
        return $res->withStatus(301)->withHeader('Location', '/back/login/admin')->write('');
    }

    if (! \uauth\admin::isRoot()) {
        $role = \uauth\admin::adminRole();
        $ptable = include(CONFIG_PATH . '/permtable.php');
        $GLOBALS['role_menu'] = $ptable[$role]['MENU'];
    }

    $res = $next($req, $res);

    return $res;
});

/*
    后台接口
*/
$app->group('/master', function () use ($app) {
    $app->get('/admin/logout', function($req, $res) {
        (new \uauth\admin)->logout();
        return api_ret($res, [
            'status'    => 0,
            'info'      => 'ok'
        ]); 
    });

    $app->get('/sys/load', function($req, $res){
        return (new \master\action\monitor)->sysload($req, $res);
    });

    $app->post('/upload/media', function($req, $res) {
        return $this->masterMedia->upload($req, $res);
    });

    $app->post('/rs/add', function ($req, $res){
        return $this->masterRs->add($req, $res);
    });

    $app->post('/rs/update/{id}', function ($req, $res, $args){
        return $this->masterRs->update($req, $res, $args['id']);
    });

    $app->get('/rs/get/{id}', function ($req, $res, $args) {
        return $this->masterRs->get($req, $res, $args['id']);
    });
    
    $app->get('/rs/list', function ($req, $res) {
        return $this->masterRs->rsList($req, $res);
    });
    
    $app->get('/rs/delete/{id}', function ($req, $res, $args) {
        return $this->masterRs->remove($req, $res, $args['id']);
    });

    $app->post('/rs/delete/all', function ($req, $res){
        return $this->masterRs->removeBatch($req, $res);
    });
    
    $app->post('/rs/makewxnews/{id}', function ($req, $res, $args) {
        return $this->masterRs->makeWeixinNews($req, $res, $args['id']);
    });

    $app->get('/group/list', function($req, $res) {
        return $this->masterGroup->groupList($req, $res);
    });

    $app->post('/group/add', function($req, $res) {
        return $this->masterGroup->add($req, $res);
    });

    $app->post('/group/update/{id}', function($req, $res, $args) {
        return $this->masterGroup->update($req, $res, $args['id']);
    });
    
    $app->get('/group/delete/{id}', function($req, $res, $args) {
        return $this->masterGroup->remove($req, $res, $args['id']);
    });
    
    $app->get('/media/list', function($req, $res) {
        return $this->masterMedia->mediaList($req, $res);
    });

    $app->get('/media/delete/{id}', function($req, $res, $args) {
        return $this->masterMedia->remove($req, $res, $args['id']);
    });

    $app->get('/media/wxupload/{id}',function($req, $res, $args) {
        return $this->masterMedia->uploadWXMedia($req, $res, $args['id']);
    });

    $app->get('/rs/lastlog/{id}/{type}', function($req, $res, $args){
        return $this->masterRs->lastLogStat(
                    $req,
                    $res,
                    $args['id'],
                    $args['type']
                );
    });

    $app->get('/admin/info', function($req, $res){
        return $this->masterAdmin->adminInfo($req, $res);
    });

    $app->get('/admin/list', function($req, $res){
        return $this->masterAdmin->adminList($req, $res);
    });

    $app->post('/admin/add', function($req, $res){
        return $this->masterAdmin->add($req, $res);
    });

    $app->post('/admin/update/{id}', function($req, $res, $args){
        return $this->masterAdmin->update($req, $res, $args['id']);
    });

    $app->post('/admin/remove/{id}', function($req, $res, $args) {
        return $this->masterAdmin->remove($req, $res, $args['id']);
    });

})->add(function($req, $res, $next){
    
    $ad = new \uauth\admin;
    $r = $ad->authStatus();

    if (!$r) {
        return api_ret($res, errinfo::retErr('master','NOT_LOGIN'));
    }

    if (!$ad->checkToken(get_data('api_token',''))) {
        return api_ret($res, errinfo::retErr('master', 'TOK_TMO'));
    }

    $res = $next($req, $res); 

    return $res;
})->add(function($req, $res, $next) {
    //permission check
    $auth = new \uauth\perm;

    $r = $auth->permCheck($req);
    if (!$r) {
        return api_ret($res, errinfo::defErr('permission deny!' . $req->getUri()->getPath()));
    }

    $res = $next($req, $res);
    return $res;
});

$app->run();

