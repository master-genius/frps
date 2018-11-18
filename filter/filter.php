<?php
namespace filter;

/*
    数据过滤模块，需要每个类注册自己的过滤正则表达式或者是类型
    过滤方式包括正则匹配，类型匹配，长度限制
    格式：
    [
        'match' => 'REGEX'
    ]

    [
        'type'  => 'int|string|array',
    ]

    [
        'size'  => 'NUMBER'
    ]

    [
        'range' => [
            'min'   => 0,
            'max'   => 1024
        ]
    ]

    [
        'rangeOr' => [
            [
                'min'   => 0,
                'max'   => 5
            ],
            [
                'min'   => 12,
                'max'   => 35
            ],

        ]
    ]

    注意使用match要求必须是字符串类型，这已经具备类型限定的功能
    range限制数字的作用域，或者是字符串的长度，如果多个作用域都符合使用rangeOr

*/

class filter {
    private static $match_table = [
        'tag'   => [
            'id'    => [
                'type'  => 'int',
                'range' => [
                    'min'   => 1
                ]
            ],
            'tag_name'  => [
                'match' => '/^[A-Za-Z][a-zA-Z0-9]{5,15$}/'
            ],
            'group_id'  => [
                'type'  => 'int',
                'range' => [
                    'min'   => 0
                ]
            ]
        ],

        'resource'  => [
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

        ],

        'user'  => [
            'username'  => [
                'match' => '/^[A-Za-z][a-zA-Z0-9\-]{5,17}$/'
            ],

            'passwd'    => [
                'match' => '/^[A-Za-z0-9\-\.\@\$\%\^\&\*]{8,16}$/'
            ],

            'email'     => [
                'match' => '/^[A-Za-z0-9\-\_]{3,20}@[a-zA-Z0-9]{1,16}\.[a-zA-Z]{1,8}$/'
            ],

            'mobile'    => [
                'match' => '/^[11|12|13|14|15|16|17|18|19][0-9]{9}$/'
            ]
        ],

        'admin' => [
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
                'match' => '/(^[a-z0-9\-]{6,18}$)|(^[\u4e00-\u9fa5]{2,10}$)/i'
            ]
        ]


    ];


    public static function detectVal($val, $rule) {
         
        if (isset($rule['type'])) {
            switch($rule['type']) {
                case 'int':
                    if (!is_numeric($val)) {
                        set_sys_error('TypeError: value not a int');
                        return false;
                    }
                    break;
                case 'number':
                    if (!is_numeric($val)) {
                        set_sys_error('TypeError: value not a number');
                        return false;
                    }
                    break;
                case 'string':
                    if (!is_string($val)) {
                        set_sys_error('TypeError: value not a string');
                        return false;
                    }
                    break;
                case 'array':
                    if (!is_array($val)) {
                        set_sys_error('TypeError: value not a array');
                        return false;
                    }
                    break;
                default:;
            }
        }

        if (isset($rule['match'])) {
            if (!is_string($val)) {
                set_sys_error('TypeError: value must be string');
                return false;
            }
            if (!preg_match($rule['match'], $val)) {
                set_sys_error('MatchError: illegal char or wrong type --> ' . $val);
                return false;
            }
        } elseif (isset($rule['range'])) {
            if (!is_numeric($val) && !is_string($val)) {
                set_sys_error('TypeError: value must be number or string');
                return false;
            }

            $num = is_numeric($val)?$val:strlen($val);

            $info = 'ValueError: number out of limit';
            if (is_string($val)) {
                $info = 'ValueError: string cannot be empty or too long';
            }
            if (isset($rule['range']['min'])) {
                if ($num < $rule['range']['min']) {
                    set_sys_error($info);
                    return false;
                }
            }

            if (isset($rule['range']['max'])) {
                if ($num > $rule['range']['max']) {
                    set_sys_error($info);
                    return false;
                }
            }
        } elseif (isset($rule['size'])) {
            if (!is_string($val)) {
                set_sys_error('TypeError: value must be string');
                return false;
            }
            if (strlen($val) > $rule['size']) {
                set_sys_error('ValueError: too long text');
                return false;
            }
        } elseif (isset($rule['rangeOr'])) {
        
        }

        return true;
    }

    public static function check($m, $val_list, $keys=[]) {
        $match_file = CONFIG_PATH . '/filter/' . $m . '.php';
        if (file_exists($match_file)) {
            self::$match_table = include($match_file);
        } else {
            return true;
        }

        $rule_list = self::$match_table;

        foreach ($val_list as $k=>$v) {
            if (!empty($keys) && array_search($k, $keys)===false) {
                continue;
            }
            if (isset($rule_list[$k])) {
                if (!self::detectVal($v, $rule_list[$k])) {
                    return false;
                }
            }
        }

        return true;
    }
}

