<?php
namespace core;

use Medoo;

class DB
{
    static private $_db = null;

    private function __construct(){
    
    }

    static public function instance(){
        if (self::$_db === null) {
            $dbconfig = include CONFIG_PATH . '/database.php';
            self::$_db = new \Medoo\Medoo($dbconfig);
        }
        return self::$_db;
    }

}

