<?php
$content_list = array();
$feedManager = new ExpressCurate_FeedManager();
$content_list = $feedManager->get_feed_list();
?>
<div class="expresscurate_feed_list expresscurate_Styles wrap">
    <div class="expresscurate_headBorderBottom expresscurate_OpenSansRegular">
        <a href="admin.php?page=expresscurate&type=keywords" class="expresscurate_writeUs">Suggestions? <span>Submit here!</span></a>

        <h2>Content Feed</h2>

        <div class="pageDesc">
            Content Feed brings content from your RSS feeds directly into ExpressCurate, providing you a convenient
            starting point
            for writing your curated posts. Pick an article (or multiple articles) and click on the curate button to
            create a post.
        </div>
        <ul class="feedListControls expresscurate_preventTextSelection expresscurate_controls expresscurate_displayNone">
            <li class="check">
                <span class="tooltip">select / deselect</span>
            </li>
            <li class="remove expresscurate_floatRight">
                <span class="tooltip">delete</span>
            </li>
            <li class="bookmark expresscurate_floatRight">
                <span class="tooltip">bookmark</span>
            </li>
            <li class="quotes expresscurate_floatRight">
                <span class="tooltip">curate</span>
            </li>
            <div class="expresscurate_clear"></div>
        </ul>
    </div>
    <div class="expresscurate_clear"></div>

    <?php
    if (is_array($content_list['content']) && count($content_list['content'])) {
        $i = 0;
        ?>
        <ul class="expresscurate_feedBoxes"><?php
        foreach ($content_list['content'] as $key => $item) {
            ?>
            <li class="expresscurate_preventTextSelection">
                <input id="uniqueId_<?php echo $i; ?>" class="checkInput" type="checkbox"/>
                <label for="uniqueId_<?php echo $i; ?>"></label>
                <textarea
                    class="expresscurate_displayNone expresscurate_feedData"><?php echo json_encode($item); ?></textarea>
                <a class="postTitle" href="<?php echo $item['link'] ?>"
                   target="_newtab"><?php echo $item['title'] ?></a><br/>
                <a class="url" href="<?php echo $item['link'] ?>"><?php echo $item['domain'] ?></a>
          <span class="curatedBy">/<?php echo $item['curated'] ? 'curated by' : 'author'; ?>
              <span><?php echo $item['author']; ?></span> /</span>
                <span
                    class="time"><?php echo human_time_diff(strtotime($item['date']), current_time('timestamp')) . ' ago'; ?></span></br>
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
                    <span class="inContent">content<p><?php echo $stats['percent'] * 100 ?>%</p></span>
                  </span>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>

                <ul class="controls expresscurate_preventTextSelection">
                    <li class="curate"><a
                            href="<?php echo get_admin_url() ?>post-new.php?expresscurate_load_source=<?php echo urlencode($item['link']); ?>&expresscurate_load_title=<?php echo urlencode($item['title']); ?>">Curate</a>
                    </li>
                    <li class="separator">-</li>
                    <li class="bookmark">Bookmark</li>
                    <li class="separator">-</li>
                    <li class="hide">Delete</li>
                </ul>
                <div class="expresscurate_clear"></div>
                <span class="label label_<?php echo $item['type'] ?>"><?php echo $item['type'] ?></span>
            </li>

            <?php $i++;
        }
        ?></ul><?php
    }?>
        <label class="expresscurate_notDefined expresscurate_displayNone">Your content feed is empty. Configure <a href="admin.php?page=expresscurate_feeds">RSS feeds</a> to
            start.</label>

    <form method="POST" action="<?php echo get_admin_url() ?>post-new.php#expresscurate_sources_collection"
          id="expresscurate_bookmarks_curate">
        <textarea name="expresscurate_bookmarks_curate_data" id="expresscurate_bookmarks_curate_data"
                  class="expresscurate_displayNone"></textarea>
        <input type="hidden" name="expresscurate_load_sources" value="1"/>
    </form>
</div>