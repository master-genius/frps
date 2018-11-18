<?php
use \core\DB;
use \errcode\errinfo;

/*
    define system vars for resource control
*/
define('APP_PATH', __DIR__);
define('CONFIG_PATH', APP_PATH . '/config');
define('VIEW_PATH', APP_PATH . '/view');

$app = new \Slim\App;

$app->group('/api', function () use ($app) {

    $app->get('/rs', function ($req, $res){
        return (new \first\action\resource)->rsList($req, $res);
    });

    $app->get('/rs/get/{id}', function ($req, $res, $args){
        return (new \first\action\resource)->get($req, $res, $args['id']); 
    });

    $app->get('/group/list', function($req, $res) {
        return (new \first\action\resource)->groupList($req, $res);
    });

})->add(function($request, $response, $next){

    $response = $next($request, $response);
    $uri = $request->getUri();
    return $response;
});


$app->group('', function() use ($app) {

    $app->get('/', function($req, $res) {
        return (new \first\action\index)->rsindex($req, $res);
    });
    
});


$app->run();

