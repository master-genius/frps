<?php
namespace msqserv;

use \core\DB;
use \errrcode\errinfo;
use \weixin\wxCURL;
use \weixin\wxToken;
use \weixin\wxMaterial;
use \master\model\media;
use \master\model\resource;
use \master\model\rsLog;
use \master\model\mediaLog;

class msq {

    private $msq_num    = 1990;
    private $type   =   1;

    private $error_log_file   = '/tmp/wx_error.log';

    private $log_temp = [
    ];

    /*
        消息类型模板映射表，根据此表找出需要填写的参数
        用户发送的消息格式：
        [
            'type'      => 'TYPE',

            'msg_time'  => 'TIME',

            ....对应参数
        ]
    */
    private $msg_map = [

        'create-weixin-news'    => [
            'type'  => 'create-weixin-news',
            'rs_id' => 0
        ],

        'remove-media'      => [
            'type'      => 'remove-media',
            'mid'       => 0
        ],

        'remove-media-list' => [
        
        ],



    ];

    private $mqr = null;

    public function __construct($options=[]) {
        if (isset($options['msq_num'])) {
            $this->msq_num = $options['msq_num'];
        }
        if (isset($options['type'])) {
            $this->type = $options['type'];
        }
        $this->mqr = msg_get_queue($this->msq_num);
    }

    public function send($type, $data=[], $blocking=true) {
        if ( ( !isset( $this->msg_map[$type]) ) ) {
            return false;
        }
        $err = 0;
        if ($type == 'create-weixin-news') {
            $msg = $this->msg_map['create-weixin-news'];
            $msg['rs_id'] = $data;
            $r = msg_send($this->mqr, $this->type, $msg, true, $blocking, $err);
            if (!$r) {
                return false;
            }
        } else if ($type == 'remove-media') {
            $msg = $this->msg_map['remove-media'];
            $msg['mid'] = $data;
            $r = msg_send($this->mqr, $this->type, $msg, true, $blocking, $err);
            if (!$r) {
                return false;
            }
        }
        return true;
    }

    public function serv() {
        echo $this->mqr;
        $msg = [];
        $errcode = 0;
        while(true) {
            usleep(50000);
            if (false === msg_receive($this->mqr, 
                    0,
                    $this->type, 
                    1024, //msg max size
                    $msg, //store msg
                    true, //unserialize
                    $errcode
                )
            ) {
                continue;
            }
//            if ($errcode) {
//                continue;
//            }
            var_dump($msg); 
            if ($msg['type'] == 'create-weixin-news') {
                $this->makeWeixinNews($msg['rs_id']);
            } else if ($msg['type'] == 'remove-media'){
                $this->removeMedia($msg['mid']);
            }

            $msg = [];
        }
    }

    /*
        处理过程：首先获取内容并匹配出所有的media_url，
        系统在设计时创建资源是已经记录好了image_list，
        这个数据字段可以直接使用。
        然后检查每个image是否已经是上传到微信服务器的素材，
        如果已经上传则直接从数据库读取，否则先调用上传过程的接口然后再获取。
    */
    public function makeWeixinNews($rsid) {
        $rs_info = (new resource)->get($rsid);
        
        $me = new media;
        $r = null;
        $thumb_media_id = '';

        //获取不到则直接退出
        if (!isset($rs_info['resource'])) {
            $log = strftime("%Y-%m-%d %H:%M:%S") . " RS_NOTFD resource not found\n";
            file_put_contents($this->error_log_file, $log, FILE_APPEND);
            return false;
        }
        
        $rslog = new rsLog;
        $log_type = 'mkwxnews';

        $rs = $rs_info['resource'];
        if ($rs['version'] == $rs['wx_version']) {
            $rslog->addLog([
                'rs_id'     => $rsid,
                'log_time'   => time(),
                'log_type'   => $log_type,
                'rs_status' => -1,
                'rs_log'    => 'need not to create weixin news : version == wx_version'
            ]);
            return false;
        }

        $imgs_name =[];
        $tmp = '';
        $image_list = json_decode($rs['image_list'], true);
        foreach ($image_list as $v) {
            $tmp = explode('/', $v);
            $imgs_name[] = $tmp[count($tmp)-1];
        }
        /*
            如果内容没有图片则直接跳转到图文创建的过程
        */
        if (empty($imgs_name)) {
            goto start_create_news;
        }

        $imgs_info = (new media)->mediaList([
            'media_type'    => 'image',
            'media_name'    => $imgs_name
        ]);

        $warning = [];
        foreach ($imgs_info as $im) {
            if ($im['wx_status'] == 0 || $im['wx_status'] == 11) {
                $r = $me->uploadWeixin($im['id']);
                if (!$r) {
                    continue;
                }
                $im['media_id'] = $r['media_id'];
                $im['media_url'] = $r['url'];
            } elseif ($im['wx_status'] == 12) {
                continue;
            }

            $rs['rs_content'] = str_replace(
                $im['media_site_url'],
                $im['media_url'],
                $rs['rs_content']
            );
        }

        start_create_news:;
        
        if (empty($rs['rs_cover'])) {
            $rslog->addLog([
                'rs_id'     => $rsid,
                'log_type'   => $log_type,
                'log_time'   => time(),
                'rs_status' => -1,
                'rs_log'    => 'create weixin news : resource does`t not have cover'
            ]);
            return false;
        }

        $tmp = explode('/', $rs['rs_cover']);
        $cover_name = $tmp[count($tmp) - 1];

        $media_info = DB::instance()->get('media', [
                            'id','wx_status', 'media_id',
                        ],['media_name' => $cover_name]);

        if (empty($media_info)) {
            $rslog->addLog([
                'log_type'  => $log_type,
                'log_time'  => time(),
                'rs_status' => -1,
                'rs_id'     => $rsid,
                'rs_log'    => 'cover image not found in media table'
            ]);
            return false;
        }

        if ($media_info['wx_status'] != 1) {
            $r = $me->uploadWeixin($media_info['id']);
            if (!$r) {
                $rslog->addLog([
                    'rs_id'     => $rsid,
                    'log_type'  => $log_type,
                    'rs_status' => 1,
                    'log_time'  => time(),
                    'rs_log'    => 'failed to upload cover to weixin server'
                ]);
                return false;
            }
            $thumb_media_id = $r['media_id'];
        } else {
            $thumb_media_id = $media_info['media_id'];
        }

        $wx_news = [
            "articles" => [
                [
                    "title"             => $rs['rs_title'],
                    'digest'            => $rs['description'],
                    "thumb_media_id"    => $thumb_media_id,
                    "show_cover_pic"    => 1,
                    "content"           => $rs['rs_content'],
                    "content_source_url"=> ''
                ]
            ]
        ];
        $is_update = 0;
        if ($rs['is_article'] && $rs['media_id']) {
            $is_update = 1;
            $upd_news['articles'] = $wx_news['articles'][0];
            $upd_news['media_id'] = $rs['media_id'];
            $upd_news['index'] = 0;
            $r = (new wxMaterial)->setNews($upd_news);
        } else {
            $r = (new wxMaterial)->createNews($wx_news);
        }
        $ret = json_decode($r, true);
        if (!isset($ret['media_id'])) {
            $rslog->addLog([
                'rs_id'     => $rsid,
                'log_type'  => $log_type,
                'log_time'  => time(),
                'rs_status' => 1,
                'rs_log'    => $r
            ]);
            return false;
        }

        $cond = ['id' => $rs['id'] ];
        if ($is_update == 0) {
            (new resource)->updData([
                'media_id'  => $ret['media_id'],
                'wx_version' => $rs['version'],
                'is_article'    => 1,
            ], $cond); 
        } else {
            (new resource)->updData([
                'wx_version'    => $rs['version']
            ], $cond);
        }

        $rslog->addLog([
            'rs_id'     => $rsid,
            'log_type'   => $log_type,
            'rs_status' => 0,
            'rs_log'    => ($is_update?'update':'create') . ' weixin news : ' . $ret['media_id'],
            'log_time'   => time()
        ]);

        return true;
    }

    public function removeMedia($id) {

        $mlog = new mediaLog;
        $log_type = 'rm-media';

        $m = new media;
        $mi = $m->getMediaInfo($id);

        if (empty($mi)) {    
            return false;
        }

        $unlink_try  = 3;

        $cond = [
            'OR'    => [
                'rs_cover'   => $mi['media_site_url'],
                'image_list[~]' => $mi['media_name']
            ]
        ];

        $total = $m->mediaPageInfo($cond, 1);
        if ($total['total'] > 0) {
            $mlog->addLog([
                'media_id'      => $id,
                'log_type'      => $log_type,
                'log_time'      => time(),
                'errcode'       => -1,
                'errinfo'       => 'media used by resource',
            ]);
            return false;
        }

        $mfile = $mi['media_path'] . $mi['media_name'];

        /*start remove media*/ start_remove:;

        if (file_exists($mfile)) {
            $r = @unlink($mfile);
            if (!$r) {
                if ($unlink_try > 1) {
                    $unlink_try -= 1;
                    goto start_remove;
                }

                $mlog->addLog([
                    'media_id'  => $id,
                    'log_time'  => time(),
                    'errcode'   => -1,
                    'errinfo'   => 'failed to unlink file',
                    'log_type'  => $log_type
                ]);
                return false;
            }
        }

        $r = $m->removeInfo($id);
        if (!$r) {
            $mlog->addLog([
                'media_id'  => $id,
                'log_time'  => time(),
                'errcode'   => -1,
                'errinfo'   => 'failed to delete media info',
                'log_type'  => $log_type
            ]);
            return false;
        }
        
        $mlog->addLog([
            'media_id'  => $id,
            'log_time'  => time(),
            'errcode'   => 0,
            'errinfo'   => 'ok',
            'log_type'  => $log_type
        ]);

        return true;

    }

}

