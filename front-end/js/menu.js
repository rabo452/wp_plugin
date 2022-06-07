jQuery.noConflict();

const add_wp_btn = document.querySelector('#add-wp-btn');
const select_all_apps_btn = document.querySelector('#select-categories-btn');
const unselect_apps_btn = document.querySelector('#unselect-categories-btn');
const selected_apps_block = document.querySelector('#selected-apps-count');
const appsBtns = document.querySelectorAll('.select-btn > input');

class Events {
    _selected_apps_urls = [];
    // this variable need for while the script sends the requests for adding 
    // to wp app, the user can't select new apps
    adding_to_wp = false; 

    constructor() {
        this.AppBtnOnClickEvent = this.AppBtnOnClickEvent.bind(this);
        this.selectAllAppsEvent = this.selectAllAppsEvent.bind(this);
        this.AddToWpBtnEvent = this.AddToWpBtnEvent.bind(this);
        this.unSelectAllAppsEvent = this.unSelectAllAppsEvent.bind(this);

        appsBtns.forEach((btn) => btn.addEventListener('click', this.AppBtnOnClickEvent));
        select_all_apps_btn.addEventListener('click', this.selectAllAppsEvent);
        add_wp_btn.addEventListener('click', this.AddToWpBtnEvent);
        unselect_apps_btn.addEventListener('click', this.unSelectAllAppsEvent);
    }

    AppBtnOnClickEvent(e) {
        if (this.adding_to_wp) return;

        var btn = e.target;
        var is_selected = btn.getAttribute('selected') == 'false' ? false : true;
        var app_url = btn.getAttribute('url');

        if (is_selected) this.unSelectBtn(btn, app_url);
        else this.selectBtn(btn, app_url);

        this.ShowSelectedAppsCount();
    }

    unSelectAllAppsEvent() {
        appsBtns.forEach((btn) => {
            var is_selected = btn.getAttribute('selected') == 'false' ? false : true; 
            
            if (!is_selected) return;
            btn.click();
        })
    }

    selectAllAppsEvent() {
        appsBtns.forEach((btn) => {
            var is_selected = btn.getAttribute('selected') == 'false' ? false : true;

            if (is_selected) return;
            btn.click();
        });
    }
    
    AddToWpBtnEvent() {
        if (this._selected_apps_urls.length == 0) {
            alert('Please select the apps that you want to add');
            return;
        }

        this.adding_to_wp = true;
        this.hideSelectedApps(); // hide apps that user can't click on it again
        this.addAppsToWp();
    }

    // add to wordpress apps 1 by 1
    async addAppsToWp() {
        if (this._selected_apps_urls.length === 0) {
            alert("All apps have been added to wordpress");
            this.adding_to_wp = false;
            return;
        }

        var ajax_obj = {
            url: global_variables['ajax-url'],
            type: 'POST',
            data: {
                action: 'add_to_wp',
                app_link: this._selected_apps_urls.shift()
            }
        }

        await jQuery.ajax(ajax_obj);
        this.ShowSelectedAppsCount();

        await this.addAppsToWp().catch(e => {
			alert("Something went wrong or app doesn't exist");
		});
    }

    ShowSelectedAppsCount() {
        selected_apps_block.innerHTML = this._selected_apps_urls.length;
    }

    hideSelectedApps() {
        appsBtns.forEach((btn) => {
            var btn_url = btn.getAttribute('url');

            for (let selected_url of this._selected_apps_urls) {
                if (selected_url === btn_url) {
                    btn.closest('.app-block').remove();
                }
            }
        });
    }

    unSelectBtn(btn, url) {
        btn.setAttribute('selected', 'false');
        btn.value = "Select this app";

        this.deleteUrlFromSelectedApps(url);
    }

    selectBtn(btn, url) {
        btn.setAttribute('selected', 'true');
        btn.value = 'Delete from selected this app';

        this._selected_apps_urls.push(url);
    }
    
    deleteUrlFromSelectedApps(url) {
        for (var i = 0; i < this._selected_apps_urls.length; i++) {
            if (this._selected_apps_urls[i] === url) {
                this._selected_apps_urls.splice(i, 1);
                break;
            }
        }
    }
}

new Events();