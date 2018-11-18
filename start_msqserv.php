<?php
require ('./vendor/autoload.php');
use \msqserv\msq;

define('ROOT_PATH', __DIR__);
define('APP_PATH', __DIR__ . '');
define('CONFIG_PATH', APP_PATH . '/config');
define('VIEW_PATH', APP_PATH . '/view');

define('UPLOAD_PATH', ROOT_PATH . '/public/media');
define('IMAGE_PATH', UPLOAD_PATH . '/image/');
define('AUDIO_PATH', UPLOAD_PATH . '/audio/');
define('VIDEO_PATH', UPLOAD_PATH . '/video/');
define('OTHERS_PATH', UPLOAD_PATH . '/others/');

$pid = pcntl_fork();

if ($pid < 0) {
    exit('Error: failed fo fork');
}

if ($pid > 0) {
    exit(0);
}

(new msq)->serv();

