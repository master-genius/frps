<?php
namespace core;

class apiret
{
    static private $errinfo = [ 
        'user' => [
            'login_failed' => [1030, '用户登录失败'],
            'not_login' => [1031, '用户未登录'],
            'deny' => [1032, '用户不被允许'],
            'locked'=> [1033, '用户被锁定'],
            'black_list' => [1034, '用户被加入黑名单'],
            'empty_name_pass' => [1035, '用户名和密码不能为空'],
            'error_name_pass' => [1036,'用户名或密码错误'],
            'error_name_type' => [1037, '用户名格式错误'],
            'error_pass_type' => [1038, '密码格式错误'],
            'register_failed' => [1039,
                '注册失败，请检查密码和用户名是否符合要求'
            ],
        ],

        'note' => [
            'create_failed' => [1010, '笔记创建失败'],
            'data_illegal' => [1011, '笔记存在非法数据'],
            'size_out_limit' => [1012, '笔记大小超出限制'],
            'count_out_limit' => [1013, '笔记数量超出限制'],
            'delete_failed' => [1014,'删除笔记失败'],
            'collect_failed' => [1015, '收藏笔记失败'],
            'get_failed' => [1016, '获取信息失败'],
            'update_failed' => [1017, '笔记更新出现错误'],
            'not_update' => [1018, '笔记未更新'],
            'error_data_type' => [1019, '数据类型错误，提交整数1/0'],
            'error_price_type' => [10110, '请提交正确标价数据：0-1024'],
            'error_set_price' => [10111, '价格设置失败'],
            'set_price_deny' => [10112, '权限不被允许'],
            'get_notes_failed' => [10113, '批量获取笔记信息失败'],
            'error_idlist' => [10114, 'note_id列表格式错误'],
        ],

        'msg' => [
        
        ]
    ];
    static public function reterr($res, $mname, $err) {
        if (isset(self::$errinfo[$mname]) && isset(self::$errinfo[$mname][$err])) {
            return self::ret($res, [
                'errcode' => self::$errinfo[$mname][$err][0],
                'errmsg' => self::$errinfo[$mname][$err][1]
            ]);
        }
        else {
            return self::ret($res, [
                'errcode' => 11011,
                'errmsg' => '未知错误',
            ]);
        }
    }

    static public function ret($res, $data='success') {
        return $res->withHeader('Access-Control-Allow-Origin','*')
            ->withStatus(200)
            ->write(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

}

