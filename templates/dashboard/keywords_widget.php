<?php
$keywords = new ExpressCurate_Keywords();
$keywords_stats = $keywords->getKeywordStats();
?>

<div class="expresscurate_dashboard">
  <div>
      <div class="dashboardMessage expresscurate_displayNone">
          <span>Currently you don't have any defined keywords.  <a class="settingsLink"
                                                                                    href="admin.php?page=expresscurate_keywords">Start adding now.</a></span>
      </div>
  <?php
  foreach ($keywords_stats as $word => $stat) {
    ?>
    <div class="expresscurate_background_wrap ">
      <div title="<?php echo $word ?>" class="statisticsTitle expresscurate_<?php echo $stat['color']; ?>"><span><?php echo $word ?></span></div>
      <div title="Occurance in Title: <?php echo $stat['title']; ?>%" class="statistics borderRight">
        <div  class="center">title <br /><span><?php echo $stat['title']; ?>%</span></div>
      </div>
      <div title="Occurance in Content: <?php echo $stat['percent']; ?>%" class="statistics borderRight">
        <div>content<span><?php echo $stat['percent']; ?>%</span></div>
      </div>
      <div title="<?php echo $stat['posts_count']; ?> post(s) with <?php echo $word ?> keyword" class="statistics">
        <div>posts<span><?php echo $stat['posts_count']; ?></span></div>
      </div>
    </div>
  <?php } ?>
  </div>
  <a class="settingsLink" href="admin.php?page=expresscurate_keywords">Keywords Dashboard</a>
</div>
