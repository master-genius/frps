<?php

require ('./vendor/autoload.php');

use \core\DB;

define('ROOT_PATH', __DIR__);
define('APP_PATH', __DIR__ );
define('CONFIG_PATH', APP_PATH . '/config');
define('VIEW_PATH', APP_PATH . '/view');

$pds = DB::instance()->query("show columns from w_media");

var_dump($pds->fetchAll());


