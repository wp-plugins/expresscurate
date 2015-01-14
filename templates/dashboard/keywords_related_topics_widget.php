<?php
$keywords = new ExpressCurate_Keywords();
$keywordsArray = $keywords->getKeywords();

$keywordsProcessed = array();
foreach($keywordsArray as $keyword) {
    $keyword = trim($keyword);
    $keywordsProcessed[] = preg_replace('/\s+/i', '+', $keyword);
}

$query = implode(',', $keywordsProcessed);

?>

<div class="expresscurate_dashboard expresscurate_Styles dashboard_widget_keywords_related_topics">
  <div>
      <?php if (sizeof($keywordsArray) != 0) {?>
              <script type="text/javascript" src="//www.google.com/trends/embed.js?hl=en-US&q=<?php echo $query; ?>&cmpt=q&tz&tz&content=1&cid=TOP_ENTITIES_0_0&export=5"></script>
  
      <?php } else { ?>
          <label class="expresscurate_notDefined">Currently you don't have any defined keywords.  <a class="settingsLink" href="admin.php?page=expresscurate_keywords">Start adding now.</a></label>
      <?php } ?>
  </div>
  <a class="settingsLink" href="admin.php?page=expresscurate_keywords">Keywords Dashboard</a>
</div>

