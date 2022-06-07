<?php

require_once plugin_dir_path(__FILE__) . '/Base/ExternalDownloadScraper.php';
require_once plugin_dir_path(__FILE__) . '/../config.php';

class ApkmodyScraper extends ExternalDownloadScraper
{
    public $site_name = "apkmody.io";
    public $external_server_endpoint;

    function __construct()
    {
        global $SCRAPER_SERVER_URL;

        $this->download_server_endpoint = $SCRAPER_SERVER_URL . '/download-file-apkmody';
        $this->external_server_endpoint = $SCRAPER_SERVER_URL . "/get-files-apkmody";
    }
}
