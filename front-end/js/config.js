jQuery.noConflict();

// wordpress localyser can't set right bool value
config_params['is-cronjob-turned'] = config_params['is-cronjob-turned'] == '' ? false : true;
config_params['is-publish'] = config_params['is-publish'] == '' ? false : true;

const desc_block = document.querySelector('#app-desc-style-block');
const cronjob_checkbox = document.querySelector('#cronjob-checkbox');
const publish_checkbox = document.querySelector('#is-publish');
const save_btn = document.querySelector('#save-btn');

desc_block.value = config_params['post-desc-style'];
cronjob_checkbox.checked = config_params['is-cronjob-turned'];
publish_checkbox.checked = config_params['is-publish'];

save_btn.addEventListener('click', save_config);

function save_config() {
    var post_desc_style = desc_block.value;
    var cronjob_value = cronjob_checkbox.checked;
    var is_publish_post_status = publish_checkbox.checked;

    $ajax_obj = {
        'url': config_params['ajax-url'],
        'type': 'POST',
        'data': {
            post_desc_style,
            cronjob_value,
            is_publish_post_status,
            action: 'change_config'
        }  
    }

    jQuery.ajax($ajax_obj).then((res) => {
        alert(res);
        window.location.reload();
    });
}