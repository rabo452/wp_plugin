<?php 

# base download scraper - scraper that's getting only file from sites by url 
abstract class BaseDownloadSiteScraper {
    public $site_name;
    # return dict with title, file_download_link, file_size
    abstract public function getFileInfo(string $page_url): array; 
}