<?php
$keywords = new ExpressCurate_Keywords();
$keywords_stats = $keywords->getKeywordStats();
$words_stats = $keywords->getKeywordStats(true);

?>
<div class="expresscurate_keywords_settings wrap">
    <div class="expresscurate_menu">
        <?php
        include(sprintf("%s/menu.php", dirname(__FILE__)));?>
    </div>
    <input type="hidden" id="expresscurate_admin_url" value="<?php echo admin_url(); ?>"/>
    <input type="hidden" id="expresscurate_plugin_dir" value="<?php echo plugin_dir_url(__FILE__); ?>"/>
    <label>Keywords Dashboard</label>
    <a href="admin.php?page=expresscurate&type=keywords" class="writeUs">Suggestions? <span>Submit here!</span></a>
    <div class="expresscurate_clear"></div>
    <div class="keywordsPart">
        <div class="keywordsWrap">
        <label class="blockTitle expresscurate_color_lightGreen">Keywords</label>
        <label class="colTitle expresscurate_margin10 expresscurate_floatRight"># of posts</label>
        <label class="colTitle expresscurate_margin15 expresscurate_floatRight">in content</label>
        <label class="colTitle expresscurate_margin15 expresscurate_floatRight">in title</label>

        <div class="expresscurate_clear"></div>
        <label class="notDefined">There is no defined keywords.</label>
        <ul>
            <?php
            foreach ($keywords_stats as $word => $stat) {
                unset($post_content['words'][$word]);
                ?>
                <li>
                    <span class="color expresscurate_<?php echo $stat['color']; ?>"></span>
                    <span class="word"><?php echo $word ?> </span>
                    <a class="expresscurate_displayNone addPost"
                       href="<?php echo admin_url(); ?>post-new.php?post_title=<?php echo urlencode("TODO: define post title using " . $word) ?>&content=<?php echo urlencode("TODO: write content using " . $word); ?>&expresscurate_keyword=<?php echo $word; ?>">+
                        add post</a>
                    <span class="expresscurate_floatRight postCount"><?php echo $stat['posts_count']; ?></span>
                    <span class="remove hover expresscurate_floatRight expresscurate_displayNone">&#215</span>
                    <span class="count expresscurate_floatRight"><?php echo $stat['percent']; ?>%</span>
                    <span class="inTitle expresscurate_floatRight"><?php echo $stat['title']; ?> %</span>
                </li>
            <?php } ?>
        </ul>
        </div>
        <div class="legend">
            <span id="blue" class="expresscurate_blue"></span>
            <label for="blue"><3%<span>Usage is low</span></label>
            <span id="green" class="expresscurate_green"></span>
            <label for="green">3-5% <span>Usage is good</span></label>
            <span id="red" class="expresscurate_red"></span>
            <label for="red">>5%<span>Too many occurances</span></label>
        </div>
        <div class="addNewKeyword">
            <label for="addKeyword">Add new keyword</label>
            <textarea id="expresscurate_defined_tags" class="expresscurate_displayNone"
                      name="expresscurate_defined_tags"><?php echo get_option('expresscurate_defined_tags', '') ?> </textarea>

            <div class="addKeywords">
                <input id="addKeyword" type="text" placeholder="Add Keywords" class="disableInputStyle"/>
                <span class="">&#43</span>
            </div>
            <div class="expresscurate_clear"></div>
            <p><span>Recommendation: </span>avoid using stopwords like: </br>
                afterwards, again, against, ain’t, all, allow, allows, almost, alone, along, already, also, although,
                always, am etc. </p>
            <!--      <a href="#">Manage stopwords</a>-->
        </div>
    </div>
    <div class="usedWordsPart">
        <label class="blockTitle">Top words<span class="titleTooltip">Most frequently used words</span></label>
        <label class="colTitle expresscurate_margin30 expresscurate_floatRight">in content</label>
        <label class="colTitle expresscurate_margin30 expresscurate_floatRight">in title</label>
        <div class="expresscurate_clear"></div>
        <label class="notDefined">There is no enough data.</label>
        <ul>
            <?php
            $i = 0;
            foreach ($words_stats as $word => $stat) {
                if ($stat['percent'] >= 1 && $i < 15) {
                    ?>
                    <li>
                        <span class="color expresscurate_<?php echo $stat['color'] ?>"></span>
                        <span class="word"><?php echo $word; ?></span>
                        <span class="inTitle"><?php echo $stat['title']; ?> %</span>
                        <span class="add hover expresscurate_floatRight expresscurate_displayNone">&#43<span>make keyword</span></span>
                        <span class="count expresscurate_floatRight"><?php echo $stat['percent']; ?>%</span>
                    </li>
                <?php
                }
                $i++;
            }
            ?>
        </ul>
    </div>
</div>