<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_ContentManager {

    private static $instance;

    function __construct() {
        // action shall be added from actions controller
    }

    public static function getInstance() {
        if ( ! ( self::$instance instanceof self ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

      public function check_get_url() {
        $file_get_enabled = preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen'));
        if (!$file_get_enabled && !is_callable('curl_init')) {
          return false;
        } else {
          return true;
        }
      }

      public function getArticle($url = false, $echo = true) {
        if(!ExpressCurate_HtmlParser::supportsDownload()) {
          echo json_encode(array('status' => 'error', 'error' => 'You should activate either curl extension or allow_url_fopen setting.'));
          die;
        } else {

          if (!$url) {
            $url = $this->_post('expresscurate_source', '');
          }
          $url = preg_replace( '/\s+/', '', $url );
          if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $url = 'http://' . $url;
          }
          if (strlen($url) < 1) {
            $data = array('status' => 'error', 'error' => 'Please enter the URL');
            if ($echo) {
              echo json_encode($data);
              die();
            } else {
              return $data;
            }
          }
          if (filter_var($url, FILTER_VALIDATE_URL) === FALSE || !preg_match('#(http|https)\:\/\/[aA-zZ0-9\.]+\.[aA-zZ\.]+#',$url)) {
            $data = array('status' => 'error', 'error' => 'Please enter a valid URL');
            if ($echo) {
              echo json_encode($data);
              die();
            } else {
              return $data;
            }
          }
          if ($this->_get('check', '') == 1) {
            $data_check = array();
            $curated_urls = $this->get_meta_values('_expresscurate_link_', $url);
            if (isset($curated_urls[0]) && isset($curated_urls[0]['meta_value'])) {
              $data_check["status"] = "notification";
              $data_check["msg"] = "Warning! This page has been curated before";
            }
            if ($echo) {
              echo json_encode($data_check);
              die();
            } else {
              return $data_check;
            }
          } else {
            $HtmlParser = new ExpressCurate_HtmlParser($url);
            if(isset($_REQUEST['cloned']) && $_REQUEST['cloned']==1){
              $article = $HtmlParser->getCloneContents();
            }else{
              $article = $HtmlParser->getContents();
            }
            if ($echo == true) {
              echo json_encode($article);
              die;
            } else {
              return $article;
            }
          }
        }
      }

      public function get_meta_values($key = '', $url = '', $type = 'post', $status = 'publish') {
        global $wpdb;
        if (empty($key))
          return;
        $metas = $wpdb->get_results("
            SELECT p.ID, pm.meta_value FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key LIKE '{$key}%'
            AND pm.meta_value = '{$url}'
            AND p.post_status = 'publish'
            AND p.post_type = 'post'", ARRAY_A);

        return $metas;
      }

      private function _post($data, $default) {
        return isset($_POST[$data]) ? $_POST[$data] : $default;
      }

      private function _get($data, $default) {
        return isset($_GET[$data]) ? $_GET[$data] : $default;
      }
}