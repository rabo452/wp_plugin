<?php 

require_once plugin_dir_path(__FILE__) . '/Base/ExternalDownloadScraper.php';
require_once plugin_dir_path(__FILE__) . '/../config.php';

class ApkdoneScraper extends ExternalDownloadScraper {
    public $site_name = "apkdone.com";
    public $external_server_endpoint;

    function __construct(){
        global $SCRAPER_SERVER_URL;

        $this->download_server_endpoint = $SCRAPER_SERVER_URL . '/download-file-apkdone';
        $this->external_server_endpoint = $SCRAPER_SERVER_URL . "/get-files-apkdone";
    }
}