<?php

return [
    'id'    => [
        'range' => [
            'min'   => 1
        ]
    ],

    'username'  => [
        'match' => '/^[a-z][a-z0-9\-]{5,17}$/i'
    ],

    'passwd'    => [
        'match' => '/^[a-z0-9\-\.\@\$\%\^\&\*]{8,16}$/i'
    ],
    
    'author_name'   => [
        //'match' => '/(^[a-z0-9\-]{6,18}$)|(^[\u4e00-\u9fa5]{3,48}$)/i'
        'size'  => 40
    ]
];

