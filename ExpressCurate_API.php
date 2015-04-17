<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

function expresscurate_the_source_urls($post_id = null) {
  if(!$post_id){
    global $post;
    $post_id = $post->ID;
    if(!$post_id){
      return false;
    }
  }
  $exportApi = new ExpressCurate_AjaxExportAPI();
  $curated_urls = $exportApi->get_meta_values('_expresscurate_link_', '', '', $post_id);
  return $curated_urls;
}

/*
 * Returns the first source url of the last post if $post_id is not defined
 * @param int $post_id
 * @return array
 */

function expresscurate_the_source_url($post_id = null) {
  if(!$post_id){
    global $post;
    $post_id = $post->ID;
    if(!$post_id){
      return false;
    }
  }
  $exportApi = new ExpressCurate_AjaxExportAPI();
  $curated_urls = $exportApi->get_meta_values('_expresscurate_link_', '', '', $post_id);
  return isset($curated_urls[0]) ? $curated_urls[0] : null;
}

?>
