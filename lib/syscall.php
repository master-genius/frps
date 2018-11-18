<?php

function what_system()
{
    $si = php_uname('a');
    $si = strtolower($si);
    if( strstr($si,'windows') ){
        return 'windows';
    }
    elseif( strstr($si,'linux') ){
        return 'linux';
    }
    else{
        return 'unknow';
    }
}

function is_linux()
{
    $what = what_system();
    return ($what=='linux')?true:false;
}

function is_windows()
{
    $what = what_system();
    return ($what=='windows')?true:false;
}

function set_token_session($u, $user_token)
{
    $GLOBALS['_token_session'] = $u;
    $GLOBALS['_token_session']['_token'] = $user_token;
}

function get_session($key='')
{
    if (empty($key)) {
        return (
            isset($GLOBALS['_token_session'])
            ?$GLOBALS['_token_session']
            :null
        );
    } elseif (isset($GLOBALS['_token_session'])) {
        return (
            isset($GLOBALS['_token_session'][$key])
            ?$GLOBALS['_token_session'][$key]
            :null
        );
    }
    return null;
}

function set_sys_error($info='')
{
    $GLOBALS['_sys_error_info'] = $info;
}

function get_sys_error()
{
    if(isset($GLOBALS['_sys_error_info'])){
        return $GLOBALS['_sys_error_info'];
    }
    return '';
}

