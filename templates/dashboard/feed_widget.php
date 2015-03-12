<?php
$feedManager = new ExpressCurate_FeedManager();
?>
<div class="expresscurate_dashboard expresscurate_Styles expresscurate_dashboard_feed">

    <ul class="list">
        <?php
        $feeds = $feedManager->get_feed_list();
        if(!empty($feeds) && !empty($feeds['content'])){
            $content_list = array_slice($feeds['content'], 0, 5);
            $i = 0;
            foreach ($content_list as $key => $item) {
                ?>
                <li>
                    <a href="<?php echo $item['link'] ?>" target="_newtab"><?php echo $item['title'] ?></a>
                    <a href="<?php echo get_admin_url() ?>post-new.php?expresscurate_load_source=<?php echo base64_encode(urlencode($item['link'])); ?>&expresscurate_load_title=<?php echo urlencode($item['title']); ?>" class="curate">curate</a>
                </li>
                <?php $i++;
            }
        }else{
            echo '<label class="expresscurate_notDefined">There is no content suggestion.</label>';
        }
        ?>
    </ul>
    <?php  if(!empty($feeds) && !empty($feeds['content'])){ ?>
        <a class="settingsLink" href="admin.php?page=expresscurate_feed_list">More Content (<?php echo count($feeds['content']); ?>)</a>
    <?php  } ?>
</div>