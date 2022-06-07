<?php 

require_once plugin_dir_path( __FILE__ ) . '/../Classes/App.php';
require_once plugin_dir_path( __FILE__ ) . '/../Classes/Category.php';
require_once plugin_dir_path( __FILE__ ) . '/Base/ExternalScraper.php';
require_once plugin_dir_path( __FILE__ ) . '/../config.php';

class ApkpureScraper extends ExternalScraper {
    public $site_name = "apkpure.com";
    public $categories_names = ['app', 'game'];

    function __construct(){
        global $SCRAPER_SERVER_URL;
        $this->external_urls = [
            'base-app' => $SCRAPER_SERVER_URL . '/apkpure-get-base-app',
            'category' => $SCRAPER_SERVER_URL . '/apkpure-get-category',
            'full-app-info' => $SCRAPER_SERVER_URL . '/apkpure-download-app'
        ];
    }
    
}