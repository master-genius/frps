<?php

require ('vendor/autoload.php');

define ('CONFIG_PATH' , __DIR__ . '/config');
define('MEDIA_PATH', __DIR__ . '/public/media/image/');

use \master\model\media;
use \core\DB;

$mdi = new media;

$media_info = $mdi->mediaPageInfo(['id[>]'=>0], 100);
$page = 1;
start_update:;
$media_list = $mdi->mediaList([
    'id[>]' => 0, 
    'LIMIT' => [
        ($page-1)*100, 100
    ]
]);

foreach( $media_list as $m ) {
    $tmp = MEDIA_PATH . $m['media_name'];
    if (!file_exists($tmp)) {
        DB::instance()->update('media', ['media_status' => 1], ['id' => $m['id']]);
    } else if ($m['media_size'] <= 0){
        $size = filesize($tmp);
        DB::instance()->update('media', ['media_size' => $size], ['id' => $m['id'] ]);
    }

}

if ($page < $media_info['total_page']) {
    $page += 1;
    goto start_update;
}

