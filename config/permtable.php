<?php

return [

    'ROLE-LIST' => ['rs-admin'],

    'MENU-LIST' => [
    ],

    'root'  => '*',

    'rs-admin' => [
        'GET'   => '*',

        'POST'  => [
            '/master/upload/media',
            '/master/rs/update',
            '/master/rs/add',
            '/master/rs/delete',
        ],

        'MENU'  => [
            //'首页'     => '/backend/manage',
            '创建资源' => '/backend/rs/add',
            '资源列表' => '/backend/rs/list',
            '媒体素材' => '/backend/media'
        ]
    ],

];

