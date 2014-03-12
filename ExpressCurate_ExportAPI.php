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
    
    if (current_user_can('edit_posts')) {
      $categories = get_categories(array("hide_empty" => 0));
      foreach ($categories as $i => $category) {
        if ($category->category_nicename != 'uncategorized') {
          $data["categories"][$i]["term_id"] = $category->term_id;
          $data["categories"][$i]["name"] = $category->name;
        }
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

    if ($current_user && $current_user->data && $current_user->data->user_login) {
      $response['username'] = $current_user->data->user_login;
    }
    echo json_encode($response);
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
    if ($data) {
      $domain = parse_url($data['url']);
      $domain = $domain['host'];
      $data['content'] .= '<div class="curated_from"><p>' . get_option('expresscurate_curated_text') . ' <a href = "' . $data['url'] . '">' . $domain . '</a><span class="expresscurated" data-curated-url="' . $data['url'] . '">&nbsp;</span></p></div>';
      if (isset($data['terms'])) {
        foreach ($data['terms'] as $i => $term) {
          $term_id = get_cat_ID($term);
          $data['terms'][$i] = $term_id;
        }
      }
      //var_dump($data['terms']); die;
      $post_id = $this->insert_post($data, $post_status);
      if ($post_id) {
        $result = json_encode(array('status' => "success", 'post_id' => $post_id, 'postUrl' => post_permalink($post_id), 'msg' => "Post saved."));
      } else {
        $result = json_encode(array('status' => "error", 'msg' => "Something went wrong!"));
      }
    } else {
      $result = json_encode(array('status' => "error", 'msg' => "Something went wrong!"));
    }
    echo $result;
    die;
  }

  private function insert_post($data, $post_status) {
    array_push($data['terms'], get_option('expresscurate_def_cat'));
    $details = array(
        'post_content' => str_replace("&nbsp;", " ", $data['content']),
        'post_author' => get_current_user_id(),
        'post_title' => $data['title'],
        'post_status' => $post_status,
        'post_category' => $data['terms']
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
    return $post_id;
  }

}

?>
