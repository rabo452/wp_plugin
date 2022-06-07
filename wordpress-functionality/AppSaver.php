<?php

require_once plugin_dir_path( __FILE__ ) . '/../Classes/App.php';
require_once plugin_dir_path( __FILE__ ) . '/../DataManipulator.php';
require_once plugin_dir_path( __FILE__ ) . '/FilterApps.php';

// save app to wordpress
class AppSaver {
    private $post_type = 'post';

    public function saveAppToWp($app_link, $site_scraper): void {
        // check if this app has already in wordpress
        $filter_obj = new FilterApps();
        $app = $site_scraper->getBasicApp($app_link);

        if ($filter_obj->hasAppInWp($app['title'], $app['version'])) {
            return;
        }

        $app = $site_scraper->getSiteApp($app_link);
		    $post_id = $filter_obj->getAnotherVersionApp($app->title);

        // wordpress hasn't this app before or
        // wordpress has this app but with another version (deprecated version)
        $post_id === null ? $this->createPost($app) : $this->updatePost($app, $post_id);
    }

    // this method create app that hasn't in wordpress
    public function createPost(App $app): void {
        $icon_src = $app->icon_image_src;

        $main_category_name = $app->main_category;
        $sub_category_name = $app->sub_category;
        $author = property_exists($app, 'author') ? $app->author : '';

        $taxonomy_data = $this->generateTaxonomies($main_category_name, $sub_category_name, $author);

        $main_category_id = $taxonomy_data['main_category_id'];
        $main_category_link = $taxonomy_data['main_category_link'];

        $sub_category_id = $taxonomy_data['sub_category_id'];
        $sub_category_link = $taxonomy_data['sub_category_link'];

        $author_page_link = $taxonomy_data['author_page_link'];
        $author_term_id = $taxonomy_data['author_term_id'];

        # args for generate post content
        $post_content_args = [
          'main_category_link' => $main_category_link,
          'sub_category_link' => $sub_category_link,
          'author_page_link' => $author_page_link
        ];
        $post_content = $this->generatePostContent($app, $post_content_args);

        $post_options = [
            'post_title' => $app->title,
            'post_content' => $post_content,
            'post_status' => (new DataManipulator())->IsPostStatusPublish() ? "publish" : "draft",
            'post_date' => date('Y-m-d H:i:s'),
            'post_author' => 1,
            'post_type' => $this->post_type,
            'post_category' => [$main_category_id, $sub_category_id]
        ];

        $post_id = wp_insert_post($post_options);
        if ($author_term_id) wp_set_post_terms($post_id, [$author_term_id], 'dev');

        // if in string isn't the "android" word, then add word
        if (strpos(strtolower($app->requirements), 'android') === false) {
          $app->requirements = "Android {$app->requirements}";
        }

        $app_info = [
          'app_status'          => 'new',
          'categoria_app'       => $main_category_name,
          'descripcion'	        => $post_content,
          'version'             => $app->version,
          'tamano'              =>  "{$app->file_size} MB",
          'fecha_actualizacion' => date('F d, Y'),
          'last_update'		     	=> date('F d, Y'),
          'requerimientos' 		  => $app->requirements,
          'desarrollador'       => $app->author,
          'consiguelo' 			    => $app->google_play_url
        ];

        if (property_exists($app, 'news')) {
          $app_info['novedades'] = $app->news;
        }

        // if there several download files
        // then show all
        if (property_exists($app, 'file_download_links')) {
          $download_info = [];

          foreach(array_reverse($app->file_download_links) as $index => $download_link) {
			$match = [];
			preg_match('/\/(.[^\/]+)$/', $download_link, $match);
			$block_text = str_replace('/', '', $match[0]);

            $download_info[] = [
              'link' => $download_link,
              'texto' => $block_text . " $app->file_size MB",
              "follow" => '1'
            ];
          }
        }else {
		  $match = [];
		  preg_match('/\/(.[^\/]+)$/', $app->file_download_link, $match);
		  $block_text = str_replace('/', '', $match[0]);

          $download_info = [[
              'link' => $app->file_download_link,
              'texto' => "{$block_text} {$app->file_size} MB",
              'follow' => '1'
            ]];
        }


        # app post meta
        update_post_meta($post_id, 'previous_version', []);
        update_post_meta($post_id, 'datos_imagenes', $app->images_src);

        if ($app->people_votes_count and $app->app_grade) {
          update_post_meta($post_id, 'new_rating_count', (int) ($app->people_votes_count * $app->app_grade));
          update_post_meta($post_id, 'new_rating_users', $app->people_votes_count);
          update_post_meta($post_id, 'new_rating_average', (int) $app->app_grade);
        }else {
          update_post_meta($post_id, 'new_rating_count', 0);
          update_post_meta($post_id, 'new_rating_users', 0);
          update_post_meta($post_id, 'new_rating_average', 0);
        }

        # set the youtube video for app
        if (property_exists($app, 'youtube_video_id')) {
          update_post_meta($post_id, 'datos_video', ['id' => $app->youtube_video_id]);
        }

        update_post_meta($post_id, 'datos_download', $download_info);
        update_post_meta($post_id, 'datos_informacion', $app_info);
        update_post_meta($post_id, '_app_title', $app->title);
        update_post_meta($post_id, '_app_versions', [$app->version]);

        $image_id = media_sideload_image($icon_src, 0, 'icon', 'id');
        if (!is_wp_error($image_id)) {
            set_post_thumbnail($post_id, $image_id);
        }
    }

    // app was created before, need to change already created post
    public function updatePost(App $app, $post_id): void {
    		$prev_versions = get_post_meta($post_id, 'previous_version', true);
    		$versions = get_post_meta($post_id, '_app_versions', true);
        $prev_app_data = get_post_meta($post_id, 'datos_informacion', true);
        $prev_download_file_info = get_post_meta($post_id, 'datos_download', true)[0]; # get the info from app file

        $main_category_name = $app->main_category;
        $sub_category_name = $app->sub_category;
        $author = property_exists($app, 'author') ? $app->author : '';

        $taxonomy_data = $this->generateTaxonomies($main_category_name, $sub_category_name, $author);

        $main_category_link = $taxonomy_data['main_category_link'];
        $sub_category_link = $taxonomy_data['sub_category_link'];

        $author_page_link = $taxonomy_data['author_page_link'];
        $author_term_id = $taxonomy_data['author_term_id'];


        # args for generate post content
        $post_content_args = [
          'main_category_link' => $main_category_link,
          'sub_category_link' => $sub_category_link,
          'author_page_link' => $author_page_link
        ];
        $post_content = $this->generatePostContent($app, $post_content_args);

        $post_options = [
            'post_content' => $post_content,
            'ID' => $post_id,
            'post_type' => $this->post_type,
            'post_status' => (new DataManipulator())->IsPostStatusPublish() ? "publish" : "draft"
        ];
        wp_update_post($post_options);
        if ($author_term_id) wp_set_post_terms($post_id, [$author_term_id], 'dev');

        // if there are several app files to download (for one version of app)
        // then don't need to show previous versions
        if (property_exists($app, 'file_download_links')) {
          $download_file_info = [];

          foreach (array_reverse($app->file_download_links) as $index => $download_link) {
      			$match = [];
      			preg_match('/\/(.[^\/]+)$/', $download_link, $match);
      			$block_text = str_replace('/', '', $match[0]);

            $download_file_info[] = [
              'link' => $download_link,
              'texto' => $block_text . " $app->file_size MB",
              "follow" => '1'
            ];
          }

          $prev_versions = [];
        }else {
          // if there is 1 app file to download, then prev app version file show in previous versions
          $prev_versions[] = [
            'download_link' => $prev_download_file_info['link'],
            'version' => $prev_app_data['version'],
            'size' => $prev_app_data['tamano'],
            'last_updated' => $prev_app_data['last_update']
          ];

		  if (count($prev_versions) >= 4) {
			  array_unshift($prev_versions);
		  }

		  $match = [];
		  preg_match('/\/(.[^\/]+)$/', $app->file_download_link, $match);
		  $block_text = str_replace('/', '', $match[0]);

          $download_file_info = [[
            'link' => $app->file_download_link,
            'texto' =>  $block_text . "{$app->file_size} MB",
            'follow' => '1'
          ]];
        }

        // if in string isn't the "android" word, then add word
        if (strpos(strtolower($app->requirements), 'android') === false) {
          $app->requirements = "Android {$app->requirements}";
        }

        $app_info = [
          'app_status'          => 'updated',
          'categoria_app'       => $app->main_category,
          'descripcion'	        => $post_content,
          'version'             => $app->version,
          'tamano'              =>  "{$app->file_size} MB",
          'fecha_actualizacion' => date('F d, Y'),
          'last_update'		     	=> date('F d, Y'),
          'requerimientos' 		  => $app->requirements,
          'desarrollador'       => $app->author,
          'consiguelo' 			    => $app->google_play_url ? $app->google_play_url : $prev_app_data['google_play_url']
        ];

        if (property_exists($app, 'news')) {
          $app_info['novedades'] = $app->news;
        }

        $versions[] = $app->version;

        if ($app->people_votes_count and $app->app_grade) {
          update_post_meta($post_id, 'new_rating_count', (int) ($app->people_votes_count * $app->app_grade));
          update_post_meta($post_id, 'new_rating_users', $app->people_votes_count);
          update_post_meta($post_id, 'new_rating_average', (int) $app->app_grade);
        }

        # set the youtube video for app
        if (property_exists($app, 'youtube_video_id')) {
          update_post_meta($post_id, 'datos_video', ['id' => $app->youtube_video_id]);
        }

        update_post_meta($post_id, 'previous_version', $prev_versions);
        update_post_meta($post_id, 'datos_imagenes', $app->images_src);
        update_post_meta($post_id, 'datos_download', $download_file_info);
        update_post_meta($post_id, 'datos_informacion', $app_info);
        update_post_meta($post_id, '_app_versions', $versions);
    }

    # generate post content as user wanted
    // by special tags in config page of plugin
    private function generatePostContent(App $app, array $post_content_args) {
        $desc_style = (new DataManipulator())->getDescriptionStyle();

        $search_values = [
            '{title}', '{author}', '{version}',
            '{requirements}', '{description}', '{google_play_url}',
            '{installs}', '{sub_category}', '{main_category}',
            '{sub_category_link}', '{main_category_link}', '{author_page_link}'
        ];
        $replace_values = [
            $app->title, $app->author, $app->version,
            $app->requirements, $app->description, $app->google_play_url,
            $app->download_counts, $app->sub_category, $app->main_category,
            $post_content_args['sub_category_link'], $post_content_args['main_category_link'],
            $post_content_args['author_page_link']
        ];

        return str_replace($search_values, $replace_values, $desc_style);
    }

    # create categories, terms
    # return array with category ids, pages links, author term
    private function generateTaxonomies($main_category_name, $sub_category_name, $author): array {
      $main_category_id = wp_create_category($main_category_name);
      $sub_category_id = wp_create_category($sub_category_name, $main_category_id);

      $main_category_link = get_category_link($main_category_id);
      $sub_category_link = get_category_link($sub_category_id);

      $author_page_link = '';
      $term_id = null;
      if ($author != '') {
        wp_insert_term($author, 'dev', ['slug' => $author]);
        $term_id = get_term_by('name', $author, 'dev')->term_id;
        $author_page_link = get_term_link($author, 'dev'); // link for author page in theme
      }

      return [
        'main_category_id' => $main_category_id,
        'sub_category_id' => $sub_category_id,
        'main_category_link' => $main_category_link,
        'sub_category_link' => $sub_category_link,
        'author_page_link' => $author_page_link,
        'author_term_id' => $term_id
      ];
    }
}
