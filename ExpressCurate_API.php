<?php

require_once 'ExpressCurate_AjaxExportAPI.php';

/*
 * Returnes source urls of all posts if $post_id is not defined
 * @param int $post_id 
 * @return array
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
  $curated_urls = $exportApi->get_meta_values('expresscurate_link_', '', '', $post_id);
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
  $curated_urls = $exportApi->get_meta_values('expresscurate_link_', '', '', $post_id);
  return isset($curated_urls[0]) ? $curated_urls[0] : null;
}

?>
