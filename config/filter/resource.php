<?php

return [
    'id'    => [
        'type'  => 'int',
        'range' => [
            'min'   => 1
        ]
    ],

    'rs_title'  => [
        'range'  => [
            'min'   => 1,
            'max'   => 100
        ]
    ],

    'rs_keywords' => [
        'range'  => [
            'min'   => 1,
            'max'   => 100
        ]
    ],

    'rs_group'  => [
        'type'  =>'int',
        'range' => [
            'min'   => 0
        ]
    ],

    'rs_content' => [
        'range' => [
            'min'   => 10,
            'max'   => 40000
        ]
    ],

    'description' => [
        'range' => [
            'min'   => 0,
            'max'   => 128
        ]
    ]

];

