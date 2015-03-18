<?php
$feedManager = new ExpressCurate_FeedManager();
$bookmarks = $feedManager->get_bookmarks();
if (!empty($bookmarks)) {
    $sorted_bookmarks = array_reverse($bookmarks);
}
?>
<input id="adminUrl" type="hidden" value="<?php echo get_admin_url(); ?>"/>
<div
    class="expresscurate_bookmarks expresscurate_Styles wrap <?php if (get_option('expresscurate_bookmark_layout', '') == 'single') {
        echo 'expresscurate_singleColumn';
    } ?>">
    <div class="expresscurate_headBorderBottom expresscurate_OpenSansRegular">
        <a href="admin.php?page=expresscurate&type=keywords" class="expresscurate_writeUs">Suggestions? <span>Submit here!</span></a>

        <h2>Bookmarks</h2>

        <div class="pageDesc">
            List of your bookmarked web pages. You can start a post by picking a bookmarked article (or multiple
            articles) and clicking on the curate button.
        </div>
        <div class="controlsWrap">
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
                <li class="layout expresscurate_floatRight">
                <span class="tooltip"><?php if (get_option('expresscurate_bookmark_layout', '') == 'single') {
                        echo 'view as grid';
                    } else {
                        echo 'view as list';
                    } ?></span>
                </li>
                <div class="expresscurate_clear"></div>
            </ul>
        </div>
    </div>

    <label class="expresscurate_displayNone expresscurate_notDefined">Currently you have no bookmarked web
        pages.</label>
    <ul class="expresscurate_bookmarkBoxes expresscurate_masonryWrap">
        <div class="addNewBookmark expresscurate_masonryItem grid-sizer">
            <label for="addBookmark ">Add new Bookmark</label>

            <div class="addBookmark">
                <input id="addBookmark" type="text" placeholder="URL" class="expresscurate_disableInputStyle"/>
                <span class="expresscurate_preventTextSelection"><span></span></span>
            </div>
            <span class="errorMessage"></span>

            <div class="expresscurate_clear"></div>
            <p><span></span></p>
        </div>
        <?php
        if (!empty($sorted_bookmarks)) {
            $i = 0;
            foreach ($sorted_bookmarks as $key => $item) {
                ?>
                <li class="expresscurate_preventTextSelection expresscurate_masonryItem">
                    <input id="uniqueId_<?php echo $i ?>" class="checkInput" type="checkbox"/>
                    <label for="uniqueId_<?php echo $i ?>"></label>
                <textarea
                    class="expresscurate_displayNone expresscurate_bookmarkData"><?php echo json_encode($item); ?></textarea>

                    <ul class="keywords">
                        <?php if (!empty($item['media']['videos'])) {
                            echo '<li class="media videos"><span class="tooltip">Video(s):  '.$item["media"]["videos"].'</span></li>';
                        }
                        if (!empty($item['media']['images'])) {
                            echo '<li class="media images"><span class="tooltip">Image(s):  '.$item["media"]["images"].'</span></li>';
                        }
                        if (!empty($item[' keywords'])) { ?>
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
                            <?php }
                        } ?>
                    </ul>

                    <a class="postTitle" href="<?php echo $item['link'] ?>"
                       target="_newtab"><?php echo $item['title'] ?></a><br/>
                    <a class="url" href="<?php echo $item['link'] ?>"><?php echo $item['domain'] ?></a>
                    <!--<span class="curatedBy">/<?php /*echo $item['curated'] ? 'curated by' : 'author'; */ ?> <span><?php /*echo $item['author']; */ ?></span> /</span>-->
                    <span class="curatedBy">/ by <span><?php echo $item['user']; ?></span> /</span>
                <span
                    class="time"><?php echo human_time_diff(strtotime($item['bookmark_date']), current_time('timestamp')) . ' ago'; ?></span>

                    <div class="comment">
                        <label class="<?php if ($item['comment']) {
                            echo 'active';
                        } ?>"
                               for="comment__<?php echo $i ?>"><?php echo $item['comment'] ? $item['comment'] : 'add comment'; ?></label>
                        <input type="text" class="expresscurate_disableInputStyle expresscurate_displayNone"
                               id="comment__<?php echo $i ?>" value="<?php echo $item['comment'] ?>">
                        <span class="expresscurate_displayNone">&#215</span>
                    </div>
                    <ul class="controls expresscurate_preventTextSelection">
                        <li class="curate"><a
                                href="<?php echo esc_url(get_admin_url() . "post-new.php?expresscurate_load_source=" . base64_encode(urlencode($item['link'])) . "&expresscurate_load_title=" . urlencode($item['title'])); ?>">Curate</a>
                        </li>
                        <li class="separator">-</li>
                        <li class="copyURL">Copy URL</li>
                        <li class="separator">-</li>
                        <li class="hide">Delete</li>
                    </ul>
                    <div class="expresscurate_clear"></div>
                    <!--<span class="label label_<?php /*echo $item['type'];*/ ?>"><?php /*echo $item['type'];*/ ?></span>-->
                </li>
                <?php
                $i++;
            }
        }
        ?>

    </ul>
    <form method="POST" action="<?php echo get_admin_url() ?>post-new.php#expresscurate_sources_collection"
          id="expresscurate_bookmarks_curate">
        <textarea name="expresscurate_bookmarks_curate_data" id="expresscurate_bookmarks_curate_data"
                  class="expresscurate_displayNone"></textarea>
        <input type="hidden" name="expresscurate_load_sources" value="1"/>
    </form>
    <script type="text/html" id="tmpl-bookmarksItem">
        <li class="expresscurate_preventTextSelection expresscurate_masonryItem">
            <input id="uniqueId_{{data.id}}" class="checkInput" type="checkbox"/>
            <label for="uniqueId_{{data.id}}" class="expresscurate_preventTextSelection"></label>
            <ul class="keywords">
                <# if (data.media.videos) { #>
                    <li class="media videos"><span class="tooltip">{{data.media.videos}}</span></li>
                <# } #>
                <# if (data.media.images) { #>
                    <li class="media images"><span class="tooltip">{{data.media.images}}</span></li>
                <# } #>
            </ul>
            <a class="postTitle" href="{{data.link}}" target="_newtab">{{data.title}}</a><br/>
            <a class="url" href="{{data.link}}" target="_newtab">{{data.domain}}</a>
            <span class="curatedBy">/ by <span>{{data.user}}</span> /</span>
            <span class="time">Just now</span>

            <div class="comment">
                <label class="" for="uniqueId">add comment</label>
                <input type="text" class="expresscurate_disableInputStyle expresscurate_displayNone" id="uniqueId">
                <span class="expresscurate_displayNone">&#215</span>
            </div>
            <ul class="controls expresscurate_preventTextSelection">
                <li><a class="curate" href="post-new.php?expresscurate_load_source={{data.curateLink}}">Curate</a></li>
                <li class="separator">-</li>
                <li class="copyURL">Copy URL</li>
                <li class="separator">-</li>
                <li class="hide">Delete</li>
            </ul>
            <div class="expresscurate_clear"></div>
            <!--<span class="label label_{{data.type}}">{{data.type}}</span>-->
        </li>
    </script>
</div>