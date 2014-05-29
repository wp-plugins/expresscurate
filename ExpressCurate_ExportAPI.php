<?php

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_ExportAPI {

  public function get_terms() {
    $data = array();
    $data["categories"] = array();
    $data["keywords"] = array();
    $data["featured_image"] = 0;
    if (current_user_can('edit_posts')) {
      $categories = get_categories(array("hide_empty" => 0));
      foreach ($categories as $i => $category) {
        if ($category->category_nicename != 'uncategorized') {
          $data["categories"][$i]["term_id"] = $category->term_id;
          $data["categories"][$i]["name"] = $category->name;
        }
      }
      $defined_tags = get_option("expresscurate_defined_tags", '');
      if ($defined_tags) {
        $defined_tags = explode(",", $defined_tags);
        foreach ($defined_tags as $tag) {
          $data["keywords"][] = trim($tag);
        }
      }
      if (get_option('expresscurate_featured', '')) {
        $data["featured_image"] = get_option('expresscurate_featured', '');
      }
    }

    //return $data;
    echo json_encode($data);
    die(); // this is required to return a proper result
  }

  public function check_auth() {
    $response = array();
    $response['logged_in'] = true;
    $response['can_edit_post'] = current_user_can('edit_posts');
    $current_user = wp_get_current_user();
    if ($current_user && $current_user->data && $current_user->data->user_login) {
      $response['username'] = $current_user->data->user_login;
    }
    echo json_encode($response);
    die();
  }

  public function check_images() {
    $data = $_REQUEST;
    $options = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36'));
    $context = stream_context_create($options);
    if (!$data['img_url'] && !$data['img_url2']) {
      $data_check = array('status' => "error", 'msg' => "Data is empty!");
    } else {
      $img = @file_get_contents($data['img_url'], false, $context);
      if (!$img) {
        $img = @file_get_contents($data['img_url2'], false, $context);
      }
      if ($img) {
        if ($http_response_header[0] == "HTTP/1.1 200 OK") {
          $data_check = array('status' => 'success', 'statusCode' => 200);
        } else if ($http_response_header[0] == "HTTP/1.1 403 Forbidden") {
          $data_check = array('status' => 'fail', 'statusCode' => 403);
        } else {
          $data_check = array('status' => 'fail', 'statusCode' => $http_response_header[0]);
        }
      } else {
        $data_check = array('status' => "error", 'msg' => "Images not found!");
      }
    }
    echo json_encode($data_check);
    die();
  }

  public function download_images() {
    $data = $_REQUEST;

    if (!$data['images']) {
      $result = array('status' => "error", 'error' => "Data is empty!");
    } else {
      $downloaded_images = array();
      $upload_dir = wp_upload_dir();
      $images = $data['images'];
      $post_id = $data['post_id'];

      if (wp_mkdir_p($upload_dir['path'])) {
        $this->delete_dir($upload_dir['path'] . '/expresscurate_tmp/');
        mkdir($upload_dir['path'] . '/expresscurate_tmp/', 0777);
        mkdir($upload_dir['path'] . '/expresscurate_tmp/' . $post_id, 0777);
      } else {
        $this->delete_dir($upload_dir['basedir'] . '/expresscurate_tmp/');
        mkdir($upload_dir['basedir'] . '/expresscurate_tmp/', 0777);
        mkdir($upload_dir['basedir'] . '/expresscurate_tmp/' . $post_id, 0777);
      }
      $options = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36'));
      $context = stream_context_create($options);
      if (count($images) > 0 && is_writable($upload_dir['path'])) {
        for ($i = 0; $i < count($images); $i++) {
          $image = strtok($images[$i], '?');
          $image_data = file_get_contents($images[$i], false, $context);
          $filename[$i] = basename($image);
          if (wp_mkdir_p($upload_dir['path'])) {
            $file[$i] = $upload_dir['path'] . '/expresscurate_tmp/' . $post_id . '/' . $filename[$i];
          } else {
            $file[$i] = $upload_dir['basedir'] . '/expresscurate_tmp/' . $post_id . '/' . $filename[$i];
          }
          if (file_put_contents($file[$i], $image_data)) {
            $file[$i] = substr($file[$i], ($pos = strpos($file[$i], '/wp-content')) !== false ? $pos + 1 : 0);
            $downloaded_images[] = site_url() . '/' . $file[$i];
          }
        }
        $result = array('status' => 'success', 'images' => $downloaded_images);
      } else {
        $result = array('status' => 'error', 'error' => 'Upload dir is not writable');
      }
    }
    echo json_encode($result);
    die();
  }

  public function check_source() {
    $data_check = array();
    $data = $_REQUEST;
    if (!$data['url']) {
      $data_check = array('status' => "error", 'msg' => "Data is empty!");
    }
    $curated_urls = $this->get_meta_values('expresscurate_link_', $data['url']);
    if (isset($curated_urls[0]) && isset($curated_urls[0]['meta_value'])) {
      $data_check["status"] = "notification";
      $data_check["msg"] = "This page is already curated!";
      $data_check['permalink'] = get_permalink($curated_urls[0]["ID"]);
    }
    echo json_encode($data_check);
    die();
  }

  public function save_post() {
    if (!current_user_can('edit_posts')) {
      $result = json_encode(array('status' => "error", 'msg' => __('You do not have sufficient permissions to access this page.')));
      echo $result;
      die();
    }

    $result = false;
    $data = $_REQUEST;
    $post_status = get_option('expresscurate_post_status', '') ? get_option('expresscurate_post_status', '') : 'draft';
    if (isset($data['url'])) {
      $domain = parse_url($data['url']);
      $domain = $domain['host'];
      $data['content'] .= '<div class="curated_from"><p>' . get_option('expresscurate_curated_text') . ' <a href = "' . $data['url'] . '">' . $domain . '</a><span class="expresscurated" data-curated-url="' . $data['url'] . '">&nbsp;</span></p></div>';
      if (isset($data['terms'])) {
        foreach ($data['terms'] as $i => $term) {
          $term_id = get_cat_ID($term);
          $data['terms'][$i] = $term_id;
        }
      }
      $post_id = $this->insert_post($data, $post_status);
      if ($post_id) {
        $result = json_encode(array('status' => "success", 'post_status' => $post_status, 'post_id' => $post_id, 'postUrl' => post_permalink($post_id), 'msg' => "Post saved as " . $post_status . "."));
      } else {
        $result = json_encode(array('status' => "error", 'msg' => "Something went wrong!"));
      }
    } else {
      $result = json_encode(array('status' => "error", 'msg' => "Data is emty!"));
    }
    echo $result;
    die;
  }

  private function insert_post($data, $post_status) {
    $post_cats = array();
    if (!isset($data['terms']) || !count($data['terms'])) {
      $post_cats[] = get_option('expresscurate_def_cat');
    } else {
      $post_cats = $data['terms'];
    }

    $details = array(
        'post_content' => str_replace("&nbsp;", " ", $data['content']),
        'post_author' => get_current_user_id(),
        'post_title' => $data['title'],
        'post_status' => $post_status,
        'post_category' => $post_cats,
        'post_type' => get_option('expresscurate_def_post_type', 'post')
    );

    $post_id = wp_insert_post($details);
    $meta_key = "expresscurate_chrome";
    $meta_value = 1;
    add_post_meta($post_id, $meta_key, $meta_value);
    if (isset($data['keywords']) && $data['keywords']) {
      add_post_meta($post_id, '_expresscurate_keywords', $data['keywords']);
    }
    if (isset($data['description']) && $data['description']) {
      add_post_meta($post_id, '_expresscurate_description', $data['description']);
    }
    if ($post_status == 'draft' && get_option('expresscurate_publish', '') == 1) {
      add_post_meta($post_id, '_expresscurate_smart_publish', 1);
    }

    return $post_id;
  }

  private function get_meta_values($key = '', $url = '', $type = 'post') {
    global $wpdb;
    if (empty($key))
      return;
    $metas = $wpdb->get_results("
        SELECT p.ID, p.guid, pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key LIKE '{$key}%'
        AND pm.meta_value = '{$url}'
        AND p.post_type = 'post'", ARRAY_A);
    return $metas;
  }

  public function delete_dir($dirPath) {
    if (!is_dir($dirPath)) {
      return;
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
      $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
      if (is_dir($file)) {
        $this->delete_dir($file);
      } else {
        unlink($file);
      }
    }
    rmdir($dirPath);
  }

}

?>
