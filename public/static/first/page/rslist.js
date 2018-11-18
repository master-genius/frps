var rslist = function() {

    this.key = 'rs-list-';

    this.go_home = function() {
        window.location.hash = '#home';
    };

    this.gotoPage = function(page) {
        wstg_setItem(`${this.key}cur-scroll`, 0);
        wstg_setItem(`${this.key}cur-page`, page);
        this.list();
    };

    this.getRs = function(id) {
        wstg_setItem(`${this.key}cur-scroll`, 
            document.documentElement.scrollTop + document.body.scrollTop
        );
        window.location.hash = `#rs-${id}`;
    }

    this.page = function() {
        var html = `
            <div class="head-menu">
                <div class="grid-x">
                    <div class="cell small-2 medium-2 large-1">
                        <a href="javascript:this.go_home();"  style="color: #efefef;">
                            返回
                        </a>
                    </div>
                    <div class="cell small-4 medium-4 large-3">
                        <span id="group-name"></span>
                    </div>

                </div>
            </div>
            <div class="head-menu-space"></div>
            <div class="grid-x">
                <div class="cell medium-2 large-3">&nbsp;</div>
                <div class="cell small-12 medium-8 large-6" id="rs-list"></div>
                <div class="cell medium-2 large-3">&nbsp;</div>
            </div>
            <div class="footer-menu-space"></div>
            <div class="footer-menu">
                <div class="grid-x">
                    <div class="cell small-12" id="rs-list-pagination">
                    </div>
                </div>
            </div>
        `;

        brutal.html('#main-container', html);
        init_com_pagination('#rs-list-pagination');
        com_init_page_evt(this.gotoPage, this.gotoPage, this.gotoPage);
    };

    this.setPageInfo = function(page, total) {
        com_set_pageinfo(page, total);
    };

    this.showList = function(li) {
        var html = '';

        for(var i=0; i<li.length; i++) {
            html += `
                <div class="grid-x" style="margin-bottom: 0.8rem;">
                    <div class="cell small-12">
                        <a href="javascript:rslist.getRs(${li[i].id});" style="color:#4a4a4a;">
                            <h4>
                                <span>·</span>
                                ${li[i].rs_title}
                            </h4>
                            <p style="font-size:86%;color:#969696;">
                                ${li[i].description}
                            </p>
                        </a>
                    </div>
                </div>
            `;
        }

        brutal.autod('#rs-list', html);
    };

    this.list = function() {
        var d = home.searchData();
        var page = wstg_getItem(`${this.key}cur-page`);
        
        var total_page = wstg_getItem(`${this.key}total-page`);
        
        page = parseInt(page);
        total_page = parseInt(total_page);

        if (page > total_page || page < 1) {
            page = 1;
        }

        var qstr = '';
        qstr += `page=${page}`;

        if (d.kwd !== '' && d.kwd !==null && d.kwd !== undefined) {
            qstr += `&kwd=${encodeURIComponent(d.kwd)}`;
        }

        if (d.group != 0 && d.group !==null && d.group !== undefined) {
            qstr += `&group=${encodeURIComponent(d.group)}`;
        }

        _apicall({
            name : 'rs_list',
            querystr:qstr,
            success : function(xr) {
                if (xr.status == 0) {
                    if (xr.total_page == 0) {
                        rslist.setPageInfo(0, 0);
                        wstg_setItem(`${rslist.key}total-page`, 0);
                        wstg_setItem(`${rslist.key}cur-page`, 0);
                        rslist.showList([]);
                    } else {
                        rslist.setPageInfo(xr.cur_page, xr.total_page);
                        wstg_setItem(`${rslist.key}total-page`, xr.total_page);
                        rslist.showList(xr.rs_list);

                        var cur_scroll = wstg_getItem(`${rslist.key}cur-scroll`);
                        cur_scroll = parseInt(cur_scroll);
                        document.documentElement.scrollTop = cur_scroll;
                        document.body.scrollTop = cur_scroll;
                    }
                }
            }
        });
    };

    this.oninit = function() {
        rslist.page();
        if (wstg_getItem(`${this.key}init`) === null) {
            wstg_setItem(`${this.key}init`, 1);
            wstg_setItem(`${this.key}cur-page`, 1);
            wstg_setItem(`${this.key}total-page`, 1);
            wstg_setItem(`${this.key}cur-scroll`, 0);
        } else {
            var d = home.searchData();
            if (d.new_search === 'yes') {
                wstg_setItem(`${this.key}cur-page`, 1);
                wstg_setItem(`${this.key}total-page`, 1);
                wstg_setItem(`${this.key}cur-scroll`, 0);
            }
        }
        rslist.list();

    };

    return {
        key : this.key,
        oninit : this.oninit,
        page : this.page,
        list : this.list,
        showList : this.showList,
        setPageInfo : this.setPageInfo,
        gotoPage : this.gotoPage,
        getRs : this.getRs
    };
}();
