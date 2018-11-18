var home = function(){

    this.key = 'home-rs-';

    this.searchData = function() {
        return {
            kwd : wstg_getItem(`${this.key}kwd`),
            group : wstg_getItem(`${this.key}group`),
            new_search : wstg_getItem(`${this.key}new-search`)
        };
    };

    this.page = function() {
        var html = `
            <div class="grid-container full" style="height:100%;">
                <div class="grid-x" style="height:100%;">
                    <div class="cell small-6 medium-6 large-6" style="height:100%;background-color:#4a4a4a;color:#efefef;text-align:center;">
                        <h3>大</h3>
                        <h3>道</h3>
                        <h3>至</h3>
                        <h3>简</h3>
                    </div>

                    <div class="cell small-6 medium-6 large-6" style="height:100%;color:#4a4a4a;text-align:center;">
                        <p>
                            避免一切肤浅，穿透一切复杂。
                            <p style="margin-left : 1.9rem;"> ——— 李小龙</p>
                        </p>
                        <div style="width:50%;position:fixed; text-align:center;bottom: 0.3rem;">
                            <h3>大</h3>
                            <h3>道</h3>
                            <h3>至</h3>
                            <h3>简</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="home-search">
                <div class="grid-x">
                    <div class="cell small-1 medium-2 large-3">&nbsp;</div>
                    <div class="cell small-10 medium-8 large-6">
                        <select id="group-list">
                            <option value="0">-所有-</option>
                        </select>
                    </div>
                    <div class="cell small-1 medium-2 large-3">&nbsp;</div>
                </div>
                <div class="grid-x">
                    <div class="cell small-1 medium-2 large-3">&nbsp;</div>
                    <div class="cell small-10 medium-8 large-6">
                        <form onsubmit="return false;">
                            <div class="input-group">
                                <input type="text" placeholder="搜索关键字，直接确认查看所有" class="input-group-field" id="rs-kwd">
                                <div class="input-group-button">
                                    <input type="submit" class="button secondary" value="Q" onsubmit="home.rsearch()" onclick="home.rsearch()">
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="cell small-1 medium-2 large-3">&nbsp;</div>
                </div>
            </div>
        `;

        document.body.style.height = "100%";
        document.body.style.overflowX = "hidden";
        
        brutal.autod('#main-container', html);
        _apicall({
            name : 'group_list',
            success : function (xr) {
                if (xr.status == 0) {
                    var gl = make_group_options(xr.group_list);
                    if (gl !== '') {
                        brutal.html('#group-list', gl, true);
                        
                        brutal.selected('#group-list', 
                            'set', 
                            wstg_getItem(`${home.key}group`)
                        );
                    }
                }
            }
        });
        
    };

    this.rsearch = function() {
        var kwd = wstg_getItem(`${this.key}kwd`);
        var group = wstg_getItem(`${this.key}group`);
        var new_kwd = brutal.autod('#rs-kwd');
        var new_group = brutal.selected('#group-list');
        if (new_kwd == kwd 
            && parseInt(new_group) == parseInt(group)
        ) {
            wstg_setItem(`${this.key}new-search`, 'no');
        }
        else {
            wstg_setItem(`${this.key}new-search`, 'yes');
            wstg_setItem(`${this.key}kwd`, brutal.autod('#rs-kwd'));
            wstg_setItem(`${this.key}group`, brutal.selected('#group-list'));
        }
        window.location.hash = '#rslist';
    };

    this.oninit = function() {
        if (wstg_getItem(`${this.key}init`) === null) {
            wstg_setItem(`${this.key}init`, 1);
            wstg_setItem(`${this.key}kwd`, '');
            wstg_setItem(`${this.key}group`, 0);
            wstg_setItem(`${this.key}new-search`, 'yes');
            this.page();
        } else {
            this.page();
            brutal.autod('#rs-kwd', wstg_getItem(`${this.key}kwd`));
        }
    };

    return {
        key : this.key,
        oninit : this.oninit,
        page : this.page,
        rsearch : this.rsearch,
        searchData : this.searchData
    };

}();
