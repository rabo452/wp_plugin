jQuery.noConflict();


document.querySelectorAll(".scraper-btn").forEach((btn) => {
    btn.addEventListener('click', btn_click_event);
});

async function btn_click_event(e) {
    var btn = e.target; 

    // send request by site name that in button attribute
    var site_name = btn.getAttribute("site");
    var page_url = document.querySelector(`.scraper-url-input[site="${site_name}"]`).value;
    var site_links_block = document.querySelector(`.site-links[site="${site_name}"]`);
    site_links_block.innerHTML = ""; // clear prev output 

    var ajax_obj = {
        // ajax-url url endpoint for wordpress ajax 
        url: global_variables['ajax-url'],
        type: 'POST',
        data: {
            action: 'file_scraper_action',
            site_name: site_name,
            page_url: page_url
        }
    }

    files = await jQuery.ajax(ajax_obj);
    files = JSON.parse(files);

    // create for each file own link block
    files.forEach((file_info, index) => {
        var link_block = document.createElement("div");
        link_block.classList.add("link-block");

        var p = document.createElement('p');
        p.innerHTML = `link - <a href="${file_info['file_url']}" style="margin-right: 10px;"> filename - ${file_info['filename']}, size - ${file_info['file_size_mb']} (mb) </a>`;
        link_block.append(p);
        
        var link_btn = document.createElement("input");
        link_btn.type = "button";
        link_btn.value = "download the link";
        link_btn.setAttribute("site_name", site_name);
        link_btn.setAttribute("file_info", JSON.stringify(file_info));
        link_btn.addEventListener("click", btn_event_download);
        link_block.append(link_btn);

        site_links_block.append(link_block);
    });

}

async function btn_event_download(e) {
    var btn = e.target;
    var file_info = btn.getAttribute("file_info");
    var site_name = btn.getAttribute("site_name");

    var output_block = document.querySelector(`.scraper-output[site="${site_name}"]`);

    var res = await jQuery.ajax({
        url: global_variables['ajax-url'],
        type: "POST",
        data: {
            action: "download_file_scraper",
            site_name: site_name,
            file_info: file_info
        }
    });

    res = JSON.parse(res);
    output_block.value += `<a href="${res['file_download_link']}"> ${res['title']} [${res['file_size']} mb] </a>`;
    // delete link block
    btn.closest('.link-block').remove();
}