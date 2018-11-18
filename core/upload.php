<?php
namespace core;

class upload
{
    private $options = [
        //file type
        //this is all the type
        'type' => ['jpg','png','gif','mp4','mp3','jpeg'],

        //for all file type
        'max_size' => 25000000,

        'max_image_size' => 2097152,

        'max_audio_size' => 15000000,

        'max_video_size' => 25000000,

    ];

    private $ext = '';

    public function __construct($options=[])
    {
        if (isset($options['type'])) {
            $this->options['type'] = $options['type'];
        }

        $ftype = ['image', 'audio', 'video'];
        $type_ind = '';
        foreach($ftype as $t) {
            $type_ind = 'max_'.$t.'_size';
            if (isset($options[$type_ind])) {
                $this->options[$type_ind] = $options[$type_ind];
            }
        }
    }
    
    public function uploadFilter($file)
    {

        $type = mime_content_type($file['tmp_name']);
        $type_check = false;
        $file_type = '';
        
        switch ($type) {
            case 'audio/mpeg':
                $type_check = 'mp3';
                $file_type = 'audio';
                $this->ext = '.mp3';
                break;
            case 'image/jpeg':
                $type_check = 'jpg';
                $this->ext = '.jpg';
                if (is_array($this->options['type']) 
                    && false === array_search($type_check, $this->options['type'])
                ) {
                    $type_check = 'jpeg';
                    $this->ext = '.jpeg';
                }
                $file_type = 'image';
                break;
            case 'image/png':
                $file_type = 'image';
                $type_check = 'png';
                $this->ext = '.png';
                break;
            case 'image/gif':
                $file_type = 'image';
                $type_check = 'gif';
                $this->ext = '.gif';
                break;
            case 'video/mp4':
                $file_type = 'video';
                $type_check = 'mp4';
                $this->ext = '.mp4';
                break;
            default:;
        }

        if($this->options['type'] == '*') {
            goto all_type;
        }

        if (empty($file_type)) {
            throw new \Exception('Error: file type wrong');
        }

        if (false===array_search($type_check, $this->options['type'])) {
            throw new \Exception('Error: file type not be allowed');
        }
        all_type:;
        if ($file['size'] > $this->options['max_'.$file_type.'_size']) {
            throw new \Exception("Error: file size out of the max size limit");
        }

        return true;
    }

    public function fsha1($filename) {
        return sha1_file($filename);
    }

    public function getExt($filename) {
        $a = explode('.', $filename);
        $c = count($a);
        if ($c <= 1) {
            return '';
        }
        $this->ext = '.' . $a[$c-1];
        return $this->ext;
    }

    public function todo($file,$to)
    {
        $this->uploadFilter($file);

        if ($this->ext === '') {
            $this->getExt($file['name']);
        }

        $up_name = md5(microtime() . mt_rand(1, 10000)) . $this->ext;

        $r = @move_uploaded_file($file['tmp_name'], $to . $up_name);
        if (!$r) {
            throw new \Exception('Error: failed to move file');
        }

        return [
            'media_name' => $up_name,
            'ext'        => $this->ext
        ];
    }

}
