<?php
namespace master\model;

use \core\DB;
use \core\upload;
use \errcode\errinfo;
use \weixin\wxMaterial;
use \msqserv\msq;
use \uauth\admin;

class media {

    private $max_image  = 10000;
    private $max_audio = 2000;
    private $max_video = 1000;

    private $options = [
        'type' => ['jpg','jpeg', 'png', 'mp3', 'mp4']
    ];

    private $table = 'media';

    private $fields = [
        'id', 
        'wx_status', 
        'media_url', 
        'media_site_url',
        'media_name',
        'media_type',
        'media_sha1',
        'media_id',
        'media_size',
        'media_tag'
    ];


    public function upload($f) {

        try {

            if (!isset($f['media_upload'])) {
                return errinfo::retErr('master', 'MI_FNOT');
            }

            $mime_type = $f['media_upload']->getClientMediaType();

            $path = '';
            $type = 'others';

            if (preg_match('/image.*/', $mime_type)) {
                $type = 'image';
                $path = IMAGE_PATH;
            } elseif (preg_match('/audio.*/', $mime_type)) {
                $type = 'audio';
                $path = AUDIO_PATH;
            } elseif (preg_match('/video.*/', $mime_type)) {
                $type = 'video';
                $path = VIDEO_PATH;
            } else {
                $path = OTHERS_PATH;
            }

            $media_url = '';
            $up = new upload($this->options);
            $file_sha1 = $up->fsha1($_FILES['media_upload']['tmp_name']);
            $chk = DB::instance()->get('media', ['media_site_url'], ['media_sha1' => $file_sha1]);
            //check if file already here
            $uploaded = false;
            if (!empty($chk)) {
                $uploaded = true;
                $media_url = $chk['media_site_url'];
                goto just_ret;
            }

            $ret = $up->todo($_FILES['media_upload'], $path);
            $upname = $ret['media_name'];
            $media_url = MEDIA_URI_PATH . '/' . $type . '/' . $upname;


            $retry_count = 2;
        db_retry:;
            $r = DB::instance()->insert('media', [
                'media_sha1'    => $file_sha1,
                'media_type'    => $type,
                'wx_status'     => 0,
                'media_path'    => $path,
                'media_site_url'=> $media_url,
                'media_name'    => $upname,
                'media_extname' => $ret['ext'],
                'upload_time'   => time(),
                'media_size'    => $f['media_upload']->getSize(),
                'admin_id'      => admin::adminId()
            ]);
            
            if ($r->rowCount() == 0) {
                if ($retry_count > 0) {
                    $retry_count -= 1;
                    goto db_retry;
                }
                return errinfo::retErr('master', 'MI_DBFAIL');
            }
            just_ret:;
            return [
                'status'        => 0,
                'media_url'     => $media_url,
                'uploaded'      => $uploaded
            ];

        } catch (\Exception $e) {
            $err = errinfo::retErr('master', 'MI_UFAIL');
            $err['errinfo'] .= ';' . $e->getMessage();
            return $err;
        }
    }

    public function mediaList($cond){

        $list = DB::instance()->select($this->table, $this->fields, $cond);
        return $list;
    }

    public function mediaPageInfo($cond, $pagesize) {
        $total = DB::instance()->count($this->table, $cond);
        $total_page = total_page($total, $pagesize);
        return [
            'total'     => $total,
            'total_page'=> $total_page
        ];
    }

    public function getMediaInfo($id) {
        $cond = ['id'   => $id];

        /*
        if (! admin::isRoot()) {
            $cond = [
                'AND'   => [
                    'id'        => $id,
                    'admin_id'  => admin::adminId()
                ]
            ];
        }
        */
        $mid = DB::instance()->get($this->table, $this->fields, $cond);
        if (empty($mid)) {
            return null;
        }
        return $mid;
    }

    public function mediaInfo($id) {
        $m = $this->getMediaInfo($id);
        if ($m === null) {
            return errinfo::retErr('master', 'MI_FNOT');
        }
        return [
            'status'    => 0,
            'media'     => $m
        ];
    }

    public function removeInfo($id) {
        $r = DB::instance()->delete($this->table, ['id' => $id]);
        if ($r->rowCount() <= 0) {
            return false;
        }
        return true;
    }

    public function remove($id) {
        (new msq)->send('remove-media', $id);
        return [
            'status'    => 0,
            'info'      => 'request accepted'
        ];
    }

    public function mediaTotalInfo($pagesize = 15) {
        $cond = ['id[>0]' => 0];
        if (! admin::isRoot()) {
            $cond = [
                'AND' => [
                    'id[>]' => 0,
                    'admin_id'  => admin::adminId()
                ]
            ];
        }
        $total = DB::instance()->count($this->table, $cond);
        $total_page = total_page($total, $pagesize);

        $wx_total = DB::instance()->count($this->table, ['wx_status' => 1]);

        $wx_total_page = total_page($wx_total, $pagesize);
        
        return [
            'total'         => $total,
            'total_page'    => $total_page,
            'wx_total'      => $wx_total,
            'wx_total_page' => $wx_total_page
        ];

    }

    public function uploadWeixin($id) {
        set_sys_error('');

        $mid = $this->getMediaInfo($id);
        if (null === $mid) {
            set_sys_error('can not get media info');
            return false;
        }
        
        if ($mid['media_type'] == 'image') {
            $r = (new wxMaterial)->uploadMaterial(
                IMAGE_PATH . $mid['media_name'],
                'image'
            );
            if ($r === false) {
                set_sys_error('upload media to weixin failed');
                return false;
            }
            $ret = json_decode($r, true);
            if (isset($ret['errcode'])) {
                set_sys_error($ret['errmsg']);
                DB::instance()->update($this->table, ['wx_status'=>11], ['id' => $id]);
                return false;
            }

            $r = DB::instance()->update($this->table, [
                'media_id'  => $ret['media_id'],
                'media_url' => $ret['url'],
                'wx_status' => 1,
            ],['id'=>$id]);
            return $ret;
        }
        
        set_sys_error('media type not be supported');
        return false;
    }

    public function removeWXMedia($id) {
        $mid = $this->getMediaInfo($id);
        if (null === $mid) {
            set_sys_error('media not found');
            return false;
        }

        if ($mid['wx_status']!=1 && empty($mid['media_id'])) {
            set_sys_error('not a weixin media');
            return false;
        }

        $r = (new wxMaterial)->removeMaterial($mid['media_id']);
        $ret = json_decode($r, true);
        if (isset($ret['errcode'])) {
            set_sys_error($ret['errmsg']);
            return false;
        }
        return true;
    }

}

