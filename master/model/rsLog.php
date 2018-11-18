<?php
namespace master\model;

use \core\DB;
use \errcode\errinfo;


class rsLog {

    private $table = 'resource_log';

    private $log_type = [
    ];

    public function addLog($log) {
        $r = DB::instance()->insert($this->table, $log);
        if ($r->rowCOunt() <= 0) {
            return false;
        }
        return true;
    }

    public function get($id) {
    
    }

    public function getLastStat($rsid, $log_type) {
        $cond = [
            'AND'   => [
                'rs_id'     => $rsid,
                'log_type'  => $log_type
            ],

            'ORDER' => [
                'log_time'   => 'DESC'
            ]
        ];
        $fields = [
            'rs_id',
            'log_time',
            'log_type',
            'rs_status',
            'rs_log'
        ];
        $log = DB::instance()->get($this->table, $fields, $cond);
        if (empty($log)) {
            return errinfo::defErr('can not get log');
        }
        return ['status'    => 0, 'log' => $log];
    }

}

