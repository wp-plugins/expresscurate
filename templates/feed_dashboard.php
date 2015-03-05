<?php
$feedManager = new ExpressCurate_FeedManager();
$feed_list = $feedManager->get_rss_list();
?>

<div class="expresscurate_feed_dashboard expresscurate_Styles wrap">
    <div class="expresscurate_headBorderBottom expresscurate_headerPart expresscurate_OpenSansRegular">
        <a href="admin.php?page=expresscurate&type=keywords" class="expresscurate_writeUs">Suggestions? <span>Submit here!</span></a>

        <h2>RSS Feeds</h2>

        <div class="pageDesc">
            Manage RSS feeds to customize the content that gets delivered to your ExpressCurate Content Feed.
        </div>
    </div>
    <div class="expresscurate_content_wrapper whiteWrap">
        <ul class="expresscurate_columnsName">
            <li class="mainTitle">RSS feeds</li>
            <li class="title expresscurate_floatRight"># of curated posts</li>
        </ul>
        <label class="expresscurate_displayNone expresscurate_notDefined">There is no enough data</label>
        <ul class="expresscurate_feedSettingsList">
            <?php
                if(!empty($feed_list)){
                    foreach ($feed_list as $url => $feed_url) { ?>
                        <li>
                            <a href="<?php echo $feed_url['feed_url'] ?>"
                               target="_newtab"><?php echo $feed_url['feed_url']; ?></a>
                            <span class="postsCount expresscurate_floatRight">
                                <?php echo $feed_url['post_count']; ?>
                                <input type="hidden" name="expresscurate_feed_url" value="<?php echo $url ?>"/>
                            </span>
                            <span class="close"></span>
                        </li>
                    <?php }
                }
            ?>
        </ul>
        <div class="addNewFeed">
            <label for="addFeed ">Add RSS feed</label>

            <div class="addFeed">
                <input id="addFeed" type="text" placeholder="Feed Address" class="expresscurate_disableInputStyle"/>
                <span class="expresscurate_preventTextSelection"><span></span></span>
            </div>
            <span class="errorMessage"></span>
            <div class="expresscurate_clear"></div>
        </div>
    </div>
    <script type="text/html" id="tmpl-rssfeedItem">
        <li>
            <a target="_newtab" href="{{data.feed_url}}">{{data.feed_url}}</a>
                        <span class="postsCount expresscurate_floatRight">{{data.post_count}}
                        <input type="hidden" name="expresscurate_feed_url" value="{{data.feed_url}}"/>
                        </span>
            <span class="close">&#215</span>
        </li>
    </script>
</div>