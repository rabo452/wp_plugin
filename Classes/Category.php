<?php 

// category class 
class Category {
    public $name;
    public $apps;

    function __construct(string $name, array $apps = []){
        $this->name = $name;
        $this->apps = $apps;
    }
}