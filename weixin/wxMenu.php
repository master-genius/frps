<?php

namespace weixin;

use weixin\wxCURL;
use weixin\wxToken;

class wxMenu extends wxCURL
{
    //创建自定义菜单接口
    private $create_menu_api = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=';
    //删除自定义菜单接口
    private $delete_menu_api = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=';
    //获取自定菜单接口
    private $get_menu_api = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=';
    //要保存的access_token值
    private $token = '';

    public function __construct(){
        $this->token = wxToken::getToken();
        //如果获取失败则退出并返回错信息
        if (empty($this->token)) {
            exit('Error: get token failed.');
        }
    }

    //获取自定菜单方法
    public function getMenu()
    {
        $cur_api = $this->get_menu_api . $this->token;
        $ret = $this->get($cur_api);
        return $ret;
    }

    //创建自定义菜单方法
    public function createMenu($menu)
    {
        //如果是数组变量则转换成JSON编码字符串
        if( is_array($menu) ){
            $menu = json_encode($menu,JSON_UNESCAPED_UNICODE);
        }
        $cur_api = $this->create_menu_api . $this->token;
        $ret = $this->post($cur_api,$menu);
        return $ret;
    }

    //删除自定义菜单方法
    public function deleteMenu()
    {
        $cur_api = $this->delete_menu_api . $this->token;
        $ret = $this->get($cur_api);
        return $ret;
    }

}
