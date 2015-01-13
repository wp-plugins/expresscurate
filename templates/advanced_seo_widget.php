<?php
global $post;
?>

<div class="container expresscurate_Styles expresscurate_advancedSEO_widget">

<ul class="tabs">
    <li class="tab-link expresscurate_preventTextSelection green current" data-tab="tab-1">Advanced SEO</li>
    <li class="tab-link expresscurate_preventTextSelection red" data-tab="tab-2">Sitemap</li>
    <li class="tab-link expresscurate_preventTextSelection blue" data-tab="tab-3">Social</li>
</ul>

<div id="tab-1" class="tab-content current">
    <a name="expresscurate" id="expresscurate" xmlns="http://www.w3.org/1999/html"></a>

    <div id="expresscurate_advancedSEO_widget">
        <div class="info">
            <label for="expresscurate_advanced_seo_title" class="label">SEO Title</label>
        </div>
        <div class="value">
            <input id="expresscurate_advanced_seo_title" class="expresscurate_disableInputStyle" type="text"
                   name='expresscurate_advanced_seo_title'
                   value="<?php echo get_post_meta($post->ID, 'expresscurate_advanced_seo_title', true); ?>">
        </div>
        <div class="expresscurate_clear"></div>
        <div class="info">
            <label for="expresscurate_advanced_seo_canonical_url" class="label">Cannonical URL</label>
        </div>
        <div class="value">
            <input id="expresscurate_advanced_seo_canonical_url" class="expresscurate_disableInputStyle" type="text"
                   name='expresscurate_advanced_seo_canonical_url'
                   value="<?php echo get_post_meta($post->ID, 'expresscurate_advanced_seo_canonical_url', true); ?>">
        </div>
        <div class="expresscurate_clear"></div>
        <div class="info">
            <label class="robotsLabel">Robots must follow links </label>
        </div>
        <div class="value">
            <input class="expresscurate_displayNone" id="expresscurate_advanced_seo_nofollow" type='checkbox'
                   name='expresscurate_advanced_seo_nofollow'
                <?php echo get_post_meta($post->ID, 'expresscurate_advanced_seo_nofollow', true) == 'on' ? 'checked' : '' ?>>
            <label class="expresscurate_preventTextSelection" for="expresscurate_advanced_seo_nofollow"></label>
        </div>
        <div class="expresscurate_clear"></div>
        <div class="info">
            <label class="robotsLabel">Robots must index this page </label>
        </div>
        <div class="value">
            <input class="expresscurate_displayNone" id="expresscurate_advanced_seo_noindex" type='checkbox'
                   name='expresscurate_advanced_seo_noindex'
                <?php echo get_post_meta($post->ID, 'expresscurate_advanced_seo_noindex', true) == 'on' ? 'checked' : '' ?>>
            <label class="expresscurate_preventTextSelection" for="expresscurate_advanced_seo_noindex"></label>
        </div>
        <div class="expresscurate_clear"></div>
    </div>
</div>
<div id="tab-2" class="tab-content">
    <div id="expresscurate_sitemap_widget">
        <ul class="expresscurate_sitemap_widget">
            <li>
                <div class="info">
                    <span class="label">Configure Sitemap Manually</span>
                </div>
                <div class="value">
                    <input class="expresscurate_displayNone" type="checkbox"
                           id="expresscurate_sitemap_post_configure_manually"
                           name="expresscurate_sitemap_post_configure_manually" <?php if (get_post_meta($post->ID, 'expresscurate_sitemap_post_configure_manually', true) == 'on') echo 'checked'; ?>>
                    <label for="expresscurate_sitemap_post_configure_manually"></label>
                </div>
                <div class="expresscurate_clear"></div>
            </li>
            <li class="hiddenOptions <?php if (get_post_meta($post->ID, 'expresscurate_sitemap_post_configure_manually', true) != 'on') echo 'expresscurate_displayNone'; ?>">
                <ul>
                    <li>
                        <div class="info">
                            <span class="label">Exclude from sitemap</span>
                        </div>
                        <div class="value">
                            <input class="expresscurate_displayNone" type="checkbox"
                                   id="expresscurate_sitemap_post_exclude_from_sitemap"
                                   name="expresscurate_sitemap_post_exclude_from_sitemap"  <?php if (get_post_meta($post->ID, 'expresscurate_sitemap_post_exclude_from_sitemap', true) == 'on') echo 'checked'; ?>>
                            <label for="expresscurate_sitemap_post_exclude_from_sitemap"></label>
                        </div>
                        <div class="expresscurate_clear"></div>
                    </li>

                    <ul class="sitemapOption <?php if (get_post_meta($post->ID, 'expresscurate_sitemap_post_exclude_from_sitemap', true) == 'on') echo 'expresscurate_displayNone'; ?>">
                        <li>
                            <div class="info">
                                <span class="label">Sitemap frequency</span>
                                <span class="gray-italic desc">Please select frequency for posts in sitemap</span>
                            </div>
                            <div class="value">
                                <select name="expresscurate_sitemap_post_frequency"
                                        id="expresscurate_sitemap_post_frequency">
                                    <option value="always" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_frequency', true) == 'always') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>always
                                    </option>
                                    <option value="hourly" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_frequency', true) == 'hourly') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>Hourly
                                    </option>
                                    <option value="daily" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_frequency', true) == 'daily') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>Daily
                                    </option>
                                    <option value="weekly" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_frequency', true) == 'weekly') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>Weekly
                                    </option>
                                    <option value="monthly" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_frequency', true) == 'monthly') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>Monthly
                                    </option>
                                    <option value="yearly" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_frequency', true) == 'yearly') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>Yearly
                                    </option>
                                    <option value="never" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_frequency', true) == 'never') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>Never
                                    </option>
                                </select>
                            </div>
                            <div class="expresscurate_clear"></div>
                        </li>
                        <li>
                            <div class="info">
                                <span class="label">Sitemap priority</span>
                                <span class="gray-italic desc">Please select priority for posts in sitemap</span>
                            </div>
                            <div class="value">
                                <select name="expresscurate_sitemap_post_priority"
                                        id="expresscurate_sitemap_post_priority">
                                    <option value="0.1" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_priority', true) == '0.1') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>0.1
                                    </option>
                                    <option value="0.2" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_priority', true) == '0.2') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>0.2
                                    </option>
                                    <option value="0.3" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_priority', true) == '0.3') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>0.3
                                    </option>
                                    <option value="0.4" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_priority', true) == '0.4') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>0.4
                                    </option>
                                    <option value="0.5" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_priority', true) == '0.5') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>0.5
                                    </option>
                                    <option value="0.6" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_priority', true) == '0.6') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>0.6
                                    </option>
                                    <option value="0.7" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_priority', true) == '0.7') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>0.7
                                    </option>
                                    <option value="0.8" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_priority', true) == '0.8') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>0.8
                                    </option>
                                    <option value="0.9" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_priority', true) == '0.9') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>0.9
                                    </option>
                                    <option value="1" <?php
                                    if (get_post_meta($post->ID, 'expresscurate_sitemap_post_priority', true) == '1') {
                                        echo 'selected="selected"';
                                    }
                                    ?>>1
                                    </option>
                                </select>
                            </div>
                            <div class="expresscurate_clear"></div>
                        </li>
                    </ul>
                </ul>
            </li>
        </ul>
    </div>
</div>
<div id="tab-3" class="tab-content">
    <div id="expresscurate_social_widget">
        <div class="info">
            <label for="expresscurate_advanced_seo_social_title" class="label">Title</label>
        </div>
        <div class="value">
            <input id="expresscurate_advanced_seo_social_title" class="expresscurate_disableInputStyle" type="text"
                   name='expresscurate_advanced_seo_social_title'
                   value="<?php echo get_post_meta($post->ID, 'expresscurate_advanced_seo_social_title', true); ?>">
        </div>
        <div class="expresscurate_clear"></div>
        <div class="info">
            <label for="expresscurate_advanced_seo_social_shortdesc" class="label">Short Description</label>
        </div>
        <div class="value">
            <input id="expresscurate_advanced_seo_social_shortdesc" class="expresscurate_disableInputStyle" type="text"
                   name='expresscurate_advanced_seo_social_shortdesc'
                   value="<?php echo get_post_meta($post->ID, 'expresscurate_advanced_seo_social_shortdesc', true); ?>">
        </div>
        <div class="expresscurate_clear"></div>
        <div class="info">
            <label for="expresscurate_advanced_seo_social_desc" class="label">Description</label>
        </div>
        <div class="value">
            <input id="expresscurate_advanced_seo_social_desc" class="expresscurate_disableInputStyle" type="text"
                   name='expresscurate_advanced_seo_social_desc'
                   value="<?php echo get_post_meta($post->ID, 'expresscurate_advanced_seo_social_desc', true); ?>">
        </div>
        <div class="expresscurate_clear"></div>
    </div>
</div>

</div>

