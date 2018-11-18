<?php
namespace core;

class dispatch {

    private $mod_map = [];

    public function __construct() {
        $this->mod_map = include(CONFIG_PATH . '/mod_map.php');
    }

    public function run($module, $action, $method, $req, $res) {
        $module_dir = APP_PATH . '/' . $module . '/action/';
        $map_name = null;
        if (isset($this->mod_map[$module]) && isset($this->mod_map[$module][$action])) {
            $map_name = $this->mod_map[$module][$action];
        } else {
            echo json_encode($this->mod_map);
            throw new \Exception('Error: module action not map --> ' . $action);
        }

        $action_file = $module_dir . $map_name . '.php';
        
        //echo $module_dir,$action_file;

        if (!file_exists($module_dir) || !file_exists($action_file)) {
            throw new \Exception('Error: module or action file is not exist');
        }
        $load_file = '\\' . $module . '\\action\\' . $map_name;

        try {
            $act = new $load_file;
            if (!method_exists($act, $method)) {
                throw new \Exception('Error: method is not exist');
            }
            return $act->$method($req, $res);
        } catch (\Exception $e) {
            throw $e;
        }
    }

}

