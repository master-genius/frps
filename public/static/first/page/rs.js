var rs = function(){

    this.parseRsId = function() {
        var hash = window.location.hash;
        return hash.substring(4);
    };

    this.showContent = function(r) {
        var html = `
            <h3>${r.rs_title}</h3>
            <div style="border-bottom: solid 0.05rem #aeaeae;height:0.8rem;">
            </div>
            <p>
                ${r.rs_content}
            </p>
        `;

        brutal.autod('#rs-content', html);
    };

    this.get = function(id) {
        
        _apicall({
            name : 'rs_get',
            args : id,
            success : function(xr) {
                if (xr.status == 0) {
                    rs.showContent(xr.resource);
                } else {

                }
            }
        });
    };

    this.page = function() {
        var html = `
            <div class="grid-x" style="margin-top:0.5rem;margin-bottom: 0.5rem;padding-left:0.2rem;padding-right:0.2rem;">
                <div class="cell medium-2 large-3">&nbsp;</div>
                <div class="cell small-12 medium-8 large-6" id="rs-content">
                </div>
                <div class="cell medium-2 large-3">&nbsp;</div>
            </div>
        `;

        brutal.autod('#main-container', html);
    };

    this.oninit = function() {
        this.page();
        this.get(this.parseRsId());
    };

    return {
        oninit : this.oninit,
        page : this.page,
        get : this.get,
        parseRsId : this.parseRsId,
        showContent : this.showContent
    };
}();
