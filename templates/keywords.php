<?php
$keywords = new ExpressCurate_Keywords();
$words_stats = $keywords->getKeywordStats(true);
$keywords_stats = $keywords->getKeywordStats();
?>
<div class="expresscurate_keywords_settings expresscurate_Styles wrap">
    <input type="hidden" id="expresscurate_plugin_dir" value="<?php echo plugin_dir_url(__FILE__); ?>"/>
    <label>Keywords Dashboard</label>
    <a href="admin.php?page=expresscurate&type=keywords" class="expresscurate_writeUs">Suggestions?
        <span>Submit here!</span></a>
    <br/>
    <label class="pageDescription pageDesc">Monitor and manage keyword utilization in your blog.</label>

    <div class="expresscurate_clear"></div>
    <div class="whiteWrap">

        <div class="keywordsPart">
            <div>
                <label class="blockTitle expresscurate_color_lightGreen">Keywords</label>
                <label class="colTitle expresscurate_margin30 expresscurate_floatRight"># of posts</label>
                <label class="colTitle expresscurate_margin15 expresscurate_floatRight">in content</label>
                <label class="colTitle expresscurate_margin30 expresscurate_floatRight">in title</label>

                <div class="expresscurate_clear"></div>
                <label class="expresscurate_displayNone expresscurate_notDefined">Add your keywords in the box below.</label>
                <ul>
                    <?php
                    foreach ($keywords_stats as $word => $stat) {
                        unset($post_content['words'][$word]);
                        ?>
                        <li>
                            <span class="color expresscurate_<?php echo $stat['color']; ?>"></span>
                            <span class="word"><?php echo $word ?> </span>
                            <a class="addPost"
                               href="<?php echo admin_url(); ?>post-new.php?post_title=<?php echo urlencode("TODO: define post title using '" . $word . "'") ?>&content=<?php echo urlencode("TODO: write content using '" . $word . "'"); ?>&expresscurate_keyword=<?php echo $word; ?>">+
                                add post</a>
                            <span class="expresscurate_floatRight postCount"><?php echo $stat['posts_count']; ?></span>
                            <span class="remove hover expresscurate_floatRight"></span>
                            <span class="count expresscurate_floatRight"><?php echo $stat['percent']; ?>%</span>
                            <span class="inTitle expresscurate_floatRight"><?php echo $stat['title']; ?> %</span>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="legend expresscurate_floatLeft">
                <span id="blue" class="expresscurate_blue"></span>
                <label for="blue"><3%<span>Usage is low</span></label>
                <span id="green" class="expresscurate_green"></span>
                <label for="green">3-5% <span>Usage is good</span></label>
                <span id="red" class="expresscurate_red"></span>
                <label for="red">>5%<span>Too many occurances</span></label>
            </div>
            <a href="https://www.google.com/alerts" class="googleAlert expresscurateLink expresscurate_floatRight">Create Google Alert</a>
            <div class="expresscurate_clear"></div>
            <div class="addNewKeyword">
                <label for="addKeyword">Add new keyword</label>
                <textarea id="expresscurate_defined_tags" class="expresscurate_displayNone"
                          name="expresscurate_defined_tags"><?php echo get_option('expresscurate_defined_tags', '') ?> </textarea>

                <div class="addKeywords">
                    <input id="addKeyword" type="text" placeholder="Add Keywords"
                           class="expresscurate_disableInputStyle"/>
                    <span><span></span></span>
                    <ul class="suggestion">
                    </ul>
                </div>
                <div class="expresscurate_clear"></div>
                <p><span>Recommendation: </span>avoid using stop words like: </br>
                    ain’t, all, is, at, on, which, etc. </p>
                <!--<a href="#">Manage stopwords</a>-->
            </div>
        </div>
        <div class="usedWordsPart">
            <label class="blockTitle">Top words<!--<span class="titleTooltip">Most frequently used words</span>--></label>

            <div class="expresscurate_clear"></div>
            <div class="expresscurate_topWordsDesc">List of most frequently used words in your blog.  Pick the “+” sign to turn them into target keywords.</div>

            <label class="colTitle expresscurate_margin35 expresscurate_floatRight">in content</label>
            <label class="colTitle expresscurate_margin35 expresscurate_floatRight">in title</label>
            <label class="expresscurate_displayNone expresscurate_notDefined">There is no top words data at this time.  Please check again later.</label>
            <ul>
                <?php
                $i = 0;
                foreach ($words_stats as $word => $stat) {
                    if ($stat['percent'] >= 1 && $i < 15) {
                        ?>
                        <li>
                            <span class="color expresscurate_<?php echo $stat['color'] ?>"></span>

                            <div class="wordWrap">
                                <span
                                    class="word"><?php echo $word; ?></span>
                            </div>
                            <span class="inTitle"><?php echo $stat['title']; ?> %</span>
                            <span class="add hover expresscurate_floatRight"><!--<span>make keyword</span>--></span>
                            <span class="count expresscurate_floatRight"><?php echo $stat['percent']; ?>%</span>
                        </li>
                    <?php
                    }
                    $i++;
                }
                ?>
            </ul>
        </div>
        <div class="expresscurate_clear"></div>
    </div>
</div>