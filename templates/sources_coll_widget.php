<?php
global $post, $pagenow;
$items = array();
$is_json = true;
if ($pagenow == 'post-new.php' && $_POST && isset($_POST['expresscurate_bookmarks_curate_data'])) {
  $items = json_decode(stripslashes($_POST['expresscurate_bookmarks_curate_data']), true);
}else if($pagenow == 'post-new.php' && isset($_REQUEST['expresscurate_load_source'])){
  $domain = parse_url($_REQUEST['expresscurate_load_source']);
  $items[0]['link'] = $_REQUEST['expresscurate_load_source'];
  $items[0]['domain'] = $domain['host'];
  $items[0]['title'] = urldecode($_REQUEST['expresscurate_load_title']);
  $is_json = false;
} else {
  $items = get_post_meta($post->ID, 'expresscurate_curated_data', true);
  $is_json = false;
  if(!$items){
    $items = array();
  }
}
?>
<div class="expresscurate_sources_coll_widget expresscurate_Styles">
  <ul>
    <?php foreach ($items as $key => $item) {
     
      ?>
      <li class="list">
        <textarea name="expresscurate_sources[<?php echo $key ?>]" class="expresscurate_displayNone">  <?php echo ($is_json ? esc_attr(esc_attr($item)) : json_encode($item)); ?> </textarea>
        <?php
        if ($is_json) {
          $item = json_decode($item, true);
        }
        ?>
          <span class="title"><span><?php echo $item['title']; ?></span></span>

        <div class="hover">
          <a class="curate expresscurate_curate" >Curate</a><a class="delete">
            Delete</a>
          <span class="tooltip">
            <p>Collected from</p>
            <a href="<?php echo $item['link']?>"><?php echo $item['domain']?></a>
          </span>
        </div>
      </li>
      <?php
    }
    ?>
    <!--add new-->
    <li class="addSource">
      <span class="text">+ Add new source</span>
      <div>
        <input class="expresscurate_disableInputStyle" placeholder="Add new source" type="text">
        <span><span></span></span>
      </div>
    </li>
  </ul>
</div>
