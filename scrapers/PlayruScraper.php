<?php

require_once plugin_dir_path(__FILE__) . '/../Classes/App.php';
require_once plugin_dir_path(__FILE__) . '/../Classes/Category.php';
require_once plugin_dir_path(__FILE__) . '/Base/ExternalScraper.php';
require_once plugin_dir_path(__FILE__) . '/../config.php';

class PlayruScraper extends ExternalScraper
{
    public $site_name = "5play.ru";
    public $categories_names = ['apps', 'games'];

    function __construct()
    {
        global $SCRAPER_SERVER_URL;
        $this->external_urls = [
            'base-app' => $SCRAPER_SERVER_URL . '/playru-get-base-app',
            'category' => $SCRAPER_SERVER_URL . '/playru-get-category',
            'full-app-info' => $SCRAPER_SERVER_URL . '/playru-download-app'
        ];
    }
}