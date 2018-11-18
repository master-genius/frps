<?php
namespace master\model;

use \core\DB;
use \errcode\errinfo;


class mediaLog {

    private $table = 'media_log';



    public function addLog($log) {
        $r = DB::instance()->insert($this->table, $log);
        if ($r->rowCount() <= 0) {
            return false;
        }
        return true;
    }

    public function getLastStat($id) {
    
    }


}

