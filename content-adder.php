<?php
/*
  Plugin Name: content-filler
  Author: Sergey
  Requires PHP: 7.0+
  Description: This plugin written for appyn theme. this plugin get the information apps from (5play.ru, modroid.co and apkpure) sites and store for this site
*/

// this is start point of plugin
// include anothers scripts
require_once plugin_dir_path(__FILE__) . '/wordpress-functionality/ajax/AjaxHandler.php';
require_once plugin_dir_path(__FILE__) . '/wordpress-functionality/AdminPagesGenerator.php';
require_once plugin_dir_path(__FILE__) . '/wordpress-functionality/CronJob.php';
require_once plugin_dir_path(__FILE__) . '/wordpress-functionality/MetaBoxesGenerator.php';

require_once plugin_dir_path(__FILE__) . '/config.php';
require_once plugin_dir_path(__FILE__) . '/DataManipulator.php';

require_once plugin_dir_path(__FILE__) . '/scrapers/ApkpureScraper.php';
require_once plugin_dir_path(__FILE__) . '/scrapers/ModroidScraper.php';
require_once plugin_dir_path(__FILE__) . '/scrapers/PlayruScraper.php';
require_once plugin_dir_path(__FILE__) . '/scrapers/ApkdoneScraper.php';
require_once plugin_dir_path(__FILE__) . '/scrapers/TechBigsScraper.php';
require_once plugin_dir_path(__FILE__) . '/scrapers/ApkmodyScraper.php';

// scrapers for use in this plugin
$scrapers = [
  # new PlayruScraper(),
  # new ModroidScraper(),
  new ApkpureScraper()
];

$download_file_scrapers = [
  new ApkmodyScraper(),
  new TechBigsScraper(),
  new ApkdoneScraper()
];

// register jquery and another javascript scripts
// register css files
add_action('admin_enqueue_scripts', function() {
  wp_register_script('custom-jquery', 'https://code.jquery.com/jquery-3.6.0.min.js', false, null, true);

  wp_register_script('config-js', plugins_url('front-end/js/config.js', __FILE__), ['custom-jquery'], time());
  wp_register_script('menu-js', plugins_url('front-end/js/menu.js', __FILE__), ['custom-jquery'], time());
  wp_register_script('file-scraper-metabox-js', plugins_url('front-end/js/file-scraper-metabox.js', __FILE__), ['custom-jquery'], time());

  wp_register_style('config-css', plugins_url('front-end/css/config.css', __FILE__), [], time());
  wp_register_style('menu-css', plugins_url('front-end/css/menu.css', __FILE__), [], time());
  wp_register_style("file-scraper-metabox-css", plugins_url('front-end/css/file-scraper-metabox.css', __FILE__), [], time());
});

// avoid wordpress to check ssl while making request
// comment it if you're uploading the images from server that has ssl
add_filter( 'http_request_args', function ( $args ) {
    $args['reject_unsafe_urls'] = false;
    
    return $args;
});

// create for each scraper the page in admin menu
new AdminPagesGenerator($scrapers);
new AjaxHandler($scrapers, $download_file_scrapers);
$cronjob_obj = new CronJob($scrapers);

register_activation_hook(__FILE__, [$cronjob_obj, 'registerCronJob']);
register_deactivation_hook(__FILE__, [$cronjob_obj, 'deregisterCronJob']);

// add meta boxes in post editor so user can work with them
// these meta boxes scraping files from another the sites and return file info to user editor page
add_action("add_meta_boxes", function () {
  global $download_file_scrapers;

  foreach ($download_file_scrapers as $scraper) {
    add_meta_box(
      "file-scraper-metabox-{$scraper->site_name}",
      "file-scraper-metabox-{$scraper->site_name}",
      [new MetaBoxesGenerator($scraper), "generateMetaBoxDownloadScraper"],
      "post"
    );
  }
});