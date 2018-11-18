<?php

require '../vendor/autoload.php';

define('ROOT_PATH', __DIR__);

define('UPLOAD_PATH', ROOT_PATH . '/media');
define('IMAGE_PATH', UPLOAD_PATH . '/image/');
define('AUDIO_PATH', UPLOAD_PATH . '/audio/');
define('VIDEO_PATH', UPLOAD_PATH . '/video/');
define('OTHERS_PATH', UPLOAD_PATH . '/others/');

/*
    通用图文封面文件，图文消息要求必须有一个封面素材
*/
define('THUMB_FILE', UPLOAD_PATH . '/thumb_common.jpg');


define('MEDIA_URI_PATH', '/media');

if (isset($_GET['admin']) && $_GET['admin']=='super') {
    require '../admin_api.php';
} else {
    require '../start.php';
}

