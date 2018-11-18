var _view = function() {

    this.set_html_init = function(id, callback) {
        if (document.getElementById(id)) {
            document.getElementById(id).innerHTML = html;
        }

        if (typeof callback === 'function') {
            callback();
        }
    }

    this.rs_add = function(id, callback=null) {
        var html = `
            <form onsubmit="return false;">
                <label>Title</label>
                <input type="text" value="" id="rs-title">

                <label>Keywords</label>
                <input type="text" value="" id="rs-keywords">

                <select id="rs-group">
                    <option value="--null--">--</option>
                </select>

                <label>Tags</label>
                <input type="hidden" value="" id="rs-tag-list">
                <a href="javascript:;"></a>

                <label>Keywords</label>
                <input type="text" value="" id="rs-keywords">
            </form>
        `;
        this.set_html_init(id, callback);
    };

    this.rs_edit = function() {

    };

    this.rs_view = function () {

    };

    this.user_list = function () {

    };

    this.user_set = function () {

    };

    this.user_view = function() {

    };

    this.tag_list = function () {

    };

    this.tag_set = function () {

    };



    return {
        rs_add      : this.rs_add,
        rs_edit     : this.rs_edit,

    };

}();
