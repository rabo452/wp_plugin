<?php

require_once plugin_dir_path(__FILE__) . '/BaseDownloadSiteScraper.php';


# download file from page of another site, using another external server that do all work
class ExternalDownloadScraper extends BaseDownloadSiteScraper {
    # what server endpoint should use plugin to get info about file
    public $external_server_endpoint;
    # download server endpoint there user can store file 
    public $download_server_endpoint; 
    public $site_name = "";

    function getFileInfo(string $page_url): array {
        global $CURL_OPTIONS;

        if (strpos($page_url, $this->site_name) === false) {
            throw new Exception("not valid url for this site");
        }

        // copy object
        $local_curl_options = json_decode(json_encode($CURL_OPTIONS), true);
        $local_curl_options[CURLOPT_POST] = true;
        $local_curl_options[CURLOPT_POSTFIELDS] = ["page_url" => $page_url];

        $ch = curl_init($this->external_server_endpoint);
        curl_setopt_array($ch, $local_curl_options);
        
        try {
            $response = json_decode(curl_exec($ch), 1);
        }catch (Exception $e) {
            throw new Exception("external server returned not valid response!");
        }finally {
            curl_close($ch);
        }
        
        $required_keys = ["title", "file_url", "filename"];
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $response[0])) {
                throw new Exception("external server returned not valid response!");
            }
        }

        return $response;
    }

    # download file by file info
    function downloadFile(array $file_info): array {
        global $CURL_OPTIONS;

        // copy object
        $local_curl_options = json_decode(json_encode($CURL_OPTIONS), true);
        $local_curl_options[CURLOPT_POST] = true;
        $local_curl_options[CURLOPT_POSTFIELDS] = [
            "app_file_link" => $file_info['file_url'],
            "app_filename" => $file_info['filename'],
            "app_title" => $file_info['title'],
            "app_extension" => $file_info["file_extension"],
            "app_category" => $file_info["category"]
        ];

        $ch = curl_init($this->download_server_endpoint);
        curl_setopt_array($ch, $local_curl_options);

        try {
            $response = json_decode(curl_exec($ch), 1);
        } catch (Exception $e) {
            throw new Exception("external server returned not valid response!");
        } finally {
            curl_close($ch);
        }

        $required_keys = ["title", "file_download_link", "file_size"];
        foreach ($required_keys as $key) {
            if (!array_key_exists($key, $response)) {
                throw new Exception("external server returned not valid response!");
            }
        }

        return $response; 
    }
}