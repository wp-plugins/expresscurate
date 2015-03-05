<?php
$keywords = new ExpressCurate_Keywords();
$keywordsArray = $keywords->getKeywords();
$keywordsProcessed = array();
if(!empty($keywordsArray)){
    foreach($keywordsArray as $keyword) {
        $keyword = trim($keyword);
        $keywordsProcessed[] = preg_replace('/\s+/i', '+', $keyword);
    }
    $query = implode(',', $keywordsProcessed);
}
?>

<div class="expresscurate_dashboard expresscurate_Styles expresscurate_keywords_related_topics_widget">
  <div>
      <?php if ($keywordsArray && sizeof($keywordsArray)!=0) {?>
              <script type="text/javascript" src="//www.google.com/trends/embed.js?hl=en-US&q=<?php echo $query; ?>&cmpt=q&tz&tz&content=1&cid=TIMESERIES_GRAPH_0&export=5&w=300&h=360"></script>
      <?php } else { ?>
          <label class="expresscurate_notDefined">Currently you don't have any defined keywords.  <a class="settingsLink" href="admin.php?page=expresscurate_keywords">Start adding now.</a></label>
      <?php } ?>
  </div>
  <a class="settingsLink" href="admin.php?page=expresscurate_keywords">Keywords Dashboard</a>
</div>
