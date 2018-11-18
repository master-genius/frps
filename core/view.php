<?php
namespace core;

class view {

    public function render($vfile, $vars=[]) {
        $path_file = VIEW_PATH . '/' . $vfile;
        if (file_exists($path_file)) {
            ob_start();
            if (!empty($vars)){
                extract($vars);
            }
            include($path_file);
            $page_data = ob_get_contents();
            ob_clean();
            exit($page_data);
        } else {
            exit("deny!");
        }
    }
}
