<?php
$feedManager = new ExpressCurate_FeedManager();
$bookmarks = $feedManager->get_bookmarks();

?>
<div class="expresscurate_bookmarks expresscurate_Styles wrap">
  <div class="expresscurate_headBorderBottom expresscurate_OpenSansRegular">
    <a href="admin.php?page=expresscurate&type=keywords" class="expresscurate_writeUs">Suggestions? <span>Submit here!</span></a>
    <h2>Bookmarks</h2>
    <div class="pageDesc">
        List of your bookmarked web pages.  You can start a post by picking a bookmarked article (or multiple articles) and clicking on the curate button.
    </div>
    <ul class="bookmarkListControls expresscurate_preventTextSelection expresscurate_controls expresscurate_displayNone">
      <li class="check">
        <span class="tooltip">select / deselect</span>
      </li>
        <li class="remove expresscurate_floatRight">
            <span class="tooltip">delete</span>
        </li>
      <li class="quotes expresscurate_floatRight">
        <span class="tooltip">curate</span>
      </li>
      <div class="expresscurate_clear"></div>
    </ul>
  </div>
  <label class="expresscurate_displayNone expresscurate_notDefined">Currently you have no bookmarked web pages.</label>
  <ul class="expresscurate_bookmarkBoxes expresscurate_masonryWrap">
      <div class="addNewBookmark expresscurate_masonryItem grid-sizer">
          <label for="addBookmark ">Add new Bookmark</label>
          <div class="addBookmark">
              <input id="addBookmark" type="text" placeholder="URL" class="expresscurate_disableInputStyle"/>
              <span class="expresscurate_preventTextSelection"><span></span></span>
          </div>
          <div class="expresscurate_clear"></div>
          <p><span></span></p>
      </div>
      <?php
    $i = 0;
    foreach ($bookmarks as $key => $item) {
      ?>
      <li class="expresscurate_preventTextSelection expresscurate_masonryItem">
        <input id="uniqueId_<?php echo $i ?>" class="checkInput" type="checkbox"/>
        <label for="uniqueId_<?php echo $i ?>"></label>
        <textarea class="expresscurate_displayNone expresscurate_bookmarkData"><?php echo json_encode($item); ?></textarea>
        <a class="postTitle" href="<?php echo $item['link'] ?>" target="_newtab"><?php echo $item['title'] ?></a><br/>
        <a class="url" href="<?php echo $item['link'] ?>"><?php echo $item['domain'] ?></a>
        <!--<span class="curatedBy">/<?php /*echo $item['curated'] ? 'curated by' : 'author'; */?> <span><?php /*echo $item['author']; */?></span> /</span>-->
        <span class="curatedBy">/ by <span><?php echo $item['user']; ?></span> /</span>
          <span class="time"><?php echo human_time_diff( strtotime($item['bookmark_date']), current_time('timestamp') ) . ' ago'; ?></span>
          <?php if (count($item['keywords'])) { ?>
              <ul class="keywords">
                  <?php foreach ($item['keywords'] as $keyword => $stats) {
                      if ($stats['percent'] * 100 < 3) {
                          $color = 'blue';
                      } else if ($stats['percent'] * 100 > 5) {
                          $color = 'red';
                      } else {
                          $color = 'green';
                      }
                      ?>
                      <li class="<?php echo $color ?>"><?php echo $keyword; ?>
                          <span class="tooltip">
                              <div class="<?php echo $color ?>">Keyword match</div>
                    <span class="inTitle">title<p class=""><?php echo $stats['title'] ?></p></span><!--inTitle yes|no-->
                    <span class="inContent">content<p><?php echo $stats['percent']*100 ?>%</p></span>
                  </span>
                      </li>
                  <?php } ?>
              </ul>
          <?php } ?>
        <div class="comment">
          <label class="<?php if ($item['comment']) {
        echo 'active';
      } ?>" for="comment__<?php echo $i ?>"><?php echo $item['comment'] ? $item['comment'] : 'add comment'; ?></label>
          <input type="text" class="expresscurate_disableInputStyle" id="comment__<?php echo $i ?>" value="<?php echo $item['comment'] ?>">
          <span>&#215</span>
        </div>
        <ul class="controls expresscurate_preventTextSelection">
          <li class="curate"><a href="<?php echo get_admin_url() ?>post-new.php?expresscurate_load_source=<?php echo urlencode($item['link']); ?>&expresscurate_load_title=<?php echo urlencode($item['title']); ?>">Curate</a></li>
          <li class="separator" >-</li>
          <li class="copyURL">Copy URL</li>
          <li class="separator">-</li>
          <li class="hide">Delete</li>
        </ul>
        <div class="expresscurate_clear"></div>
        <span class="label label_<?php echo $item['type'];?>"><?php echo $item['type'];?></span>
      </li>
      <?php
      $i++;
    }
    ?>

  </ul>
  <form method ="POST" action="<?php echo get_admin_url() ?>post-new.php#expresscurate_sources_collection" id="expresscurate_bookmarks_curate">
    <textarea name="expresscurate_bookmarks_curate_data" id="expresscurate_bookmarks_curate_data" class="expresscurate_displayNone"></textarea>
    <input type="hidden" name="expresscurate_load_sources" value="1" />
  </form>
</div>