function make_group_options(group_list) {
    var html = '';
    for(var i=0; i<group_list.length; i++) {
        html += `
            <option value="${group_list[i].id}">
                ${group_list[i].group_name}
            </option>
        `;
    }
    
    return html;
}
