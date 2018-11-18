<?php

namespace weixin;

class wxCURL
{
    //定义变量保存curl选项与获取的qccess_token
    protected $curl = '';
    protected $curl_options=[];

    //构造函数
    public function __construct()
    {

    }

    //发送请求并返回结果
    protected function send()
    {
        //设置默认变量
        $this->curl_options[CURLOPT_RETURNTRANSFER]= true;
        $this->curl_options[CURLOPT_SSL_VERIFYPEER] = false;

        //延迟到发送请求的时候再初始化curl对象
        //此操作可以在初始化一个wxCURL对象以后，可以反复调用
        $this->curl = curl_init();
        curl_setopt_array($this->curl, $this->curl_options);
        $response = curl_exec($this->curl);
        $errcode = curl_errno($this->curl);
        if ($errcode) {
            // throw new \Exception(curl_error($this->curl));
        } 
        curl_close($this->curl);
        $this->curl = null;
        //默认设置清空
        $this->curl_options = [];
        if ($errcode) {
            return false;
        }

        return $response;
    }

    //发送get请求
    /*
        url是要请求的连接
        args是GET参数，默认是空
    */
    public function get($url,$args=[])
    {
        $this->curl_options[CURLOPT_URL] = $url;
        return $this->send();
    }

    /*
        POST请求
        url：POST请求的连接
        data：POST发送的数据
        is_raw：是否提交原生数据流
    */
    public function post($url,$data,$is_raw=false)
    {
        //设置URL选项
        $this->curl_options[CURLOPT_URL] = $url;
        //设置POST数据
        $this->curl_options[CURLOPT_POST] = true;
        $this->curl_options[CURLOPT_POSTFIELDS] = $data;

        /*
            如果提交原生数据流，则设置HTTP请求Header为
            Content-Type:text/plain
        */
        if ($is_raw) {
            $this->curl_options[CURLOPT_HTTPHEADER] = [
                'Content-Type: text/plain'
            ];
        }
        return $this->send();
    }

    /*
        上传文件
        url ： 要上传的URL
        file_name ： 文件名，最好是绝对路径
        upload_name：上传使用名称，
            $_FILES[$upload_name]获取上传的文件
    */
    public function upload($url,$file_name,$upload_name='upload', $post_data=[])
    {
        $this->curl_options[CURLOPT_URL] = $url;
        $this->curl_options[CURLOPT_POST] = true;
        //初始化要上传的文件对象
        $file = new \CURLFile($file_name);
        $upload = [
            $upload_name=>$file
        ];
        if (!empty($post_data)) {
            foreach ($post_data as $k=>$v) {
                $upload[$k] = $v;
            }
        }
        //设置要上传的文件
        $this->curl_options[CURLOPT_POSTFIELDS] = $upload;
        return $this->send();
    }
    
    /*
        下载文件
        url：下载链接
        down_path：下载到本地的路径
        down_filename：下载到本地的文件名
        post_data：请求过程中要POST提交的数据
        is_raw：是否是提交原生数据流
    */
    public function download($url,$down_path,$down_filename='',$post_data='',$is_raw=false)
    {
        $this->curl_options[CURLOPT_URL] = $url;
        //如果文件名称为空则使用down_加上当前时间数字
        if (empty($down_filename)) {
            $down_filename = 'down_' . time();
        }

        //下载的本地路径+文件名
        $path_file = $down_path.'/'.$down_filename;

        //以wb模式打开文件，加上b避免因环境不同导致的格式不兼容
        $fd = fopen($path_file,'wb');
        if (flase === $fd) {
            exit('Error: open file ' . $path_file . 'failed.');
        }
        $this->curl_options[CURLOPT_FILE] = $fd;
        //如果post_data参数不为空，则设置POST提交
        if (!empty($post_data)) {
            $this->curl_options[CURLOPT_POST] = true;
            $this->curl_options[CURLOPT_POSTFIELDS] = $post_data;
            if ($is_raw) {
                $this->curl_options[CURLOPT_HTTPHEADER] = [
                    'Content-Type: text/plain'
                ];
            }
        }

        return $this->send();
    }

}
