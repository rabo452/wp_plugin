<?php

require_once plugin_dir_path(__FILE__) . '/Base/ExternalDownloadScraper.php';
require_once plugin_dir_path(__FILE__) . '/../config.php';

class TechBigsScraper extends ExternalDownloadScraper {
    public $site_name = "techbigs.com";
    public $external_server_endpoint;

    function __construct() {
        global $SCRAPER_SERVER_URL;

        $this->download_server_endpoint = $SCRAPER_SERVER_URL . '/download-file-techbigs';
        $this->external_server_endpoint = $SCRAPER_SERVER_URL . "/get-files-techbigs";
    }
}