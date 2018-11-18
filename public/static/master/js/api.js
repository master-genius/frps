var api_table = {
    group_list : {
        url:_sysv.host + '/master/group/list',
        method : 'get',
        args : 'none'
    },
    
    group_add : {
        url : _sysv.host + '/master/group/add',
        method : 'post',
        args : 'none'
    },

    group_del : {
        url : _sysv.host + '/master/group/delete',
        method : 'get',
        args : 'must'
    },

    group_upd : {
        url : _sysv.host + '/master/group/update',
        method : 'post',
        args : 'must'
    },

    media_list : {
        url : _sysv.host + '/master/media/list',
        method : 'get',
        args : 'none'
    },

    media_upload : {
        url : _sysv.host + '/master/upload/media',
        method : 'upload',
        args : 'none'
    },

    media_del : {
        url : _sysv.host + '/master/media/delete',
        method : 'get',
        args : 'must'
    },

    media_wxupload : {
        url : _sysv.host + '/master/media/wxupload',
        method : 'get',
        args : 'must'
    },

    rs_get : {
        url : _sysv.host + '/master/rs/get',
        method : 'get',
        args : 'must'
    },

    rs_list : {
        url : _sysv.host + '/master/rs/list',
        method : 'get',
        args : 'none'
    },

    rs_edit : {
        url : _sysv.host + '/master/rs/edit',
        method : 'post',
        args : 'must'
    },

    rs_add : {
        url : _sysv.host + '/master/rs/add',
        method : 'post',
        args : 'none'
    },

    rs_del : {
        url : _sysv.host + '/master/rs/delete',
        method : 'get',
        args : 'must'
    },

    rs_make_wxnews : {
        url : _sysv.host + '/master/rs/makewxnews',
        method : 'post',
        args : 'must'
    },

    rs_last_log : {
        url : _sysv.host + '/master/rs/lastlog',
        method : 'get',
        args : 'must'
    },

    admin_logout : {
        url : _sysv.host + '/master/admin/logout',
        method : 'get',
        args : 'none'
    },

    admin_add : {
        url : _sysv.host + '/master/admin/add',
        method : 'post',
        args : 'none'
    },

    admin_list : {
        url : _sysv.host + '/master/admin/list',
        method : 'get',
        args : 'none'
    },

    admin_del : {
        url : _sysv.host + '/master/admin/remove',
        method : 'get',
        args : 'must'
    },

    admin_upd : {
        url : _sysv.host + '/master/admin/update',
        method : 'post',
        args : 'must'
    },

    admin_info : {
        url : _sysv.host + '/master/admin/info',
        method : 'get',
        args : 'none'
    }

};

var jsonqry = function (jsd){
    var data = '';
    for(var k in jsd){
        if(typeof jsd[k] == 'object'){
            jsd[k] = jsd[k].toString();
        }
        data += k+'='+encodeURIComponent(jsd[k])+'&';
    }
    return data.substring(0,data.length-1);
};

function redirect_login() {
    location.href = "/back/login/admin";
}

function get_token_str(url) {
    var token = wstg_getItem('api-token');
    token = encodeURIComponent(token)
    if (url.indexOf('?') >= 0) {
        return '&api_token=' + token;
    } else {
        return '?api_token=' + token; 
    }
}

function api_get(xd) {
    xd.url += get_token_str(xd.url);

    aj_get({
        url : xd.url,
        //data : xd.data,
        success : function (xr) {
            if (xr.errcode == 10000 || xr.errcode == 10002) {
                redirect_login();
            } else {
                xd.success(xr);
            }
        },
        except : xd.except,
        error : xd.error

    });
}

function api_post(xd) {
    xd.url += get_token_str(xd.url);
    
    aj_post({
        url : xd.url,
        data : xd.data,
        success : function (xr) {
            if (xr.errcode == 10000 || xr.errcode == 10002) {
                redirect_login();
            } else {
                xd.success(xr);
            }
        },
        except : xd.except,
        error : xd.error
    });
}

function api_upload(file, xd) {
    xd.url += get_token_str(xd.url);
    upload_one(file, xd);
}

var _apicall = function(api) {
    if (typeof api_table[api.name] === undefined) {
        return false;
    }

    var a = api_table[api.name];

    if (a.args === 'must' && typeof api.args === undefined) {
        return false;
    }

    var complete_url = a.url;
    if (typeof api.args !== undefined 
        && (typeof api.args === 'string' || typeof api.args === 'number')
    ) {
        complete_url += '/' + api.args;
    }

    if (typeof api.querystr === 'string') {
        complete_url += '?' + api.querystr;
    }

    if (a.method === 'post' && typeof api.data === undefined) {
        return false;
    }

    var data = '';
    if (typeof api.data === 'string') {
        data = api.data;
    } else if (typeof api.data === 'object') {
        data = jsonqry(api.data);
    }

    if (a.method === 'get') {
        api_get({
            url : complete_url,
            success : api.success,
            error : api.error,
            except : api.except
        });
    } else if (a.method === 'post') {
        api_post ({
            url : complete_url,
            data : data,
            success : api.success,
            error : api.error,
            except : api.except
        });
    } else if (a.method === 'upload') {
        api_upload(api.file, {
            url : complete_url,
            upload_name : api.upload_name,
            success : api.success,
            error : api.error ,
            except : api.except
        });
    }
    return true;
};

