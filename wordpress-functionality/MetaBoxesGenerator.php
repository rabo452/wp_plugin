<?php 

require_once plugin_dir_path(__FILE__) . '../scrapers/Base/ExternalDownloadScraper.php';

// generate html for metabox
class MetaBoxesGenerator {
    function __construct(ExternalDownloadScraper $scraper){
        $this->scraper = $scraper;
    }

    # generate metabox html for download scraper
    public function generateMetaBoxDownloadScraper() {
        $scraper = $this->scraper;
        wp_enqueue_style("file-scraper-metabox-css");
        wp_enqueue_script("file-scraper-metabox-js");
        wp_localize_script("file-scraper-metabox-js", "global_variables", [
            'ajax-url' => admin_url('admin-ajax.php')
        ]);

        ?>
        <div class="scraper-container">
            <input type="text" class="scraper-url-input" site="<?php echo $scraper->site_name ?>" placeholder="type here link of site <?php echo $scraper->site_name; ?>"> 
            <input type="button" class="scraper-btn" site="<?php echo $scraper->site_name ?>" value="get file from the link of site">
            <div class="site-links" site="<?php echo $scraper->site_name ?>">
                <div class="link-block"></div>
            </div>
            <textarea class="scraper-output" site="<?php echo $scraper->site_name ?>" cols="30" rows="10" placeholder="output of scraper"></textarea>
        </div>
        <?php 
    }
}