<?php

require_once plugin_dir_path(__FILE__) . '/../../Classes/App.php';
require_once plugin_dir_path(__FILE__) . '/../../Classes/Category.php';
require_once plugin_dir_path( __FILE__ ) . '/../../config.php';
require_once plugin_dir_path( __FILE__ ) . '/BaseScraper.php';

// this external scraper doesn't parse the sites himself
// this parser sends the request to another server that would scrape site
// and return the information to this scraper
class ExternalScraper extends BaseScraper {
    public $site_name = "";
    public $categories_names = [];
    // urls points for external server that would parse need part of site
    protected $external_urls = [
        'base-app' => '', // get only title and version of app
        'category' => '',
        'full-app-info' => ''
    ];

    public function getSiteCategoryApps($category_name): Category
    {
        global $CURL_OPTIONS;

        // copy object
        $local_curl_options = json_decode(json_encode($CURL_OPTIONS), true);
        $local_curl_options[CURLOPT_POST] = true;
        $local_curl_options[CURLOPT_POSTFIELDS] = ['category_name' => $category_name];

        $ch = curl_init($this->external_urls['category']);
        curl_setopt_array($ch, $local_curl_options);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            throw new Exception("Server didn't responsed");
        }

        // if there isn't the json response
        // then it'd catch the error in the high level
        $apps = json_decode($response, true);
        $apps_obj = []; // array that contains App objects

        foreach ($apps as $app) {
            $app['author'] = '';
            $apps_obj[] = $this->createAppObj($app);
        }

        return new Category($category_name, $apps_obj);
    }

    public function getSiteApp(string $app_link): App
    {
        global $CURL_OPTIONS;

        // copy object
        $local_curl_options = json_decode(json_encode($CURL_OPTIONS), true);
        $local_curl_options[CURLOPT_POST] = true;
        $local_curl_options[CURLOPT_POSTFIELDS] = ['app_link' => $app_link];

        $ch = curl_init($this->external_urls['full-app-info']);
        curl_setopt_array($ch, $local_curl_options);
        
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            throw new Exception("Server didn't responsed");
        }

        $app = json_decode($response, true);
        $app['page_url'] = '';

        return $this->createAppObj($app);
    }

    public function getBasicApp($app_link): array
    {
        global $CURL_OPTIONS;

        // copy object
        $local_curl_options = json_decode(json_encode($CURL_OPTIONS), true);
        $local_curl_options[CURLOPT_POST] = true;
        $local_curl_options[CURLOPT_POSTFIELDS] = ['app_link' => $app_link];

        $ch = curl_init($this->external_urls['base-app']);
        curl_setopt_array($ch, $local_curl_options);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        if (!$response) {
            throw new Exception("Server didn't responsed");
        }

        $app = json_decode($response, true);
        return $app;
    }

}