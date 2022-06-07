<?php

require_once plugin_dir_path( __FILE__ ) . '/../DataManipulator.php';
require_once plugin_dir_path( __FILE__ ) . '/AppSaver.php';
require_once plugin_dir_path( __FILE__ ) . '/FilterApps.php';


// this class working with wp cronjob
// if user setted up cronjob in config menu of plugin
// then this functionality will work
// it's adding the apps to wordpress without user
class CronJob {
    private $action_name = 'cronjob_add_app'; # cronjob action name
    
    # setting up cronjob
    function __construct($scrapers) {
        $this->scrapers = $scrapers;

        // set custom interval for cronjob
        add_filter('cron_schedules', function ($intervals) {
            $intervals['minute_interval'] = ['interval' => 60];
            return $intervals;
        });

        add_action($this->action_name, [$this, 'cronjobFunc']);
    }

    public function registerCronJob() {
        wp_clear_scheduled_hook($this->action_name);
        wp_schedule_event(time(), 'minute_interval', $this->action_name);
    }

    public function deregisterCronJob() {
        wp_clear_scheduled_hook($this->action_name);
    }

    // this function will work every time when cronjob works
    public function cronjobFunc(): void {
        // those files need to add because the wp cron can't upload these files
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-config.php');
        require_once(ABSPATH . 'wp-includes/wp-db.php');
        require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
        require_once(ABSPATH . 'wp-includes/class-wp-query.php');

        $cronjob_data = (new DataManipulator())->getCronJobData();

        // user didn't turn on the cronjob
        if ($cronjob_data['cronjob-start'] === false) {
            return;
        }

        // if apps to scrape are finished
        // then get from every site scraper categories apps and save it to config file
        if (count($cronjob_data['apps']) === 0) {
            $this->setNewCronjobApps();
            return;
        }

        // get the first one app link to scrape it
        $app_link = array_shift($cronjob_data['apps']);
        // save apps value to file
        (new DataManipulator())->setCronJobData($cronjob_data);

        // from link get the site scraper that should scrape this link
        $site_scraper = null;
        foreach ($this->scrapers as $scraper) {
            if (strpos($app_link, $scraper->site_name) !== false) {
                $site_scraper = $scraper;
            }
        }

        if ($site_scraper === null) {
            return;
        }

        (new AppSaver())->saveAppToWp($app_link, $site_scraper);
    }

    // get from each scraper the categories apps and save it
    private function setNewCronjobApps(): void {
        $app_links = []; // save only app link here
        
        foreach($this->scrapers as $scraper) {
            $categories = $this->getFilteredCategories($scraper);

            foreach($categories as $category) {
                // $app - is the instance of App class
                foreach($category->apps as $app) {
                    $app_links[] = $app->app_link;
                }
            }
        }
		
		$cronjob_data = (new DataManipulator())->getCronJobData();
		$cronjob_data['cronjob-start'] = true;
		$cronjob_data['apps'] = $app_links;
        (new DataManipulator())->setCronJobData($cronjob_data);
    }

    private function getFilteredCategories($scraper): array {
        $categories = [];
        $filter_obj = new FilterApps();

        foreach ($scraper->categories_names as $category_name) {
            try {
                $category = $scraper->getSiteCategoryApps($category_name);
                $filter_obj->deleteExistentApps($category->apps);
                $categories[] = $category;
            } catch (Exception $e) {
                return [];
            }
        }

        return $categories;
    }
}