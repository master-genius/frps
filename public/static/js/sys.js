function wstg_setItem(key,val, json_seri=false){
    if (json_seri) {
        sessionStorage.setItem(key,JSON.stringify(val));
    } else {
        sessionStorage.setItem(key, val);
    }
}

function wstg_getItem(key, json_seri=false){
    if (sessionStorage.getItem(key)===null) {
        return null;
    }
    if (json_seri) {
        return JSON.parse(sessionStorage.getItem(key));
    } else {
        return sessionStorage.getItem(key);        
    }
}

function wstg_removeItem(key){
    sessionStorage.removeItem(key);
}

function wstg_clear(){
    sessionStorage.clear();
}

function wstg_has(key) {
    if (sessionStorage.getItem(key) === null) {
        return true;
    }
    return false;
}

function show_post_cover() {
    brutal.classname('#post-cover', 'post-cover');
}

function hide_post_cover() {
    brutal.autod('#post-cover', '');
    brutal.classname('#post-cover', '');
}

function set_post_cover_data(data, attch = false) {
    brutal.html('#post-cover', data, attch);
}

function show_system_info(info,st){
    if(st!==undefined && (st=='error' || st===false)){
      brutal.classname("#sys-info","system-info-error");
    }
    else{
      brutal.classname("#sys-info","system-info");
    }

    brutal.autod("#sys-info",info);
    setTimeout(() => {
        brutal.classname("#sys-info",'');
      brutal.autod("#sys-info",'');
    }, 2560);
}

function show_alert_block(text) {
    brutal.classname('#alert-block', 'alert-block');
    brutal.autod('#alert-block', text);
}

function hide_alert_block() {
    brutal.classname('#alert-block', '');
    brutal.autod('#alert-block', '');
}

function show_start_win() {
    brutal.classname('#start-win', '');
    //brutal.html('#start-win',);
}

function hide_start_win() {
    brutal.classname('#start-win', 'hide-start-window');
}

function change_start_win() {
    if (brutal.classname('#start-win') === '') {
        hide_start_win();
    } else {
        show_start_win();
    }
}

window.onload = function() {
    if (document.getElementById('main-container')) {
        document.getElementById('main-container').addEventListener('click', function(e){
            if (brutal.classname('#start-win') === '')
                hide_start_win();
        });
    }
};
