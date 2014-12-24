<div class="wrap expresscurate_Styles">
    <div class="expresscurate_headBorderBottom expresscurate_OpenSansRegular">
        <a href="admin.php?page=expresscurate&type=keywords" class="expresscurate_writeUs">Suggestions? <span>Submit here!</span></a>
        <h2>News</h2>
        <!--<label>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc sagittis nulla lectus,</label>-->
    </div>
  <div class="expresscurate_news_container">
<?php
$feed = ExpressCurate_Actions::getCurationNews();
$limit = ExpressCurate_Actions::NEWS_FEED_COUNT;
if(count($feed)){
    ?>
      <ul>
      <?php for ($x = 0; $x < $limit; $x++) {
        $title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
        $link = $feed[$x]['link'];
        $description = $feed[$x]['desc'];
        $date = date('l F d, Y', strtotime($feed[$x]['date'])); ?>
          <li>
              <a class="title" href="<?php echo $link; ?>"><?php echo $title; ?></a>
              <p>Posted on   <?php echo human_time_diff( strtotime($date), current_time('timestamp') ) . ' ago'; ?></p>
              <span class="description"><?php echo $description; ?></span>
          </li>
      <?php } ?>
      </ul>
<?php } ?>

  </div> 
</div>