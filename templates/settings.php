<div class="expresscurate wrap">
<div class="expresscurate_menu">
    <?php include(sprintf("%s/menu.php", dirname(__FILE__))); ?>
</div>
<h2 class="expresscurate_displayNone">Settings</h2>

<form class="expresscurate_marginTop30" method="post" action="options.php">
<?php @settings_fields('expresscurate-group'); ?>
<?php @do_settings_fields('expresscurate-group'); ?>

<?php //do_settings_sections('expresscurate');  ?>
<table class="form-table express_curate_table">

<tr valign="top">
    <th scope="row" colspan="2">
        <strong>Default Category for Curated Posts:</strong>
        <br>
        <span class="gray-italic">Please pick a default category for your curated posts from the list below. This will prevent a default "Uncategorized" being assigned to your post automatically.</span>
    </th>
</tr>

<tr valign="top" class="width-bottom-border">
    <td class="with-padding" colspan="2">
        <table>
            <tr valign="top">
                <?php
                $categories = get_categories(array("hide_empty" => 0));
                $cat_i = 1;
                ?>
                <td><?php
                    foreach ($categories as $category) {
                    ?>
                    <input class="expresscurate_cat" type="radio"
                           id="expresscurate_cat_<?php echo $category->term_id; ?>"
                           value="<?php echo $category->term_id; ?>" name="expresscurate_def_cat" <?php
                    if (get_option('expresscurate_def_cat') == $category->term_id) {
                        echo 'checked="checked"';
                    }
                    ?>>
                    <label class="expresscurate_radioLabel"
                           for="expresscurate_cat_<?php echo $category->term_id; ?>"> <?php echo $category->name; ?> </label><br/>
                    <?php if ($cat_i % 6 == 0 && count($categories) != $cat_i) {
                    ?>
                </td>
                <td>
                    <?php
                    }
                    ?>
                    <?php
                    $cat_i++;
                    ?><?php
                    }
                    ?>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr valign="top">
    <th scope="row" colspan="2">
        <strong>Post type for ExpressCurate Chrome Extension:</strong>
        <br>
        <span class="gray-italic">Please pick a post type for your curated posts from the list below. This will allow custom post types when curating with ExpressCurate Chrome Extension.</span>
    </th>
</tr>

<tr valign="top" class="width-bottom-border">
    <td class="with-padding" colspan="2">
        <table>
            <tr valign="top">
                <?php
                $post_types = array('post');
                $post_types = array_merge($post_types, get_post_types(array('_builtin' => false, 'public' => true), 'names'));
                $posts_i = 1;
                ?>
                <td><?php
                    foreach ($post_types as $post_type) {
                    ?>
                    <input class="expresscurate_post_type" type="radio"
                           id="expresscurate_post_type_<?php echo $post_type; ?>"
                           value="<?php echo $post_type; ?>" name="expresscurate_def_post_type" <?php
                    if (get_option('expresscurate_def_post_type', 'post') == $post_type) {
                        echo 'checked="checked"';
                    }
                    ?>>
                    <label class="expresscurate_radioLabel"
                           for="expresscurate_post_type_<?php echo $post_type; ?>"> <?php echo $post_type; ?> </label><br/>
                    <?php if ($posts_i % 2 == 0 && count($post_types) != $posts_i) {
                    ?>
                </td>
                <td>
                    <?php
                    }
                    ?>
                    <?php
                    $cat_i++;
                    ?><?php
                    }
                    ?>
                </td>
            </tr>
        </table>
    </td>
</tr>


<tr valign="top">
    <td scope="row" class="width-for-td">
        <strong>Publishing Directly from ExpressCurate Chrome Extension:</strong>
        <br>
        <span scope="row" class="gray-italic">This setting gives you an option to save your curated post as a draft or publish it when you click on the "Curate" button on ExpressCurate Chrome extension.</span>
    </td>
    <td>

        <input class="expresscurate_post_draft" type="radio" id="expresscurate_post_published" value="publish"
               name="expresscurate_post_status" <?php
        if (get_option('expresscurate_post_status', '') == "publish") {
            echo 'checked="checked"';
        }
        ?> />
        <label class="expresscurate_radioLabel" for="expresscurate_post_published"> Published </label>
        <input class="expresscurate_post_draft" type="radio" id="expresscurate_post_draft" value="draft"
               name="expresscurate_post_status" <?php
        if (get_option('expresscurate_post_status', '') == "draft" || get_option('expresscurate_post_status', '') == '') {
            echo 'checked="checked"';
        }
        ?> />
        <label class="expresscurate_radioLabel" for="expresscurate_post_draft"> Draft </label><br>
    </td>
</tr>
<tr class="width-bottom-border">
    <td colspan="2">
        <div id="expresscurate_publish_div" class="hidden">
            <table>
                <tbody>
                <tr>
                    <td class="smartPublishingWidth width-for-td">
                        <b class="expresscurate_marginTopBottom10">Smart publishing: </b>
                        <?php
                        $expresscurate_publish = get_option('expresscurate_publish', '') == "1" ? 'On' : 'Off';
                        ?>
                    </td>
                    <td>
                        <input class="expresscurate_displayNone" type="checkbox" id="expresscurate_publish"
                               name="expresscurate_publish" <?php
                        if (get_option('expresscurate_publish', '') == "on") {
                            echo 'checked';
                        }
                        ?> />
                        <label class="checkboxLabel" for="expresscurate_publish"></label>
                    </td>
                </tr>
                <tr >
                    <td colspan="2">
                        <?php
                        $show_interval = "expresscurate_displayNone";
                        if (get_option('expresscurate_publish', '') == 'on') {
                            $show_interval = "";
                        }
                        ?>
                        <div id="smartPublishingWrap" class="<?php echo $show_interval;?>">
                            <table>
                                <tbody>
                                    <tr>
                                        <td class="max-width-for-td width-for-td">
                                            <b class="expresscurate_marginTop15">Manually approve posts for smart publishing:</b>
                                            <?php
                                            $expresscurate_manually_approve_smart = get_option('expresscurate_manually_approve_smart', '') == "1" ? 'On' : 'Off';
                                            ?>
                                            <br/>
                                            <label class="expresscurate_marginTop15" for="hours_interval"><b>Publish draft articles:</b></label>
                                        </td>
                                        <td>
                                            <input class="expresscurate_displayNone" type="checkbox" id="expresscurate_manually_approve_smart"
                                                   name="expresscurate_manually_approve_smart" <?php
                                            if (get_option('expresscurate_manually_approve_smart', '') == "on") {
                                                echo 'checked';
                                            }
                                            ?> />
                                            <label class="checkboxLabel expresscurate_marginTop20" for="expresscurate_manually_approve_smart"></label>
                                            <br/>
                                            <select class="expresscurate_marginTop15" name="expresscurate_hours_interval">
                                                <?php
                                                for ($i = 1; $i < 14; $i++) {
                                                    ?>
                                                    <?php if ($i == 1) { ?>
                                                        <option value="<?php echo $i; ?>" <?php
                                                        if (get_option('expresscurate_hours_interval') == $i) {
                                                            echo 'selected="selected"';
                                                        }
                                                        ?>>Every hour
                                                        </option>

                                                    <?php } elseif ($i == 13) { ?>
                                                        <option value="<?php echo $i; ?>" <?php
                                                        if (get_option('expresscurate_hours_interval') == $i) {
                                                            echo 'selected="selected"';
                                                        }
                                                        ?>>Once a day
                                                        </option>

                                                    <?php } else { ?>
                                                        <option value="<?php echo $i; ?>" <?php
                                                        if (get_option('expresscurate_hours_interval') == $i) {
                                                            echo 'selected="selected"';
                                                        }
                                                        ?>>Every <?php echo $i; ?> hours
                                                        </option>

                                                    <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </td>
</tr>


<tr valign="top" class="width-bottom-border">
    <td scope="row" class="width-for-td">
        <strong>Image Placement:</strong>
        <br>
          <span scope="row" class="gray-italic">
            There are two options for placing the images (picked from the original article) in your curated post:<br/>
            <ol>
                <li>Featured. The image will be placed above the title, at the very top of your post. This will give
                    your image a more prominent look.
                <li>Standard. The image will be placed below the title and category. This option will focus your
                    viewer's attention more on the title and less on the image.
            </ol>
          </span>
    </td>
    <td>
        <input class="expresscurate_featured" type="radio" id="expresscurate_featured" value="1"
               name="expresscurate_featured" <?php
        if (get_option('expresscurate_featured', '') == "1" || get_option('expresscurate_featured', '') == '') {
            echo 'checked="checked"';
        }
        ?> />
        <label class="expresscurate_radioLabel" for="expresscurate_featured"> Featured </label>
        <input class="expresscurate_featured" type="radio" id="expresscurate_featured_no" value="0"
               name="expresscurate_featured" <?php
        if (get_option('expresscurate_featured', '') == "0") {
            echo 'checked="checked"';
        }
        ?> />
        <label class="expresscurate_radioLabel" for="expresscurate_featured_no"> Standard </label>
    </td>
</tr>
<tr valign="top">
    <th scope="row" colspan="2">
        <strong>Attribution Text For Original Article Link:</strong>
        <br>
        <span scope="row" colspan="2" class="gray-italic">Type in the prefix text for attributing the original article link.  It will show up at the bottom of your curated post.  For example, if you type "Curated from" in the box below and you curate from google.com, "Curated from google.com" will appear at the bottom of your post.</span>
    </th>
</tr>
<tr valign="top">
    <td colspan="2">
        <input type="text" class="wide-input with-max-width" id="expresscurate_curated_text" value="<?php
        if (get_option('expresscurate_curated_text')) {
            echo get_option('expresscurate_curated_text');
        } else {
            echo 'Curated from';
        }
        ?>" name="expresscurate_curated_text" size="50"/>
    </td>
</tr>
<tr valign="top" class="width-bottom-border">
    <th scope="row" class="width-for-td">
        <strong>Max Number of Auto-suggested Tags:</strong>
        <br>
        <span class="gray-italic">ExpressCurate can auto-suggest tags for your post. It is based on the keywords and tags defined in your prior WordPress blogs. Enter the max number of auto-suggested tags you would like to have in each curated posts.</span>
    </th>
    <td>
        <input type="text" id="expresscurate_max_tags" class="centered-text" value="<?php
        if (get_option('expresscurate_max_tags', '')!=='') {
            echo get_option('expresscurate_max_tags');
        } else {
            echo '3';
        }
        ?>" name="expresscurate_max_tags" size="1"/>
        <span class="gray-italic">&nbsp;&nbsp; The recommended value is 3</span>
    </td>
</tr>
<tr valign="top" class="width-bottom-border">
    <th scope="row" class="width-for-td">
        <strong>Number of Curated Paragraphs:</strong>
        <br>
          <span
              class="gray-italic">Pick the number of paragraphs to be inserted from the original article into your post.</span>
    </th>
    <td>
        <input type="text" id="expresscurate_autosummary" class="centered-text" value="<?php
        if (get_option('expresscurate_autosummary')) {
            echo get_option('expresscurate_autosummary');
        } else {
            echo '5';
        }
        ?>" name="expresscurate_autosummary" size="1"/><span
            class="gray-italic">&nbsp;&nbsp; The default value is 5</span>
    </td>
</tr>
<tr valign="top" class="width-bottom-border">
    <td scope="row" class="width-for-td">
        <strong>Enable ExpressCurate Blockquote:</strong>
        <br>
          <span scope="row" class="gray-italic">
            Select "Yes" to use ExpressCurate blockquote for marking the original content quote. Select "No" if you are already using a theme that has a good enough quote style and ExpressCurate won't override it.
          </span>
    </td>
    <td>
        <?php
        $expresscurate_quotes_style = get_option('expresscurate_quotes_style', '') == "1" ? 'On' : 'Off';
        ?>
        <input class="expresscurate_displayNone" type="checkbox" id="quotes_style" name="expresscurate_quotes_style" <?php
        if (get_option('expresscurate_quotes_style', '') == "on") {
            echo 'checked';
        }
        ?> />
        <label class="checkboxLabel" for="quotes_style"></label>
    </td>
</tr>
<tr valign="top" class="width-bottom-border">
    <td scope="row" class="width-for-td">
        <strong>SEO Enhancements:</strong>
        <br>
          <span scope="row" class="gray-italic">
            Click on "Yes" to enable ExpressCurate SEO enhancements for your curated post.
          </span>
    </td>
    <td>
        <?php
        $expresscurate_seo = get_option('expresscurate_seo', '') == "on" ? 'On' : 'Off';
        ?>
        <input class="expresscurate_displayNone" type="checkbox" id="expresscurate_seo" name="expresscurate_seo" <?php
        if (get_option('expresscurate_seo', '') == "on") {
            echo 'checked';
        }
        ?> />
        <label class="checkboxLabel" for="expresscurate_seo"></label>
    </td>
</tr>
<tr valign="top">
    <th scope="row" colspan="2">
        <div id="publisherWrap" class=" <?php
        if ($expresscurate_seo == 'Off') {
            echo 'expresscurate_displayNone';
        }
        ?> />">
            <table class="width-bottom-border">
                <tr>
                    <th scope="row" class="width-for-td">
                        <strong>Publisher:</strong>
                        <br>
              <span class="gray-italic">You can link content you publish on this blog to your company or personal Google+ profile.
                <br/>
                <a
                    href="https://plus.google.com/authorship" target="_blank">More Info</a>.</span>
                    </th>
                    <td>
                        <input type="text" id="expresscurate_publusher" size="50" value="<?php
                        if (get_option('expresscurate_publisher')) {
                            echo get_option('expresscurate_publisher');
                        } else {
                            echo '';
                        }
                        ?>" name="expresscurate_publisher"/><span class="gray-italic">&nbsp;&nbsp;<a
                                href="http://www.google.com/webmasters/tools/richsnippets?url=<?php echo bloginfo('url') ?>&user_profile=<?php echo get_option('expresscurate_publisher'); ?>"
                                target="_blank">Verify publisher</a></span>
                    </td>
                </tr>
            </table>
        </div>
    </th>
</tr>
<tr valign="top" class="width-bottom-border">
    <td scope="row" class="width-for-td">
        <strong>Auto Hashtagging:</strong>
        <br>
          <span scope="row" class="gray-italic">
            Transform words starting with # into tags and add # to the words previously defined as tags.
          </span>
    </td>
    <td>
        <?php
        $smart_tags = get_option('expresscurate_smart_tagging', '') == "on" ? 'On' : 'Off';
        ?>
        <input class="expresscurate_displayNone" type="checkbox" id="smart_tags" name="expresscurate_smart_tagging" <?php
        if (get_option('expresscurate_smart_tagging') == "on") {
            echo 'checked';
        }
        ?> />
        <label class="checkboxLabel" for="smart_tags"></label>
    </td>
</tr>
<tr valign="top">
    <th scope="row" colspan="2">
        <strong>Your Keyword Tags:</strong>
        <br>
          <span class="gray-italic">
            Enter your target keywords that you want to be tagged in the post.  Multiple keywords need to be separated by commas.
            <br/>When this list is defined, ExpressCurate will look for these words in curated content and try to tag them in the article, as well as create links from these keywords that show up on tag's page.</span>
    </th>
</tr>
<tr valign="top">
    <th scope="row" class="width-for-td">
        <strong>Your Keyword Tags:</strong>
    </th>
    <td class="expresscurate_paddingTop20">
        <a class="blue-italic vAlignBaseline" href="admin.php?page=expresscurate_keywords">Keywords Dashboard</a>
    </td>
<tr>
</table>

<?php @submit_button(); ?>
</form>
</div>