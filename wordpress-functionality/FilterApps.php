<?php

// filter is the class that delete apps that have already in wordpress
class FilterApps {
    private $post_type = "post";

    function __construct(){
        // get the apps (posts) metaboxes data in wordpress site
        $this->existen_apps = $this->getExistentsApps();
    }

    // delete apps from array that has in wordpress
    public function deleteExistentApps(array &$apps): void {
        // in apps array the instances of App class
        foreach($apps as $index => $app) {
            if ($this->hasAppInWp($app->title, $app->version)) {
                unset($apps[$index]);
            }
        }
    }

    // has app in wp with the same version
    public function hasAppInWp($title, $version): bool {
        $title = strtolower($title);
        $version = strtolower(str_replace(' ', '', $version));

        foreach($this->existen_apps as $existen_app) {
            if ($this->isIndetetyStrings($title, $existen_app['title']) and in_array($version, $existen_app['versions'])) {
                return true;
            }
        }

        return false;
    }

    // get app id with another version in wordpress
    // this checks after the method $this->hasAppInWp
    // for example the scraper got the newly version of app, so check if it's true
    // return id of post app that has previous version or null
    public function getAnotherVersionApp($title) {
        $title = strtolower($title);
        foreach($this->existen_apps as $app) {
            if ($this->isIndetetyStrings($title, $app['title'])) {
                return $app['id'];
            }
        }
        return null;
    }

    // get apps that has in wordpress currently
    private function getExistentsApps(): array {
        $apps = [];

        # get all apps in wordpress
        $post_count = (int) wp_count_posts($this->post_type)->publish;
        for ($i = 0; $i < ceil($post_count / 5000); $i++) {
            $query = new WP_Query(['post_type' => $this->post_type, 'offset' => ($i * 5000), 'posts_per_page' => 5000]);
            while ($query->have_posts()) {
                $query->the_post();

                $title = strtolower(get_post_meta(get_the_ID(), '_app_title', true));
                $versions = get_post_meta(get_the_ID(), '_app_versions', true);

                if (!$title or !$versions) {
                    continue;
                }

                for ($i = 0; $i < count($versions); $i++) {
                  $versions[$i] = strtolower(str_replace(' ', '', $versions[$i]));
                }

                $apps[] = ['title' => $title, 'versions' => $versions, 'id' => get_the_ID()];
            }
            wp_reset_postdata();
        }

        return $apps;
    }

    // check if string almost the same
    // count the indetety strings in procent
    public function isIndetetyStrings($string1, $string2): bool {
        $procent = 0;

        if (strlen($string1) !== strlen($string2)) {
            return false;
        }
        // check symbol by symbol the indetety of strings
        $len = strlen($string1);
        for ($i = 0; $i < $len; $i++) {
            if ($string1[$i] === $string2[$i]) {
                $procent += (1 / $len) * 100;
            }
        }

        if ($procent > 80) {
            return true;
        }

        return false;
    }
}
