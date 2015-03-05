<?php
$posts_list = array();
$smart_publish = new ExpressCurate_SmartPublish();
$posts = $smart_publish->get_posts_for_publish();
$smart_publish->publish_event();
if (isset($posts['posts'])) {
  $posts_list = array_slice($posts['posts'], 0, 5);
}
?>

<div class="expresscurate_dashboard expresscurate_Styles expresscurate_dashboard_smartPublishing">
  <div class="topPart">
    <div class="postCount">
      <p>Available posts</p>
      <span><?php echo count($posts['posts']); ?></span>
    </div>
    <div class="timeToPublish">
      <p>Next publishing in</p>
      <span class="countdown">-- : -- : --</span>
      <span class="target_date expresscurate_displayNone"><?php echo $posts['next_post_date'] ?></span>
      <span class="current_date expresscurate_displayNone"><?php echo str_replace(' ', ' ', date('Y/m/d H:i:s'));?></span>
    </div>
  </div>

  <ul class="list">
    <?php
    if (!empty($posts_list)){
      foreach ($posts_list as $i => $post) {
        ?>
        <li>
          <a target="_blank" href="<?php echo $post->guid ?>"><?php echo get_the_title($post->ID); ?></a>
          <span class="time"> <?php echo date('H:i', round(strtotime($posts['next_post_date']) + (60 * 60 * ($i) * get_option('expresscurate_hours_interval')))) ?></span>
          <a class="publish" href="<?php echo get_edit_post_link($post->ID); ?>">publish</a>
        </li>

        <?php
      }
    } else {
    ?>
       <label class="expresscurate_notDefined">There is no post to publish.</label>
    <?php } ?>

  </ul>
        <a class="settingsLink" href="admin.php?page=expresscurate_settings">Settings</a>
        <a class="settingsLink" href="edit.php?post_status=draft&post_type=post">Drafted Posts</a>
</div>
