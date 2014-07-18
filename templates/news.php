<div class="wrap">
    <div class="expresscurate_menu">
        <?php
        include(sprintf("%s/menu.php", dirname(__FILE__)));?>
    </div>
  <h2 class="expresscurate_displayNone">News</h2>
  <div class="expresscurate_news_container">
<?php
$feed = ExpressCurate_Settings::getCurationNews();
$limit = ExpressCurate_Settings::NEWS_FEED_COUNT;
if(count($feed)){
      for ($x = 0; $x < $limit; $x++) {
        $title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
        $link = $feed[$x]['link'];
        $description = $feed[$x]['desc'];
        $date = date('l F d, Y', strtotime($feed[$x]['date']));
        echo '<p><strong><a href="' . $link . '" title="' . $title . '" target="_blank">' . esc_attr($title) . '</a></strong><br />';
        echo '<small><em>Posted on ' . $date . '</em></small></p>';
        echo '<p>' . $description . '</p>';
      }
}
?>


  </div> 
</div>