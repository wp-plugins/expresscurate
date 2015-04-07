<?php
global $post;
$sitemap = new ExpressCurate_Sitemap();
$sitemapFrequencyArray =$sitemap->getSitemapFrequencyTagArray();
?>

<div class="container expresscurate_Styles expresscurate_advancedSEO_widget">
    <input type="hidden" name="expresscurate_post_analysis_notification" value="0" id="expresscurate_post_analysis_notification"/>
<ul class="tabs">
    <li class="tab-link expresscurate_preventTextSelection green current" data-tab="tab-1">General</li>
    <li class="tab-link expresscurate_preventTextSelection red <?php if(get_option('expresscurate_sitemap_update_permission') == 'error') echo 'disabled';?>" data-tab="tab-2">Sitemap</li>
    <li class="tab-link expresscurate_preventTextSelection blue" data-tab="tab-3">Social</li>
    <li class="tab-link postAnalysisLink expresscurate_preventTextSelection yellow" data-tab="tab-4">Post Analysis</li>
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
                   value="<?php echo get_post_meta($post->ID, '_expresscurate_advanced_seo_title', true); ?>">
        </div>
        <div class="expresscurate_clear"></div>
        <div class="info">
            <label for="expresscurate_advanced_seo_canonical_url" class="label">Cannonical URL</label>
        </div>
        <div class="value">
            <input id="expresscurate_advanced_seo_canonical_url" class="expresscurate_disableInputStyle" type="text"
                   name='expresscurate_advanced_seo_canonical_url'
                   value="<?php echo get_post_meta($post->ID, '_expresscurate_advanced_seo_canonical_url', true); ?>">
        </div>
        <div class="expresscurate_clear"></div>
        <div class="info">
            <label class="robotsLabel">Robots must follow links </label>
        </div>
        <div class="value">
            <input class="expresscurate_displayNone" id="expresscurate_advanced_seo_nofollow" type='checkbox'
                   name='expresscurate_advanced_seo_nofollow'
                <?php echo (get_post_meta($post->ID, '_expresscurate_advanced_seo_nofollow', true) == 'on' || !get_post_meta($post->ID, '_expresscurate_advanced_seo_nofollow', true)) ? 'checked' : '' ?>>
            <label class="expresscurate_preventTextSelection" for="expresscurate_advanced_seo_nofollow"></label>
            <input type="hidden" id="expresscurate_advanced_seo_nofollow_value" name="expresscurate_advanced_seo_nofollow_value"  value="<?php echo (get_post_meta($post->ID, '_expresscurate_advanced_seo_nofollow', true))?get_post_meta($post->ID, '_expresscurate_advanced_seo_nofollow', true):"on"; ?>">
        </div>
        <div class="expresscurate_clear"></div>
        <div class="info">
            <label class="robotsLabel">Robots must index this page </label>
        </div>
        <div class="value">
            <input class="expresscurate_displayNone" id="expresscurate_advanced_seo_noindex" type='checkbox'
                   name='expresscurate_advanced_seo_noindex'
                <?php echo (get_post_meta($post->ID, '_expresscurate_advanced_seo_noindex', true) == 'on' || !get_post_meta($post->ID, '_expresscurate_advanced_seo_nofollow', true)) ? 'checked' : '' ?>>
            <label class="expresscurate_preventTextSelection" for="expresscurate_advanced_seo_noindex"></label>
            <input type="hidden" id="expresscurate_advanced_seo_noindex_value" name="expresscurate_advanced_seo_noindex_value" value="<?php echo (get_post_meta($post->ID, '_expresscurate_advanced_seo_noindex', true))?get_post_meta($post->ID, '_expresscurate_advanced_seo_noindex', true):"on"; ?>">
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
                           name="expresscurate_sitemap_post_configure_manually" <?php if (get_post_meta($post->ID, '_expresscurate_sitemap_post_configure_manually', true) == 'on') echo 'checked'; ?>>
                    <label for="expresscurate_sitemap_post_configure_manually"></label>
                </div>
                <div class="expresscurate_clear"></div>
            </li>
            <li class="hiddenOptions <?php if (get_post_meta($post->ID, '_expresscurate_sitemap_post_configure_manually', true) != 'on') echo 'expresscurate_displayNone'; ?>">
                <ul>
                    <li>
                        <div class="info">
                            <span class="label">Exclude from sitemap</span>
                        </div>
                        <div class="value">
                            <input class="expresscurate_displayNone" type="checkbox"
                                   id="expresscurate_sitemap_post_exclude_from_sitemap"
                                   name="expresscurate_sitemap_post_exclude_from_sitemap"  <?php if (get_post_meta($post->ID, '_expresscurate_sitemap_post_exclude_from_sitemap', true) == 'on') echo 'checked'; ?>>
                            <label for="expresscurate_sitemap_post_exclude_from_sitemap"></label>
                        </div>
                        <div class="expresscurate_clear"></div>
                    </li>

                    <ul class="sitemapOption <?php if (get_post_meta($post->ID, '_expresscurate_sitemap_post_exclude_from_sitemap', true) == 'on') echo 'expresscurate_displayNone'; ?>">
                        <li>
                            <div class="info">
                                <span class="label">Sitemap frequency</span>
                                <span class="gray-italic desc">Please select frequency for posts in sitemap</span>
                            </div>
                            <div class="value">
                                <?php $expresscurate_sitemap_post_frequency = get_post_meta($post->ID, '_expresscurate_sitemap_post_frequency', true) ?>
                                <select name="expresscurate_sitemap_post_frequency"
                                        id="expresscurate_sitemap_post_frequency">
                                    <?php foreach($sitemapFrequencyArray as $key=>$val) {
                                        echo '<option value="' . $key . '"';
                                        if ($key == $expresscurate_sitemap_post_frequency) {
                                            echo ' selected="selected"';
                                        }
                                        echo '>' . $val . '</option>';
                                    }?>
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
                                <?php $expresscurate_sitemap_post_priority = get_post_meta($post->ID, '_expresscurate_sitemap_post_priority', true) ?>
                                <select name="expresscurate_sitemap_post_priority"
                                        id="expresscurate_sitemap_post_priority">
                                    <?php for($i=0.1; $i<=1; $i=$i+0.1) {
                                        echo '<option value="'.$i.'"';
                                        if ($i == $expresscurate_sitemap_post_priority) {
                                            echo ' selected="selected"';
                                        }
                                        echo '>'.$i.'</option>';
                                    }?>
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
                   value="<?php echo get_post_meta($post->ID, '_expresscurate_advanced_seo_social_title', true); ?>">
        </div>
        <div class="expresscurate_clear"></div>
        <div class="info">
            <label for="expresscurate_advanced_seo_social_shortdesc" class="label">Short Description</label>
        </div>
        <div class="value">
            <input id="expresscurate_advanced_seo_social_shortdesc" class="expresscurate_disableInputStyle" type="text"
                   name='expresscurate_advanced_seo_social_shortdesc'
                   value="<?php echo get_post_meta($post->ID, '_expresscurate_advanced_seo_social_shortdesc', true); ?>">
        </div>
        <div class="expresscurate_clear"></div>
        <div class="info">
            <label for="expresscurate_advanced_seo_social_desc" class="label">Description</label>
        </div>
        <div class="value">
            <input id="expresscurate_advanced_seo_social_desc" class="expresscurate_disableInputStyle" type="text"
                   name='expresscurate_advanced_seo_social_desc'
                   value="<?php echo get_post_meta($post->ID, '_expresscurate_advanced_seo_social_desc', true); ?>">
        </div>
        <div class="expresscurate_clear"></div>
    </div>
</div>
<div id="tab-4" class="tab-content postAnalysisTab">

</div>
</div>

