<?php

namespace weixin;

class wxUserManage extends wxCURL
{
    private $create_tag_url = 'https://api.weixin.qq.com/cgi-bin/tags/create?access_token=';
    private $get_tags_url = 'https://api.weixin.qq.com/cgi-bin/tags/get?access_token=';
    private $user_list_url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=';
    private $user_info_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=';
    private $edit_tag_url = 'https://api.weixin.qq.com/cgi-bin/tags/update?access_token=';
    private $remove_tag_url = 'https://api.weixin.qq.com/cgi-bin/tags/delete?access_token=';
    private $get_taguser_url = 'https://api.weixin.qq.com/cgi-bin/user/tag/get?access_token=';
    private $tag_on_user_url = 'https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token=';
    private $user_remark_url = 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token=';
    private $unset_tag_url = 'https://api.weixin.qq.com/cgi-bin/tags/members/batchuntagging?access_token=';
    private $batch_userinfo_url = 'https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token=';

    private $token = '';

    public function __construct()
    {
        $this->token = wxToken::getToken();
        if (empty($this->token)) {
            exit('Error: get token failed.');
        }
    }

    //创建用户标签
    public function createTag($tag_name)
    {
        $tag_api = $this->create_tag_url . $this->token . '&name='.$tag_name;
        $data = '{"tag":{"name":"'.$tag_name.'"}}';
        $r = $this->post($tag_api,$data);
        return $r;
    }

    //获取标签列表
    public function getTags()
    {
        $tag_api = $this->get_tags_url . $this->token;
        $response = $this->get($tag_api);
        return $response;
    }

    //编辑标签
    public function editTag($tag_id,$tag_name)
    {
        $tag_api = $this->edit_tag_url . $this->token;
        $data = '{"tag":{"id":'.$tag_id.',"name":"'.$tag_name.'"}}';
        $r = $this->post($tag_api,$data);
        return $r;
    }

    //删除标签
    public function removeTag($tag_id)
    {
        $tag_api = $this->remove_tag_url . $this->token;
        $data = '{"tag":{"id":'.$tag_id.'}}';
        $response = $this->post($tag_api,$data);
        return $response;
    }

    //获取标签下的用户列表
    public function getTagUserList($tag_id)
    {
        $list_api = $this->get_taguser_url . $this->token;
        $data = '{"tagid":'.$tag_id.',"next_openid":""}';
        $response = $this->post($list_api,$data);
        return $response;
    }

    //批量为用户打标签
    public function setTagForUsers($tag_id,$openid_list)
    {
        $tag_api = $this->tag_on_user_url . $this->token;
        $data = [
            'openid_list'=>$openid_list,
            'tagid'=>$tag_id
        ];
        $response = $this->post($tag_api,json_encode($data));
        return $response;
    }

    //批量为用户取消标签
    public function unsetTagForUsers($tag_id,$openid_list)
    {
        $tag_api = $this->unset_tag_url . $this->token;
        $data = [
            'openid_list'=>$openid_list,
            'tagid'=>$tag_id
        ];
        $response = $this->post($tag_api,json_encode($data));
        return $response;
    }

    //获取用户列表
    public function getUserList($next_openid='')
    {
        $list_api = $this->user_list_url . $this->token . '&next_openid=' . $next_openid;
        $response = $this->get($list_api);
        return $response;
    }

    //批量获取用户信息快速调用
    public function batchUserInfo($openid_list)
    {
        $post_list = [];
        foreach ($openid_list as $openid) {
            $post_list[] = [
                'openid'=>$openid,
                'lang'=>'zh_CN'
            ];
        }
        return $this->getUserInfoList($post_list);
    }

    //批量获取用户信息
    public function getUserInfoList($user_list)
    {
        $list_info_api = $this->batch_userinfo_url . $this->token;
        $post_data = [
            'user_list'=>$user_list
        ];
        $post_list = json_encode($post_data,JSON_UNESCAPED_UNICODE);
        $users_info = $this->post($list_info_api,$post_list);
        return $users_info;
    }

    //获取用户信息
    public function getUserInfo($openid)
    {
        $info_api = $this->user_info_url . $this->token . '&openid='.$openid.'&lang=zh_CN';
        $response = $this->get($info_api);
        return $response;
    }

    //设置用户备注名
    public function setUserRemark($openid,$remark)
    {
        $remark_api = $this->user_remark_url . $this->token;
        $data = '{"openid":"'.$openid.'","remark":"'.$remark.'"}';
        $response = $this->post($remark_api,$data);
        return $response;
    }

}
