var _media_callback_type    = 'cover'; // 'content'
var _media_picked_callback = '';
var _media_content_callback = '';

function show_select_media_window() {
    brutal.classname('#media-list-select', 'media-list-select-block');
    brutal.html('#media-list-select', `
        <div class="grid-x" style="z-index : 1025;position : fixed; background-color:#ffffff;line-height:2.5rem;height:2.6rem;width:60%;">
            <div class="cell small-1 medium-1 large-1">
                <h3 onclick="hide_select_media_window()">X</h3>
            </div>
            <div class="cell small-11 medium-11 large-11" id="media-select-pagination">
            </div>
        </div>
        <div style="margin-top : 2.8rem;"></div>

        <div id="pick-media-list" style="scroll:auto;">
        </div>

    `);
    init_com_pagination('#media-select-pagination');
    com_init_page_evt(function(page) {
        get_media_list(page);
    }, function(page) {
        get_media_list(page);
    }, function(page) {
        get_media_list(page);
    });
}

function hide_select_media_window() {
    brutal.autod('#media-list-select', '');
    brutal.classname('#media-list-select', '');
}


function show_select_media_list(li) {
    var html = '';
    for(var i=0; i< li.length ;i++) {
        html += `
            <div class="float-block-image">
                <div onclick="media_picked('${li[i].media_site_url}', ${li[i].id})" style="background-image:url('${li[i].media_site_url}');background-position:center;background-repeat:no-repeat;background-size:cover;width:100%;height:12rem;">
                </div>
            </div>
        `;
    }
    brutal.autod('#pick-media-list', html);
}

function media_picked(site_url, mid) {
    if (_media_callback_type !== 'content') {
        if (typeof _media_picked_callback === 'function') {
            _media_picked_callback(site_url, mid);
        }
    } else if (typeof _media_content_callback === 'function') {
        _media_content_callback(site_url);
    }
    hide_select_media_window();
}

function get_media_list(page) {
    _apicall({
        name : 'media_list',
        querystr : `page=${page}`,
        success : function (xr) {
            com_set_pageinfo(page, xr.total_page);
            show_select_media_list(xr.media_list);
        }
    });
}

function select_from_media_list (calltype = 'cover') {
    _media_callback_type = calltype;
    show_select_media_window();
    get_media_list(1); 
}
