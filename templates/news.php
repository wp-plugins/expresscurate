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

