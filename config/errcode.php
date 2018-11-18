<?php

/*
    错误代码定义，一级索引表示模块，common表示通用。

    4403  : 系统默认返回错误代码

    100    : 系统错误代码
    101    : 通用错误代码
    102+   : 其他模块错误代码

*/

return [

    'sys' => [
        'DB_QRYE'     => [1001,  'DB Query exception'],
        'DB_INSTE'    => [1002,  'DB write exception'],
        'SYS_ERR'     => [1003,  'php code exception'],
    ],

    'common' => [
        'USER_DEF' => [
            44001,
            ''
        ],

        'COMMON'    =>  [
            4403,
            'Error: failed or permission deny'
        ],
        'VAL_ERR'   => [
            4404,
            'Error: value wrong'
        ],

        'DB_UPD'    => [4405,
            'Error: failed to update data'
        ],

        'DB_INRT'   => [4407,
            'Error: failed to insert data'
        ],

        'DB_NOTFD'  => [4406,
            'Error: data not found'
        ]
    ],

    'master' => [
        'NOT_LOGIN' => [
            10000,
            'not login'
        ],

        'LOGIN_FAIL' => [
            10001,
            'login failed'
        ],

        'TOK_TMO'   => [10002,
            'token timeout'
        ],

        'RS_RNOTF'   => [10201, 
            'Error: get resource failed'
        ],
        'RS_SETE'   => [10202,
            'Error: add/update resource failed'
        ],

        'RS_RDEL'   => [102021,
            'Error: failed to delete resource'
        ],

        'RS_BADID'  => [102022,
            'Error: rs id wrong'
        ],

        'MI_UFAIL'  => [10203,
            'Error: failed to upload file'
        ],

        'MI_FNOT'   => [10204,
            'Error: file not found'
        ],

        'MI_DBFAIL' => [10205,
            'Error: failed to write media info to db'
        ],

        'TAG_OUTM'  => [10206,
            'Error: tag`s number out of max limit'
        ],

        'TAG_DBFAIL' => [10207,
            'Error: fialed to add tag'
        ],

        'TAG_RSUSE' => [10208,
            'Error: tag already used by resource'
        ],

        'GRP_RSUSE' => [10209,
            'Error: group already used by resource'
        ]
    ],

];
