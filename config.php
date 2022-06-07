<?php

// this server will scrape the data from sites and return it to wp
$SCRAPER_SERVER_URL = '';

$CURL_OPTIONS = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:47.0) Gecko/20100101 Firefox/47.0',
    CURLOPT_ENCODING => "",
    CURLOPT_VERBOSE => false
];