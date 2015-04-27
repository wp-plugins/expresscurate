<?php
$feedManager = new ExpressCurate_FeedManager();
$bookmarks = $feedManager->get_bookmarks();
if (!empty($bookmarks)){
    $sorted_bookmarks = array_reverse($bookmarks);
    $sorted_bookmarks = array_slice($sorted_bookmarks, 0, 5);
}
$last_7_days = $feedManager->count_bookmarks_by_days($bookmarks, 7);
$bookmarks_count = count($bookmarks);

?>
<div class="expresscurate_dashboard expresscurate_Styles expresscurate_dashboard_bookmarks ">
    <div class="topPart">
        <div class="postCount">
            <p>Bookmarks</p>
            <span><?php echo $bookmarks_count;?></span>
        </div>
        <div class="postCount expresscurate_borderNone">
            <p>Last 7 days</p>
            <span><?php echo $last_7_days; ?></span>
        </div>
    </div>

    <ul class="list">
        <?php
        if (!empty($sorted_bookmarks)) {
            $i = 0;
            foreach ($sorted_bookmarks as $key => $item) {
                ?>
                <li>
                    <a href="<?php echo $item['link'] ?>" target="_newtab"><?php echo $item['title'] ?></a>
                    <span
                        class="time"><?php echo human_time_diff(strtotime($item['bookmark_date']), current_time('timestamp')) . ' ago'; ?></span>
                    <a href="<?php echo get_admin_url() ?>post-new.php?expresscurate_load_source=<?php echo base64_encode(urlencode($item['link'])); ?>&expresscurate_load_title=<?php echo urlencode($item['title']); ?>"
                       class="curate">curate</a>
                </li>
                <?php
                $i++;
            }
        } else { ?>
            <label class="expresscurate_notDefined">There is no defined bookmarks.</label>
        <?php }
        ?>
    </ul>
    <a class="settingsLink" href="admin.php?page=expresscurate_bookmarks">More Bookmarks</a>
</div>
