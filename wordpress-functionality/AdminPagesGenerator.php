<?php

require_once plugin_dir_path( __FILE__ ) . '/../DataManipulator.php';
require_once plugin_dir_path( __FILE__ ) . '/FilterApps.php';

// this class generate the html pages for admin menu
class AdminPagesGenerator {
    private $scrapers = [];
    // for each scraper site create own page
    // type of the scraper is the BaseScraper
    function __construct(array $scrapers) {
        $this->scrapers = $scrapers;

        add_action('admin_menu', [$this, 'createAdminPages']);
    }

    public function createAdminPages(): void {
        add_menu_page('content-adder', 'content-adder',
                      'administrator', 'content-adder',
                       [$this, 'generateConfigPage']);

        foreach($this->scrapers as $scraper) {
            add_submenu_page('content-adder', $scraper->site_name . " scraper",
                             $scraper->site_name . " scraper", 'administrator',
                             $scraper->site_name, [new WrapPageGenerator($scraper, $this), 'generatePage']);
        }
    }

    public function generateSitePage($scraper): void {
        wp_enqueue_style('menu-css');
        wp_enqueue_script('menu-js');
        wp_localize_script('menu-js', 'global_variables', [
            'ajax-url' => admin_url('admin-ajax.php')
        ]);

        // scrape all categories in site
        $categories = [];

        foreach($scraper->categories_names as $category_name) {
            try {
                $category = $scraper->getSiteCategoryApps($category_name);
                (new FilterApps())->deleteExistentApps($category->apps);
                $categories[] = $category;
            }catch (Exception $e) {
                ?> Something went wrong with scraper <?php
                return;
            }
        }

        $this->generatePageHtml($categories, $scraper->site_name);
    }

    // generate page that user can choose which app he want to add to site
    // @array categories - array with Category objects
    private function generatePageHtml(array $categories, string $site_name): void {
        $this->createHeaderBlock($site_name);
        ?>
        <div class="categories-block">
            <?php
            // $category - the Category object
            foreach ($categories as $category) {
                $category_name = $category->name;
                $category_apps = $category->apps;

                $this->generateCategoryBlock($category_name, $category_apps);
            }
            ?>
        </div>
        <?php
    }

    private function createHeaderBlock(string $site_name): void {
    ?>
    <div class="action-block">
        <p> <h2><?php echo $site_name; ?></h2> </p>
        <div><input type="button" id="add-wp-btn" value="Add to wordpress"></div>
        <div><input type="button" id="select-categories-btn" value="Select all apps"></div>
        <div><input type="button" id="unselect-categories-btn" value="Unselect all site apps"></div>
        <div>Selected apps count: <v id='selected-apps-count'>0</v></div>
    </div>
    <?php
    }

    private function generateCategoryBlock(string $category_name, array $category_apps): void {
    ?>
    <p><h3><?php echo strtoupper($category_name); ?></h3></p>

    <div class="category-block">
        <?php
            // app is the instance of App object
            foreach($category_apps as $app) {
                ?>
                <div class="app-block">
                    <a href="<?php echo $app->app_link; ?>">
                        <div class="image"><img src="<?php echo $app->icon_image_src; ?>" alt=""></div>
                        <div class="title"><p><h3><?php echo $app->title; ?></h3></p></div>
                        <div class="version"><p><h3>version <?php echo $app->version; ?></h3></p></div>
                    </a>

                    <div class="select-btn">
                        <input type="button" value="Select this app" url="<?php echo $app->app_link; ?>" selected="false">
                    </div>
                </div>
                <?php
            }
        ?>
    </div>
    <?php
    }

    public function generateConfigPage(): void {
        $obj = new DataManipulator();
        $is_cronjob_turned = $obj->getCronJobData()['cronjob-start'];
        $post_desc_style = $obj->getDescriptionStyle();

        wp_enqueue_script('config-js');
        wp_enqueue_style('config-css');
        wp_localize_script('config-js', 'config_params', [
            'ajax-url' => admin_url( 'admin-ajax.php' ),
            'is-cronjob-turned' => $is_cronjob_turned,
            'post-desc-style' => $post_desc_style,
            'is-publish' => $obj->IsPostStatusPublish()
        ]);

        $this->generateConfigHtml();
    }

    private function generateConfigHtml(): void {
        ?>

        <h1>Config page</h1>

        <h2>Set description style of app:</h2>
        <textarea name="" id="app-desc-style-block" cols="60" rows="10"></textarea>
        <p>Generated text from script:</p>
        <p>
            <p>{google_play_url} - url of google play</p>
            <p>{requirements} - requirements for os to run the app</p>
            <p>{description} - description of app</p>
            <p>{title} - app title</p>
            <p>{version} - version of app</p>
            <p>{author} - author of app</p>
            <p>{installs} - how many users installed this app before</p>
            <p>{sub_category} - name of sub category of app</p>
            <p>{main_category} - name of main category of app</p>
            <p>{sub_category_link} - sub category page in wordpress</p>
            <p>{main_category_link} - main category page in wordpress</p>
            <p>{author_page_link} - author page in wordpress</p>
        </p>

        <div class="line"></div>

        <p>Turn on the cron job? (automatically adds the categories apps from sites) <input type="checkbox" id="cronjob-checkbox"> </p>

        <div class="line"></div>

        <p>Set post status for created apps as publish? (if true, scraped apps would be visible for users, if false only for admin) <input type="checkbox" id="is-publish"></p>

        <div class="save-block"><input type="button" id="save-btn" value="Save settings"></div>
        <?php
    }
}

// this class is wrapper for the generateSitePage func with scraper argument
class WrapPageGenerator {
    public $scraper;
    public $pages_generator_obj;

    function __construct($scraper, $pages_generator_obj){
        $this->scraper = $scraper;
        $this->pages_generator_obj = $pages_generator_obj;
    }

    public function generatePage(): void {
        $this->pages_generator_obj->generateSitePage($this->scraper);
    }
}
