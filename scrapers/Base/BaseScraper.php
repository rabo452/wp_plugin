<?php

require_once plugin_dir_path(__FILE__) . '/../../Classes/App.php';
require_once plugin_dir_path(__FILE__) . '/../../Classes/Category.php';

// this is the base class for every scraper site, that will be used for 
abstract class BaseScraper {
    public $site_name = "";
    // categories names for site  
    public $categories_names = [];

    // get apps info from site category
    abstract public function getSiteCategoryApps($category_name): Category;
    // get full info about site app 
    abstract public function getSiteApp(string $app_link): App;
    // get title and version of site app
    abstract public function getBasicApp($app_url): array;

    // from info create app obj
    protected function createAppObj(array $app_info): App {
        $app = new App($app_info['title'], $app_info['version'], 
                       $app_info['icon_image_src'], $app_info['author'],
                       $app_info['page_url']);
        
        foreach($app_info as $property => $value) {
            $app->$property = $value;
        }

        return $app;
    }
}