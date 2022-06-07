<?php 

// save data into additional-data folder
// it's saving data for cron job and post description format that settings in plugin config page
// and this class get the data from additional-data
// in the description style file stores the generated post description
// isPublish - make post status as publish or not
class DataManipulator {
    private $folder_name = 'config-data';
    private $cronjob_file_name = 'cronjob.txt';
    private $description_style_file_name = 'desc-style.txt';
    private $dir_path;

    // create folder and files if it doesn't
    function __construct(){
        $this->dir_path = plugin_dir_path(__FILE__) . "/{$this->folder_name}";

        if (!file_exists($this->dir_path)) {
            mkdir($this->dir_path);
        }

        if (!file_exists($this->dir_path . "/{$this->description_style_file_name}")) {
            $f = fopen($this->dir_path . "/{$this->description_style_file_name}", 'w+');
            fwrite($f, 'start template of description of app , modify it...');
            fclose($f);
        }

        if (!file_exists($this->dir_path . "/{$this->cronjob_file_name}")) {
            $f = fopen($this->dir_path . "/{$this->cronjob_file_name}", 'w+');
            // start value for cron job
            fwrite($f, json_encode(['cronjob-start' => false, 'apps' => [], 'isPublish' => true]));
            fclose($f);
        }
    }
    
    public function getCronJobData(): array {
        $file_path = $this->dir_path . "/{$this->cronjob_file_name}";
        try {
            $f = fopen($file_path, 'r+');
            $json_data = json_decode(fread($f, filesize($file_path)), true);
            fclose($f);

            return $json_data;
        }catch (Exception $e) {
            $default_value = ['cronjob-start' => false, 'apps' => [], 'isPublish' => true];
            
            $f = fopen($file_path, 'w+');
            fwrite($f, json_encode($default_value));
            fclose($f);

            return $default_value;
        }
    }

    public function setCronJobData(array $cronjob_data): void {
        $file_path = $this->dir_path . "/{$this->cronjob_file_name}";

        $f = fopen($file_path, 'w+');
        fwrite($f, json_encode($cronjob_data));
        fclose($f);
    }

    public function getDescriptionStyle(): string {
        $file_path = $this->dir_path . "/{$this->description_style_file_name}";

        $f = fopen($file_path, 'r+');
        $desc_style = fread($f, filesize($file_path));
        fclose($f);

        return $desc_style;
    }

    public function setDescriptionStyle(string $desc_style): void {
        $file_path = $this->dir_path . "/{$this->description_style_file_name}";

        $f = fopen($file_path, 'w+');
        fwrite($f, $desc_style);
        fclose($f);
    }

    public function IsPostStatusPublish(): bool {
        $cronjob_data = $this->getCronJobData();
        return $cronjob_data['isPublish'];
    }
}