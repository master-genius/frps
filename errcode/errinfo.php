<?php
namespace errcode;

class errinfo {

    private static $err_map = [];

    public static function errCodeInfo($m = 'common', $v = 'COMMON') {
        $err_file = CONFIG_PATH . '/errcode.php';
        if (file_exists($err_file)) {
            self::$err_map = include($err_file);
        }

        if (isset(self::$err_map[$m]) 
            && isset(self::$err_map[$m][$v])
        ) {
            return self::$err_map[$m][$v];
        } else {
            if (isset(self::$err_map['common'])
                && isset(self::$err_map['common']['COMMON'])
            ) {
                return self::$err_map['common']['COMMON'];
            } else {
                return [4404,
                    'Error: panic'
                ];
            }
        }
    }

    public static function retErr($m='common', $v='COMMON') {
        $err = self::errCodeInfo($m, $v);
        return [
            'status'  => -1,
            'errcode' => $err[0],
            'errinfo' => $err[1]
        ];
    }

    public static function defErr($info) {
        $err = self::errCodeInfo('common', 'USER_DEF');
        return [
            'status'    => -1,
            'errcode'   => $err[0],
            'errinfo'   => $info
        ];
    }

}

