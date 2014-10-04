<?php
global $post;
?>
<a name="expresscurate" id="expresscurate" xmlns="http://www.w3.org/1999/html"></a>
<div id="expresscurate_widget_wrapper" class="expresscurate_widget_wrapper">
    <label>Keywords <span class="rotate"><a href="#" onclick="SEOControl.updateKeywords();
      return false;"></a></span>
        <span class="mark expresscurate_preventTextSelection" onclick="Keywords.markEditorKeywords();"><span>mark keywords</span></span>
    </label>
    <?php
    $keywords = new ExpressCurate_Keywords();
    $new_post_data = false;
    $post_keywords = trim(get_post_meta($post->ID, '_expresscurate_keywords', true), ',');
    $new_post_content = false;
    $pre_def_keyword = isset($_REQUEST['expresscurate_keyword'])?$_REQUEST['expresscurate_keyword']:'';
    $pre_def_description = "";
    if (isset($_REQUEST['expresscurate_keyword'])) {
        $pre_def_description = "TODO: write description using " . $pre_def_keyword;
    }

    $args = false;
    if (isset($_GET['post_title'])) {
        $new_post_data = array('title' => $_GET['post_title'], 'content' => $_GET['content']);
        $new_post_content = $keywords->get_words($args, $new_post_data);
    }

    if ($post_keywords && $pre_def_keyword) {
        $post_keywords .= ', ' . $pre_def_keyword;
    } else if (!$post_keywords && $pre_def_keyword) {
        $post_keywords = $pre_def_keyword;
    }
    ?>
    <input type="hidden" id="expresscurate_admin_url" value="<?php echo admin_url(); ?>"/>
    <input type="hidden" id="expresscurate_plugin_dir" value="<?php echo plugin_dir_url(__FILE__); ?>"/>
    <input type="hidden" id="expresscurate_post_id" value="<?php echo $post->ID; ?>"/>
    <textarea id="expresscurate_defined_tags" class="expresscurate_displayNone"
              name="expresscurate_defined_tags"><?php echo $post_keywords; ?></textarea>
<!--
    <?php
/*    if ($post_keywords) {
        $args = array('id' => $post->ID);
        $post_keywords_stats = $keywords->get_stats(array_map('trim', explode(",", $post_keywords)), $args, $new_post_content);
        foreach ($post_keywords_stats as $word => $stats) {
            */?>
            <div class="expresscurate_background_wrap">
                <span class="close">&#215</span>

                <div title="<?php /*echo $word; */?>"
                     class="statisticsTitle expresscurate_<?php /*echo($stats['color'] ? $stats['color'] : 'blue'); */?>">
                    <span><?php /*echo $word; */?></span></div>
                <div title="Occurance in Title: <?php /*echo($stats['title'] ? 'yes' : 'no') */?>"
                     class="statistics borderRight">
                    <div class="center">title<img
                            src="<?php /*echo plugin_dir_url(__FILE__); */?>../images/<?php /*echo($stats['title'] ? 'yes' : 'no') */?>.png">
                    </div>
                </div>

                <div title="Occurance in Content: <?php /*echo $stat['percent']; */?>%" class="statistics">
                    <div class="center">content<span><?php /*echo $stats['percent']; */?>%</span></div>
                </div>
            </div>
        <?php
/*        }
        */?>

    --><?php /*} */?>

    <div class="addKeywords">
        <input type="text" placeholder="Add Keywords" class="disableInputStyle"/>
        <span class="">&#43</span>
    </div>
</div>
<div class="clear"></div>
<div class="description">
    <label for="description">Description</label>

    <div class="descriptionWrap textareaBorder">
        <textarea id="description" class="expresscurate_disableInputStyle"
              name="expresscurate_description"><?php
        if (strlen($pre_def_description) > 3 && get_post_meta($post->ID, '_expresscurate_description', true) == '') {
            echo trim($pre_def_description);
        } else {
            echo get_post_meta($post->ID, '_expresscurate_description', true);
        }
        ?>
    </textarea>
    </div>
        <div class="hint expresscurate_displayNone borderRight">
            <span>characters left</span>
            <p class="lettersCount"><span class="bold">156</span> / 156</p>
            <span class="tooltip">The meta description will be limited to 156 chars.</span>
        </div>
        <div class="hint expresscurate_displayNone">
            <span>keywords</span>
            <p class="usedKeywordsCount"><span class="bold" >0</span> / 0</p>
            <span class="tooltip">It'll be better to use keywords in meta description.</span>
        </div>

    <p class="expresscurate_displayNone">The meta description tag is intented to be a brief and concise summary of your
        page's content.</p>
</div>
