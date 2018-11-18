<?php

$pdo = '';
$db_name = 'rps';

try{
    $pdo = new PDO('mysql:host=127.0.0.1;', 'master', 'master2018');
}
catch (Exception $e) {
    exit($e->getMessage() . "\n");
}

$pdo->exec("create database if not exists $db_name");
$pdo->exec("use $db_name");

$prefix = 'w_';

$table_list = [
    'users'=>[
        'id'            => 'bigint primary key not null auto_increment',
        'reg_time'      => ' int not null default 0',
        'is_delete'     => 'tinyint default 0',
        'username'      => 'char(50) not null default "" unique',
        'passwd'        => 'char(200) not null default ""',
        'salt'          => 'char(32) not null default ""',
        'email'         => 'char(50) unique',
        'email_status'  => 'tinyint not null default 0',
        'mobile'        => 'char(12)',
        'mobile_status' => 'tinyint not null default 0',
        'nickname'      => 'char(100) not null default ""',
        'balance'       => 'decimal(6,2) not null default 0',
        'vip_level'     => 'tinyint not null default 0',
        'vip_start_time' => 'int not null default 0',
        'vip_end_time'  => 'int not null default 0',
        'last_login_ip' => 'char(64) not null default ""',
        'last_login_time' => 'int not null default 0',
        'stars'         => 'tinyint not null default 0',
        'headimg'       => 'text',

        'wx_openid'     => 'char(128) not null default ""',
        'wx_nickname'   => 'char(64) not null default ""',
        'wx_sex'        => 'tinyint not null default 0',
        'wx_country'    => 'char(32)',
        'wx_province'   => 'char(32)',
        'wx_city'       => 'char(32)',
        'wx_headimgurl' => 'text',

        'auto_increment' => 19001,
        'index' => [
            '`username`','`username`,`passwd`','`email`','`mobile`',
            'vip_level','`wx_openid`'
        ]
    ],

    'user_ask' => [
        'id'            => 'bigint primary key auto_increment',
        'user_id'       => 'bigint not null default 0',

        'problem'       => 'text',
        'ask_time'      => 'int not null default 0',
        /*
            0 ：问题已记录未处理
            1 ：问题已解决
            2 ：合并到重复或类似的问题答案
            3 ：拒绝
        */
        'problem_status'=> 'tinyint not null default 0',
        'tags' => 'char(32)',

        'foreign key(user_id)' => 'references w_users(`id`)',

        'auto_increment' => 1009,
        'index' => [
            '`user_id`', '`problem_status`'
        ]
    ],

    'user_tag' => [
        'id'        => 'int primary key not null',
        'tag_name'  => 'char(64)',
        'wx_tag_id' => 'int not null default 0',
        'auto_increment' => 101,
        'index' => [
            '`tag_name`'
        ]
    ],

    'resource' => [
        'id'            => 'bigint primary key not null auto_increment',
        'admin_id'      => 'int not null default 0',
        'rs_title'      => 'char(100) not null default ""',
        'add_time'      => 'int not null default 0',
        'update_time'   => 'int not null default 0',
        'rs_content'    => 'text',
        'rs_keywords'   => 'char(100) not null default ""',
        'rs_tag'        => 'char(100)',
        'rs_group'      => 'bigint not null default 0',
        'is_delete'     => 'tinyint not null default 0',
        'rs_sha1'       => 'char(50) not null default ""',
        'collect_count' => 'int not null default 0',
        'stars'         => 'tinyint not null default 0',
        'comment_status' => 'tinyint not null default 0',
        /*
            0  ：不是发布到公众号的文章，或者是已经删除公众号文章
            1  ：发布到公众号的文章
            2  ：发布到公众号失败
        */
        'is_article'    => 'tinyint not null default 0',
        'media_id'      => 'char(60) not null default ""',
        //图文素材的url
        'media_url'  => 'text',
        'index' => [
            'is_delete','rs_tag', '`media_id`'
        ],
        'auto_increment' => 1001
    ],

    'spider_resource' => [
        'id' => 'bigint primary key auto_increment',
        'rs_host' => 'char(64)',
        'rs_title' => 'char(200)',
        'rs_description' => 'text',
        'rs_url' => 'text',
        'rs_content' => 'text',
        'rs_type' => 'char(16)',
        'tags' => 'char(100)',
        'auto_increment' => 101,
        'index' => [
            '`rs_type`', '`rs_host`'
        ]
    ],

    'media' => [
        'id'            => 'bigint primary key not null auto_increment',
        'media_sha1'      => 'char(60) not null default ""',
        /*
            image
            audio
            video
            file
        */
        'media_type'    => 'char(32)',
        /*
            0  ：不是上传到微信服务器的素材
            1  ：上传到微信服务器
            10 ：没有上传到微信服务器但是文件不存在
            11 ：上传到微信服务器但是出现错误，需要重新上传
            12 ：上传到微信服务器失败，但是本地文件已不存在
        */
        'wx_status'     => 'tinyint not null default 0',
        'media_path'    => 'text',
        'media_url'     => 'text',
        'media_site_url'=> 'text',
        'upload_time'   => 'int not null default ""',
        'media_extname' => 'char(8) not null default ""',
        'index'         => [
            '`media_sha1`', '`media_type`', '`wx_status`'
        ]
    ],

    'resource_tag'=>[
        'id'        => 'bigint primary key not null auto_increment',
        'tag_name'  => 'char(30) not null unique',
        'index' => [
            'id', 'tag_name'
        ],
        'auto_increment' => 2354
    ],

    'resource_group'=>[
        'id'            => 'bigint not null primary key auto_increment',
        'group_name'    => 'char(30) not null default "undefined"',
        'parent_id'     => 'bigint not null default 0',
        'index' => [
            '`id`', 'group_name', 'parent_id'
        ],
        'auto_increment' => 2139
    ],

    /*
    'note_auth' => [
        'id' => 'char(50) primary key not null unique',
        'user_id' => 'char(40) not null default ""',
        'note_id' => 'char(50) not null default ""',
        'msg_time' => 'int not null default 0',
        'out_time' => 'int not null default 0',
        'auth_user' => 'text',
        'index' => [
            'user_id','note_id'
        ],
        'foreign key(user_id)' => 'references w_users(`id`)',
        'foreign key(note_id)' => 'references w_notes(`id`)',
    ],
    */

    'admin'=>[
        'id'            => 'int primary key not null auto_increment',
        'is_root'       => 'tinyint not null default 0',
        'is_forbid'     => 'tinyint default 0',
        'username'      => 'char(50) not null default "" unique',
        'passwd'        => 'char(200) not null default ""',
        'salt'          => 'char(32) not null default ""',
        'role'          => 'char(20) not null default ""',
        'email'         => 'char(50) not null default ""',
        'mobile'        => 'char(12) not null default ""',
        'email_status'  => 'tinyint not null default 0',
        'mobile_status' => 'tinyint not null default 0',
        'last_login_ip' => 'char(64)',
        'last_login_time' => 'int not null default 0',
        'auto_increment' => 199009,
        'index' => [
            '`is_root`', '`username`', '`username`,`passwd`'
        ]
    ],
    

];

function create_table($pdo, $table, $segment) {
    echo "create table: $table ...";
    $sql = 'create table if not exists ' . $table . '(';
    $sql_end = '';
    foreach($segment as $k=>$v) {
        if ($k == 'index') {
            foreach($v as $i) {
                $sql .= 'index('.$i.'),';
            }
        }
        elseif ($k == 'auto_increment') {
            $sql_end = 'alter table ' . $table . ' AUTO_INCREMENT=' . $v;
        }
        elseif (preg_match('/foreign.*/',$k)) {
            $sql .= $k . ' ' . $v . ',';
        }
        else{
            $sql .= '`'.$k.'`' . ' ' . $v . ',';
        }
    }
    $sql = trim($sql, ',');
    $sql .= ')';
    //echo $sql,"\n";
    $r = $pdo->exec($sql);
    if ($r===false) {
        echo $pdo->errorInfo()[2],"\n";
        return ;
    }
    if ($sql_end) {
        if (false===$pdo->exec($sql_end)) {
            echo $pdo->errorInfo()[2],"\n";
            return ;
        }
    }
    echo "ok\n";
}

foreach ($table_list as $t=>$g) {
    create_table($pdo, $prefix.$t, $g);
}

$salt = 'rootadmin';

$rpass = hash('md5','170129rootadmin');
$rpass = hash('sha512', $rpass);

$pdo->exec("insert into w_admin(`is_root`,`username`,`passwd`, `salt`) values(1,'root','$rpass', '$salt')");

