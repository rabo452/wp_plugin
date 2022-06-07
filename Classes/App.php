<?php 

// this class contains info about app
// the additional info will be suited
class App {
    // necessary properties for app
    // app_link - is the app link in the website that script parsed app
    function __construct(string $title, string $version, 
                         string $icon_image_src, string $author,
                         string $app_link){
        $this->$title = $title;
        $this->version = $version;
        $this->icon_image_src = $icon_image_src;
        $this->app_link = $app_link;
        $this->author = $author;
    }

    // set aditional properties
    function __set($property, $value) {
        $this->$property = $value;
    }
}