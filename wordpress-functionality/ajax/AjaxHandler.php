<?php
// this file handle the ajax handler points

require_once plugin_dir_path( __FILE__ ) . '/../../DataManipulator.php';
require_once plugin_dir_path( __FILE__ ) . '/../AppSaver.php';
require_once plugin_dir_path( __FILE__ ) . '/../FilterApps.php';

class AjaxHandler {
    // define all ajaxs handlers
    function __construct(array $scrapers, array $download_file_scrapers){
        $this->scrapers = $scrapers;
        $this->download_file_scrapers = $download_file_scrapers;

        add_action('wp_ajax_change_config', [$this, 'changeConfig']);
        add_action('wp_ajax_add_to_wp', [$this, 'addAppToWp']);
        add_action('wp_ajax_file_scraper_action', [$this, "getSiteFileInfo"]);
        add_action("wp_ajax_download_file_scraper", [$this, "downloadFileEndPoint"]);
    }

    public function changeConfig() {
        try {
            $post_desc_style = str_replace('\\', '', $_POST['post_desc_style']);
            $cronjob_value = $_POST['cronjob_value'] == "true";
            $is_publish_post_status = $_POST["is_publish_post_status"] == "true";

            $obj = new DataManipulator();
            $obj->setDescriptionStyle($post_desc_style);

            $cronjob_obj = $obj->getCronJobData();
            $cronjob_obj['cronjob-start'] = $cronjob_value;
            $cronjob_obj['isPublish'] = $is_publish_post_status;

            $obj->setCronJobData($cronjob_obj);

            echo "Plugin's config changed successfully";
        }catch(Exception $e) {
            echo 'Something went wrong';
        }

        wp_die();
    }

    public function addAppToWp() {
        try {
            $app_link = $_POST['app_link'];

            // from link get the site scraper that should scrape this link
            $site_scraper = null;
            foreach($this->scrapers as $scraper) {
                if (strpos($app_link, $scraper->site_name) !== false) {
                    $site_scraper = $scraper;
                }
            }

            if ($site_scraper === null) {
                throw new Exception("Can't get the scraper for app link");
            }

            (new AppSaver())->saveAppToWp($app_link, $site_scraper);
            echo 'successfully added app to wp';
        }catch (Exception $e) {
            echo "Something went wrong, exception message: {$e->getMessage()}";
        }

        wp_die();
    }

    // get only file info from sites and return info
    public function getSiteFileInfo(){
        try {
            $site_name = $_POST['site_name'];
            $page_url = $_POST['page_url'];

            // find need site scraper
            $site_scraper = null;
            foreach ($this->download_file_scrapers as $scraper) {
                if ($scraper->site_name == $site_name) {
                    $site_scraper = $scraper;
                    break;
                }
            }

            if (!$site_scraper) {
                echo "[]";
                wp_die();
            }

            $files = $scraper->getFileInfo($page_url);

            echo json_encode($files);
            wp_die();
        } catch (Exception $e) {
            echo "[]";
            wp_die();
        }
    }

    // download file from file info that user send to here
    public function downloadFileEndPoint() {
        try {
            $site_name = $_POST['site_name'];
            $file_info = json_decode(stripslashes($_POST['file_info']), true);

            // find need site scraper
            $site_scraper = null;
            foreach ($this->download_file_scrapers as $scraper) {
                if ($scraper->site_name == $site_name) {
                    $site_scraper = $scraper;
                    break;
                }
            }

            if (!$site_scraper) {
                echo "{}";
                wp_die();
            }

            $file_info = $scraper->downloadFile($file_info);
            
            echo json_encode($file_info);
            wp_die();
        }catch (Exception $e) {
            echo "{}";
            wp_die();
        }
    }
}
