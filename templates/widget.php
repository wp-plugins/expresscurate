<?php
global $post;
?>
<a name="expresscurate" id="expresscurate" xmlns="http://www.w3.org/1999/html"></a>
<div id="expresscurate_widget_wrapper" class="expresscurate_widget_wrapper expresscurate_Styles expresscurate_preventTextSelection">
    <label>Keywords <span class="rotate"><a href="#"></a></span>
        <span class="mark" onclick="Keywords.markEditorKeywords();return false;"><span>mark keywords</span></span>
    </label>
    <?php
    $keywords = new ExpressCurate_Keywords();
    $new_post_data = false;
    $post_keywords = trim(get_post_meta($post->ID, '_expresscurate_keywords', true), ',');
    $new_post_content = false;
    $pre_def_keyword = isset($_REQUEST['expresscurate_keyword']) ? $_REQUEST['expresscurate_keyword'] : '';
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
    <input type="hidden" id="expresscurate_plugin_dir" value="<?php echo plugin_dir_url(__FILE__); ?>"/>
    <input type="hidden" id="expresscurate_post_id" value="<?php echo $post->ID; ?>"/>
    <textarea id="expresscurate_defined_tags" class="expresscurate_displayNone"
              name="expresscurate_defined_tags"><?php echo $post_keywords; ?></textarea>

    <div class="addKeywords">
        <input type="text" placeholder="Add Keywords" class="expresscurate_disableInputStyle"/>
        <span class=""><span></span></span>
    </div>
</div>
<div class="clear"></div>
<?php
$seo = get_option('expresscurate_seo', '') == 'on';
if ($seo) {
?>
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

        <p class="usedKeywordsCount"><span class="bold">0</span> / 0</p>
        <span class="tooltip">It'll be better to use keywords in meta description.</span>
    </div>

    <p class="expresscurate_displayNone">The meta description tag is intented to be a brief and concise summary of your
        page's content.</p>
</div>

<a href="#expresscurate_advancedSEO_widget" class="expresscurate_moveToAdvanced">Advanced SEO</a>
<?php }  ?>
