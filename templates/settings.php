<div class="expresscurate expresscurate_Styles expresscurate_settings wrap">
    <div class="expresscurate_headBorderBottom expresscurate_OpenSansRegular">
        <a href="admin.php?page=expresscurate_support" class="expresscurate_writeUs">Suggestions?
            <span>Submit here!</span></a>

        <h2>Settings</h2>
    </div>
    <h2 class="expresscurate_displayNone">Settings</h2>
    <?php
    $expresscurate_seo = get_option('expresscurate_seo', '') == "on" ? 'On' : 'Off';
    ?>
    <ul class="tabs" data-currenttab="<?php echo $_SESSION['settings_current_tab'] ?>">
        <li class="tab-link expresscurate_preventTextSelection green current" data-tab="tab-1">General</li>
        <li class="tab-link expresscurate_preventTextSelection orange" data-tab="tab-5">Feed</li>
        <li class="tab-link expresscurate_preventTextSelection red" data-tab="tab-2">Smart publishing</li>
        <li id="sitemapTab"
            class="tab-link expresscurate_preventTextSelection blue <?php if ($expresscurate_seo == 'Off') {
                echo 'expresscurate_displayNone';
            }
            if (get_option('expresscurate_sitemap_update_permission') == 'error') echo 'disabled'; ?>"
            data-tab="tab-3">Sitemap
        </li>
        <li class="tab-link expresscurate_preventTextSelection yellow" data-tab="tab-4">Extension</li>
    </ul>

    <div id="tab-1" class="tab-content current">
        <div>
            <form class="expresscurate_marginTop20" method="post" action="options.php">
                <?php @settings_fields('expresscurate-group'); ?>
                <?php @do_settings_fields('expresscurate-group'); ?>
                <ul>
                    <li>
                        <div class="title">Image Placement
                            <div class="description">
                                There are two options for placing the images (picked from the original article) in your
                                curated post:
                                <ol>
                                    <li>Featured. The image will be placed above the title, at the very top of your
                                        post. This will give
                                        your image a more prominent look.
                                    </li>
                                    <li>Standard. The image will be placed below the title and category. This option
                                        will focus your
                                        viewer's attention more on the title and less on the image.
                                    </li>
                                </ol>
                            </div>
                        </div>
                        <input class="expresscurate_featured" type="radio" id="expresscurate_featured" value="1"
                               name="expresscurate_featured" <?php
                        if (get_option('expresscurate_featured', '') == "1" || get_option('expresscurate_featured', '') == '') {
                            echo 'checked="checked"';
                        }
                        ?> />
                        <label class="controls expresscurate_radioLabel " for="expresscurate_featured">
                            Featured </label>
                        <input class="controls expresscurate_featured" type="radio" id="expresscurate_featured_no"
                               value="0"
                               name="expresscurate_featured" <?php
                        if (get_option('expresscurate_featured', '') == "0") {
                            echo 'checked="checked"';
                        }
                        ?> />
                        <label class="controls expresscurate_radioLabel" for="expresscurate_featured_no">
                            Standard </label>
                    </li>
                    <li>
                        <label for="expresscurate_curated_text" class="title">Attribution Text For Original Article Link<span
                                class="description">Type in the prefix text for attributing the original article link.
            It will show up at the bottom of your curated post.  For example, if you type "Curated from"
            in the box below and you curate from google.com, "Curated from google.com" will appear at the bottom of your post.</span>
                        </label>
                        <input type="text" class="controls" id="expresscurate_curated_text" value="<?php
                        if (get_option('expresscurate_curated_text')) {
                            echo get_option('expresscurate_curated_text');
                        } else {
                            echo 'Curated from';
                        }
                        ?>" name="expresscurate_curated_text" size="50"/>
                    </li>
                    <li>                       
                        <p class="title">Open Original Article Link in a New Window/Tab<span scope="row" class="description  ">
            Select "Yes" if you want the original article link to be opened in a New Window/Tab. Select "No" if you want the link to be opened in the Same Window/Tab (default behavior).
          </span></p>

                        <input class="expresscurate_displayNone" type="checkbox" id="expresscurate_curated_link_target"
                               name="expresscurate_curated_link_target" <?php
                        if (get_option('expresscurate_curated_link_target', 'on') == "on") {
                            echo 'checked';
                        }
                        ?> />
                        <label class="controls checkboxLabel" for="expresscurate_curated_link_target"></label>
                        
                    </li>
                    <li>
                        <label for="expresscurate_max_tags" class="title">Max Number of Auto-suggested Tags<span
                                class="description">&nbsp;&nbsp; The recommended value is 3.<br/>
        <span>ExpressCurate can auto-suggest tags for your post. It is based on the keywords and tags defined in your prior WordPress blogs. Enter the max number of auto-suggested tags you would like to have in each curated posts.</span>
                        </label>
                        <input type="text" id="expresscurate_max_tags" class="controls" value="<?php
                        if (get_option('expresscurate_max_tags') == false) {
                            echo get_option('expresscurate_max_tags');
                        } else {
                            echo '3';
                        }
                        ?>" name="expresscurate_max_tags" size="1"/>
                    </li>
                    <li>
                        <label for="expresscurate_autosummary" class="title">Number of Curated Paragraphs<span
                                class="description  ">Pick the number of paragraphs to be inserted from the original article into your post.</span>
                            <br/><span>&nbsp;&nbsp; The default value is 5</span></label>
                        <input type="text" id="expresscurate_autosummary" class="controls" value="<?php
                        if (get_option('expresscurate_autosummary') == false) {
                            echo get_option('expresscurate_autosummary');
                        } else {
                            echo '5';
                        }
                        ?>" name="expresscurate_autosummary" size="1"/>
                    </li>
                    <li>
                        <p class="title">Enable ExpressCurate Blockquote<span class="description  ">
            Select "Yes" to use ExpressCurate blockquote for marking the original content quote. Select "No" if you are already using a theme that has a good enough quote style and ExpressCurate won't override it.
          </span></p>
                        <input class="expresscurate_displayNone" type="checkbox" id="quotes_style"
                               name="expresscurate_quotes_style" <?php
                        if (get_option('expresscurate_quotes_style', '') == "on") {
                            echo 'checked';
                        }
                        ?> />
                        <label class="controls checkboxLabel" for="quotes_style"></label>
                    </li>
                    <li>
                        <p class="title">SEO Enhancements<span scope="row" class="description  ">
            Click on "Yes" to enable ExpressCurate SEO enhancements for your curated post.
          </span></p>

                        <input class="expresscurate_displayNone" type="checkbox" id="expresscurate_seo"
                               name="expresscurate_seo" <?php
                        if (get_option('expresscurate_seo', '') == "on") {
                            echo 'checked';
                        }
                        ?> />
                        <label class="controls checkboxLabel" for="expresscurate_seo"></label>
                    </li>
                    <li>
                        <div id="publisherWrap" class=" <?php
                        if ($expresscurate_seo == 'Off') {
                            echo 'expresscurate_displayNone';
                        }
                        ?> />">
                            <label for="expresscurate_publusher" class="title">Publisher (Google+)<span
                                    class="description  ">You can link content you publish on this blog to your company or personal Google+ profile.
                <a href="https://plus.google.com/authorship" target="_blank">More Info</a>.</label></span>

                            <input class="controls" type="text" id="expresscurate_publusher" size="50" value="<?php
                            if (get_option('expresscurate_publisher')) {
                                echo get_option('expresscurate_publisher');
                            } else {
                                echo '';
                            }
                            ?>" name="expresscurate_publisher"/>
                            <a class="expresscurateLink verifyPublisherLink expresscurate_marginleft10"
                               href="http://www.google.com/webmasters/tools/richsnippets?url=<?php echo bloginfo('url') ?>&user_profile=<?php echo get_option('expresscurate_publisher'); ?>"
                               target="_blank">Verify publisher</a>
                            <br/>
                            <label for="expresscurate_publisher_twitter" class="title">Publisher (Twitter)<span
                                    class="description  ">You can link content you publish on this blog to your company or personal Twitter profile.
                <a href="https://plus.google.com/authorship" target="_blank">More Info</a>.</label></span>

                            <input class="controls" type="text" id="expresscurate_publisher_twitter" size="50"
                                   value="<?php
                                   if (get_option('expresscurate_publisher_twitter')) {
                                       echo get_option('expresscurate_publisher_twitter');
                                   } else {
                                       echo '';
                                   }
                                   ?>" name="expresscurate_publisher_twitter" placeholder="@publisher_handle"/>
                        </div>
                    </li>
                    <li>
                        <p class="title">Auto Hashtagging<span class="description  ">
            Transform words starting with # into tags and add # to the words previously defined as tags.
          </span></p>
                        <?php
                        $smart_tags = get_option('expresscurate_smart_tagging', '') == "1" ? 'On' : 'Off';
                        ?>
                        <input class="expresscurate_displayNone" type="checkbox" id="smart_tags"
                               name="expresscurate_smart_tagging" <?php
                        if (get_option('expresscurate_smart_tagging') == "on") {
                            echo 'checked';
                        }
                        ?> />
                        <label class="controls checkboxLabel" for="smart_tags"></label>
                    </li>
                    <li>
                        <p class="title">Your Keyword Tags<span class="description  ">
            Enter your target keywords that you want to be tagged in the post.  Multiple keywords need to be separated by commas.
            <br/>When this list is defined, ExpressCurate will look for these words in curated content and try to tag them in the article, as well as create links from these keywords that show up on tag's page.</span>
                        </p>
                        <a class="expresscurateLink" href="admin.php?page=expresscurate_keywords">Keywords Dashboard</a>
                    </li>
                    <!-- Number of posts  -->
                    <li>
                        <p class="title"> Keyword Statistics.
                            <span class="description">
                               Generates comprehensive keyword statistics for the last <?php echo(get_option('expresscurate_posts_number') ? get_option('expresscurate_posts_number') : 100); ?>
                                posts.
                            </span>
                        </p>
                        <input type="number" id="expresscurate_posts_number" class="controls" value="<?php
                        if (get_option('expresscurate_posts_number')) {
                            echo get_option('expresscurate_posts_number');
                        } else {
                            echo '100';
                        }
                        ?>" name="expresscurate_posts_number" size="4" min="100" max="300"/>
                    </li>
                    <!--  -->
                </ul>
                <div class="centerSave">
                    <?php @submit_button(); ?>
                </div>
            </form>
        </div>
    </div>
    <div id="tab-2" class="tab-content">
        <div>
            <form class="expresscurate_marginTop20" method="post" action="options.php">
                <?php @settings_fields('expresscurate-smartpublish-group'); ?>
                <?php @do_settings_fields('expresscurate-smartpublish-group'); ?>
                <ul>
                    <li>
                        <p class="title">Smart publishing </p>
                        <input class="expresscurate_displayNone" type="checkbox" id="expresscurate_publish"
                               name="expresscurate_publish" <?php
                        if (get_option('expresscurate_publish', '') == "on") {
                            echo 'checked';
                        }
                        ?> />
                        <label class="controls checkboxLabel" for="expresscurate_publish"></label>
                        <?php
                        $show_interval = "expresscurate_displayNone";
                        if (get_option('expresscurate_publish', '') == 'on') {
                            $show_interval = "";
                        }
                        ?>
                        <div id="smartPublishingWrap" class="<?php echo $show_interval; ?>">

                            <p class="title">Manually approve posts for smart publishing</p>
                            <?php
                            $expresscurate_manually_approve_smart = get_option('expresscurate_manually_approve_smart', '') == 'On' ? 'On' : 'Off';
                            ?>
                            <input class="expresscurate_displayNone" type="checkbox"
                                   id="expresscurate_manually_approve_smart"
                                   name="expresscurate_manually_approve_smart" <?php
                            if (get_option('expresscurate_manually_approve_smart', '') == "on") {
                                echo 'checked';
                            }
                            ?> />
                            <label class="controls checkboxLabel"
                                   for="expresscurate_manually_approve_smart"></label>
                            <br/>

                            <p class="title" for="expresscurate_hours_interval">Publish draft
                                articles</p>

                            <select class="controls" id="expresscurate_hours_interval"
                                    name="expresscurate_hours_interval">
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
                    </li>
                    <!--<li>
                        <p class="title">Social publishing </p>
                        <input class="expresscurate_displayNone" type="checkbox" id="expresscurate_social_publishing"
                               name="expresscurate_social_publishing" <?php
/*                        if (get_option('expresscurate_social_publishing', '') == "on") {
                            echo 'checked';
                        }
                        */?> />
                        <label class="controls checkboxLabel" for="expresscurate_social_publishing"></label>
                        
                        <?php /*
                             $blogName = urlencode(urlencode(get_bloginfo('url')));
                        */?>
                        <a class="getApiKey  <?php /*if (strlen(get_option('expresscurate_buffer_refresh_token')) > 2) {echo 'expresscurate_displayNone';}*/?>" href="https://www.expresscurate.com/api/connector/buffer/refreshtoken/<?/*=$blogName*/?>">Authorize access to Buffer</a>
                    </li>-->
                </ul>
                <div class="centerSave">
                    <?php @submit_button(); ?>
                </div>
            </form>
        </div>
    </div>
    <div id="tab-3" class="tab-content">
        <div>
            <?php include(sprintf("%s/sitemap.php", dirname(__FILE__))); ?>
        </div>
    </div>
    <div id="tab-4" class="tab-content">
        <div>
            <form class="expresscurate_marginTop20" method="post" action="options.php">
                <?php @settings_fields('expresscurate-extension-group'); ?>
                <?php @do_settings_fields('expresscurate-extension-group'); ?>
                <ul>
                    <li>
                        <p class="title">Default Category for Curated Posts<span class="description  ">Please pick a default category for your curated posts from the list below. This will prevent a default "Uncategorized" being assigned to your post automatically.</span>
                        </p>

                        <div class="controls">
                            <?php
                            $categories = get_categories(array("hide_empty" => 0));
                            $cat_i = 1;
                            ?>
                            <?php
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
                                       for="expresscurate_cat_<?php echo $category->term_id; ?>"> <?php echo $category->name; ?> </label>
                                <br/>
                                <?php if ($cat_i % 6 == 0 && count($categories) != $cat_i) {
                                    ?>
                                <?php
                                }
                                ?>
                                <?php
                                $cat_i++;
                                ?><?php
                            }
                            ?>
                        </div>
                    </li>
                    <li>
                        <p class="title">Post type for ExpressCurate Chrome Extension<span class="description  ">Please pick a post type for your curated posts from the list below. This will allow custom post types when curating with ExpressCurate Chrome Extension.</span>
                        </p>

                        <div class="controls">
                            <?php
                            $post_types = array('post');
                            $post_types = array_merge($post_types, get_post_types(array('_builtin' => false, 'public' => true), 'names'));
                            $posts_i = 1;
                            ?>
                            <?php
                            foreach ($post_types as $post_type) {
                                ?>
                                <input class="expresscurate_post_type" type="radio"
                                       id="expresscurate_post_type_<?php echo $post_type; ?>"
                                       value="<?php echo $post_type; ?>" name="expresscurate_def_post_type" <?php
                                if (get_option('expresscurate_def_post_type', 'post') == $post_type) {
                                    echo 'checked="checked"';
                                }
                                ?>>
                                <label class="controls expresscurate_radioLabel"
                                       for="expresscurate_post_type_<?php echo $post_type; ?>"> <?php echo $post_type; ?> </label>
                                <br/>
                                <?php if ($posts_i % 2 == 0 && count($post_types) != $posts_i) {
                                    ?>
                                <?php
                                }
                                ?>
                                <?php
                                $cat_i++;
                                ?><?php
                            }
                            ?>
                        </div>
                    </li>
                    <li>
                        <p class="title">Publishing Directly from ExpressCurate Chrome Extension<span
                                class="description  ">This setting gives you an option to save your curated post as a draft or publish it when you click on the "Curate" button on ExpressCurate Chrome extension.</span>
                        </p>

                        <div class="extLabelWrapper">
                            <input class="expresscurate_post_draft" type="radio" id="expresscurate_post_published"
                                   value="publish"
                                   name="expresscurate_post_status" <?php
                            if (get_option('expresscurate_post_status', '') == "publish") {
                                echo 'checked="checked"';
                            }
                            ?> />
                            <label class="controls expresscurate_radioLabel" for="expresscurate_post_published">
                                Published </label>
                            <input class="expresscurate_post_draft" type="radio" id="expresscurate_post_draft"
                                   value="draft"
                                   name="expresscurate_post_status" <?php
                            if (get_option('expresscurate_post_status', '') == "draft" || get_option('expresscurate_post_status', '') == '') {
                                echo 'checked="checked"';
                            }
                            ?> />
                            <label class="controls expresscurate_radioLabel" for="expresscurate_post_draft">
                                Draft </label>
                        </div>
                    </li>
                </ul>
                <div class="centerSave">
                    <?php @submit_button(); ?>
                </div>
            </form>
        </div>
    </div>
    <div id="tab-5" class="tab-content">
        <div>
            <form class="expresscurate_marginTop20" method="post" action="options.php">
                <?php @settings_fields('expresscurate-feed-group'); ?>
                <?php @do_settings_fields('expresscurate-feed-group'); ?>
                <ul>
                    <li>
                        <div class="title">
                            Content Pull Frequency
                            <div class="description">
                                How frequently should ExpressCurate pull content (from RSS feeds, Alerts, etc) into your
                                <a href="#">Content Feed</a>?
                            </div>
                        </div>
                        <select class="controls" id="expresscurate_pull_hours_interval"
                                name="expresscurate_pull_hours_interval">
                            <?php
                            for ($i = 1; $i < 14; $i++) {
                                ?>
                                <?php if ($i == 1) { ?>
                                    <option value="<?php echo $i; ?>" <?php
                                    if (get_option('expresscurate_pull_hours_interval') == $i) {
                                        echo 'selected="selected"';
                                    }
                                    ?>>Every hour
                                    </option>

                                <?php } elseif ($i == 13) { ?>
                                    <option value="<?php echo $i; ?>" <?php
                                    if (get_option('expresscurate_pull_hours_interval') == $i) {
                                        echo 'selected="selected"';
                                    }
                                    ?>>Once a day
                                    </option>

                                <?php } else { ?>
                                    <option value="<?php echo $i; ?>" <?php
                                    if (get_option('expresscurate_pull_hours_interval') == $i) {
                                        echo 'selected="selected"';
                                    }
                                    ?>>Every <?php echo $i; ?> hours
                                    </option>

                                <?php
                                }
                            }
                            ?>
                        </select>
                    </li>
                    <li>
                        <div class="title">
                            Keyword Match Email Alerts
                            <div class="description">
                                Would you like to receive email alerts when keyword matches are found in your <a
                                    href="#">Content Feed</a>?
                            </div>
                        </div>
                        <input class="expresscurate_displayNone" id="expresscurate_enable_content_alert" type="checkbox"
                               name="expresscurate_enable_content_alert" <?php if (get_option('expresscurate_enable_content_alert') == 'on') echo 'checked'; ?>>
                        <label class="controls checkboxLabel" for="expresscurate_enable_content_alert"></label>
                    </li>
                    <li class="emailAlertSlider <?php if (get_option('expresscurate_enable_content_alert') != 'on') echo 'expresscurate_displayNone'; ?>">
                        <div class="title">
                            Content Alert Frequency
                            <div class="description">
                                How frequently should ExpressCurate send content Alert Email (from RSS feeds, Alerts,
                                etc)
                            </div>
                        </div>
                        <select class="controls" id="expresscurate_content_alert_frequency"
                                name="expresscurate_content_alert_frequency">
                            <?php
                            for ($i = 1; $i < 14; $i++) {
                                ?>
                                <?php if ($i == 1) { ?>
                                    <option value="<?php echo $i; ?>" <?php
                                    if (get_option('expresscurate_content_alert_frequency') == $i) {
                                        echo 'selected="selected"';
                                    }
                                    ?>>Every hour
                                    </option>

                                <?php } elseif ($i == 13) { ?>
                                    <option value="<?php echo $i; ?>" <?php
                                    if (get_option('expresscurate_content_alert_frequency') == $i) {
                                        echo 'selected="selected"';
                                    }
                                    ?>>Once a day
                                    </option>

                                <?php } else { ?>
                                    <option value="<?php echo $i; ?>" <?php
                                    if (get_option('expresscurate_content_alert_frequency') == $i) {
                                        echo 'selected="selected"';
                                    }
                                    ?>>Every <?php echo $i; ?> hours
                                    </option>

                                <?php
                                }
                            }
                            ?>
                        </select>
                    </li>
                </ul>
                <div class="centerSave">
                    <?php @submit_button(); ?>
                </div>
            </form>

        </div>
    </div>
</div>