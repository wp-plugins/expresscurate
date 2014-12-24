<?php
$feedManager = new ExpressCurate_FeedManager();
$curated_links_rss = $feedManager->get_curated_links();
?>
<div class="expresscurate_topSources expresscurate_Styles wrap">
  <div class="expresscurate_headBorderBottom expresscurate_OpenSansRegular">
    <a href="admin.php?page=expresscurate&type=keywords" class="expresscurate_writeUs">Suggestions? <span>Submit here!</span></a>
    <h2>Top Sources</h2>
      <label class="pageDesc">Top sources where you have curated from.</label>
  </div>
  <div class="expresscurate_content_wrapper">
    <?php
    if (count($curated_links_rss['links'])) {
      ?>
      <ul class="expresscurate_columnsName">
        <li class="mainTitle">Sources</li>
        <li class="title expresscurate_floatRight">rss subscription status</li>
        <li class="title expresscurate_floatRight expresscurate_marginRight30"># of curated posts</li>
      </ul>
      <ul class="expresscurate_URL">
        <?php
        foreach ($curated_links_rss['links'] as $key => $top_link) {
          $tooltip_msg = 'Subscribe';
          if ($top_link['feed_options']['feed_status'] == "rssStatusYes") {
            $tooltip_msg = 'Subscribed';
          } elseif ($top_link['feed_options']['feed_status'] == "rssStatusNo") {
              $tooltip_msg = ' N/A';
          }
          ?>
          <li>
            <h3 class="expresscurate_topCuratedURL expresscurate_floatLeft"><?php echo $key ?></h3>
            <span class="rssStatus expresscurate_floatRight <?php echo $top_link['feed_options']['feed_status'] ?> expresscurate_floatRight">rss
                <?php if($tooltip_msg){ ?>
                <span class="tooltip"><?php echo $tooltip_msg;?></span>
              <?php } ?>
            </span>
            <span class="postsCount expresscurate_floatRight"><?php echo count($top_link['post_ids']) ?></span>
            <input type="hidden" name="expresscurate_feed_url" value="<?php echo $top_link['feed_options']['feed_url'] ?>" />
            <!--classes for rss status: rssStatusYes | rssStatusNo | rssStatusAdd -->
              <div class="expresscurate_clear"></div>
          </li>
        <?php } ?>
      </ul>
    <?php } else {
      ?>
      <span class="expresscurate_notDefined">
        No Curated Post. <a href="<?php echo admin_url(); ?>post-new.php">Curate New Post Now</a>.
      </span>
    <?php }
    ?>
  </div>
  <!---->
</div>