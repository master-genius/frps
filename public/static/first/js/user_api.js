var api_table = {
    group_list : {
        url: _sysv.host + '/api/group/list',
        method : 'get',
        args : 'none'
    },

    rs_get : {
        url : _sysv.host + '/api/rs/get',
        method : 'get',
        args : 'must'
    },

    rs_list : {
        url : _sysv.host + '/api/rs',
        method : 'get',
        args : 'none'
    },

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
    location.href = "/u/login";
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

