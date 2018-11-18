function init_com_pagination (id) {
    var html = `<div class="grid-x" style="text-align: center;">
        <div class="cell small-3 medium-3 large-3" style="text-align:right;">
            <span id="com-prev-page">&lt;&lt;</span>
        </div>
        <div class="cell small-6 medium-6 large-6">
            <span id="com-page-info">
                <span id="com-cur-page">1</span>
                <span>/</span>
                <span id="com-total-page">1</span>
            </span>
        </div>
        <div class="cell small-3 medium-3 large-3"  style="text-align:left;">
            <span id="com-next-page">&gt;&gt;</span>
        </div>
        
    </div>`;
    brutal.autod(id, html);
}

function com_jump_page (callback) {
    var page = prompt('跳转至第几页？', '');
    if (page === null) {
        
        return ;
    }
    var end_page = brutal.autod('#com-total-page');
    var cur_page = brutal.autod('#com-cur-page');
    end_page = parseInt(end_page);
    page = parseInt(page);
    cur_page = parseInt(cur_page);

    if (page >0 && page <= end_page && page != cur_page) {
        if (typeof callback === 'function') {
            brutal.autod('#com-cur-page', page);
            callback(page);
        }
    }
}

function com_prev_page (callback) {
    var cur_page = brutal.autod('#com-cur-page');
    cur_page = parseInt(cur_page);
    if (cur_page > 1) {
        cur_page -= 1;
        if (typeof callback === 'function') {
            brutal.autod('#com-cur-page', cur_page);
            callback(cur_page);
        }
    }
}

function com_next_page(callback) {
    var cur_page = brutal.autod('#com-cur-page');
    var total_page = brutal.autod('#com-total-page');
    cur_page = parseInt(cur_page);
    total_page = parseInt(total_page);
    if (cur_page < total_page) {
        cur_page += 1;
        if (typeof callback === 'function') {
            brutal.autod('#com-cur-page', cur_page);
            callback(cur_page);
        }
    }
}

function com_first_page(callback) {
    brutal.autod('#com-cur-pag', '1');
    callback(1);
}

function com_last_page(callback) {
    var last_page = brutal.autod('#com-total-page');
    callback(parseInt(last_page));
}

function com_set_pageinfo(cur_page, total_page) {
    brutal.autod('#com-cur-page', cur_page);
    brutal.autod('#com-total-page', total_page);
}

function com_init_page_evt(prev_page, next_page, jump_page) {
    brutal.addevent('#com-prev-page', 'click', function(e){
        com_prev_page(prev_page);
    });

    brutal.addevent('#com-prev-page', 'dblclick', function(e){
        com_first_page(jump_page);
    });

    brutal.addevent('#com-next-page', 'click', function(e){
        com_next_page(next_page);
    });

    brutal.addevent('#com-next-page', 'dblclick', function(e){
        com_last_page(jump_page);
    });

    brutal.addevent('#com-page-info', 'click', function(e){
        com_jump_page(jump_page);
    });
}
