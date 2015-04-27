<h2 class="expresscurate_displayNone">Sitemap.xml</h2>
<form class="expresscurate_marginTop20" method="post" action="options.php">
<?php @settings_fields('expresscurate-sitemap-group'); ?>
<?php @do_settings_fields('expresscurate-sitemap-group'); ?>

<?php //do_settings_sections('expresscurate');?>

    <ul>
<li>
    <div class="title submitSitemapWrap">
            <a class="generate <?php if (strlen(get_option('expresscurate_google_refresh_token')) < 3) {echo 'expresscurate_displayNone'; }else {echo 'generated';}?>"  id="submitSiteMap" href="#">Submit Sitemap<span class="loading"></span></a>
    </div>
        <a class="generate" id="generateSiteMap" href="#">Generate Sitemap</a>
</li>
<br/>
<li>
    <p class="title">Sitemap update frequency<span class="description ">How often should the sitemap be updated?</span></p>
    <select class="controls" name="expresscurate_sitemap_generation_interval">
        <option value="always" <?php
        if (get_option('expresscurate_sitemap_generation_interval') == 'always') {
            echo 'selected="selected"';
        }
        ?>>On every Post
        </option>
        <option value="daily" <?php
        if (get_option('expresscurate_sitemap_generation_interval') == 'daily') {
            echo 'selected="selected"';
        }
        ?>>Daily
        </option>
        <option value="weekly" <?php
        if (get_option('expresscurate_sitemap_generation_interval') == 'weekly') {
            echo 'selected="selected"';
        }
        ?>>Weekly
        </option>
        <option value="monthly" <?php
        if (get_option('expresscurate_sitemap_generation_interval') == 'monthly') {
            echo 'selected="selected"';
        }
        ?>>Monthly
        </option>
    </select>
</li>

<li>
    <p class="title">Include all new posts in sitemap?<span class="description ">All new posts will be included in your sitemap if this option is enabled</span></p>
    <input class="expresscurate_displayNone" type="checkbox" id="expresscurate_sitemap_include_new_posts"
           name="expresscurate_sitemap_include_new_posts" <?php if (get_option('expresscurate_sitemap_include_new_posts') == 'on') echo 'checked'; ?>>
    <label class="controls checkboxLabel" for="expresscurate_sitemap_include_new_posts"></label>
</li>
<li>
    <p class="title">Include all new pages in sitemap?<span class="description ">All new pages will be included in your sitemap if this option is enabled</span></p>
    <input class="expresscurate_displayNone" type="checkbox" id="expresscurate_sitemap_include_new_pages"
           name="expresscurate_sitemap_include_new_pages" <?php if (get_option('expresscurate_sitemap_include_new_pages') == 'on') echo 'checked'; ?>>
    <label class="controls checkboxLabel" for="expresscurate_sitemap_include_new_pages"></label>
</li>
<!--<li>-->
<!--    <p class="title">New entry default priority<span class="description ">Please select the priority of new pages and posts in the sitemap</span></p>-->
<!--    <select class="controls" name="expresscurate_sitemap_default_priority">-->
<!--        <option value="manual" --><?php
//        if (get_option('expresscurate_sitemap_default_priority') == 'manual') {
//            echo 'selected="selected"';
//        }
//        ?>
<!--manual-->
<!--        </option>-->
<!---->
<!--        <option value="automatic" --><?php
//        if (get_option('expresscurate_sitemap_default_priority') == 'automatic') {
//            echo 'selected="selected"';
//        }
//        ?>
<!--automatic-->
<!--        </option>-->
<!--    </select>-->
<!--</li>-->
<?php
    $sitemap = new ExpressCurate_Sitemap();
    $sitemapFrequencyArray =$sitemap->getSitemapFrequencyTagArray();
    $priority = (get_option('expresscurate_sitemap_priority_manual_value'))?get_option('expresscurate_sitemap_priority_manual_value'):"0.8";
    $frequency = (get_option('expresscurate_sitemap_default_changefreq'))?get_option('expresscurate_sitemap_default_changefreq'):"never";
?>
<li>
    <p class="title">Sitemap manual priority value<span class="description ">Please select the priority for new posts in your sitemap</span></p>
    <select class="controls" name="expresscurate_sitemap_priority_manual_value">
        <?php for($i=0.1; $i<=1; $i=$i+0.1) {
            echo '<option value="'.$i.'"';
            if ($i == $priority) {
                echo ' selected="selected"';
            }
            echo '>'.$i.'</option>';
        }?>
    </select>
</li>
<li>
    <p class="title">Sitemap default changefreq<span class="description ">Please select the sitemap changefreq value for your new posts</span></p>
    <select class="controls" name="expresscurate_sitemap_default_changefreq">
            <?php foreach($sitemapFrequencyArray as $key=>$val) {
                echo '<option value="' . $key . '"';
                if ($key == $frequency) {
                    echo ' selected="selected"';
                }
                echo '>' . $val . '</option>';
            }?>
    </select>
</li>
<li>
    <p class="title">Sitemap archiving frequency<span class="description ">Please select the frequency of archiving old post definitions in the sitemap</span></p>
    <select class="controls" name="expresscurate_sitemap_archiving">
        <option value="week" <?php
        if (get_option('expresscurate_sitemap_archiving') == 'week') {
            echo 'selected="selected"';
        }
        ?>>1 week
        </option>

        <option value="month" <?php
        if (get_option('expresscurate_sitemap_archiving') == 'month') {
            echo 'selected="selected"';
        }
        ?>>1 month
        </option>

        <option value="year" <?php
        if (get_option('expresscurate_sitemap_archiving') == 'year') {
            echo 'selected="selected"';
        }
        ?>>1 year
        </option>
    </select>
</li>
<li class="expresscurate_sitemap_webmasters">
    <p class="title">Submit sitemap automatically?<span class="description ">If this option is enabled, sitemap will be submitted to webmaster tools of search engines automatically.</span></p>
    <input class="expresscurate_displayNone" type="checkbox" id="expresscurate_sitemap_submit"
           name="expresscurate_sitemap_submit" <?php if (get_option('expresscurate_sitemap_submit') == 'on') echo 'checked'; ?>/>
    <label class="controls checkboxLabel" for="expresscurate_sitemap_submit"></label>
</li>
<li>
    <div class="sitemapUpdateFrequency <?php if (get_option('expresscurate_sitemap_submit') != 'on') { echo 'expresscurate_displayNone';}?> />">
        <p class="title">Sitemap submission frequency<span class="description ">How often should sitemap be submitted to search engines?</span></p>
        <select class="controls" name="expresscurate_sitemap_submit_frequency">
            <option value="always" <?php
            if (get_option('expresscurate_sitemap_submit_frequency') == 'always') {
                echo 'selected="selected"';
            }
            ?>>On every Post
            </option>
            <option value="daily" <?php
            if (get_option('expresscurate_sitemap_submit_frequency') == 'daily') {
                echo 'selected="selected"';
            }
            ?>>Daily
            </option>
            <option value="weekly" <?php
            if (get_option('expresscurate_sitemap_submit_frequency') == 'weekly') {
                echo 'selected="selected"';
            }
            ?>>Weekly
            </option>
            <option value="monthly" <?php
            if (get_option('expresscurate_sitemap_submit_frequency') == 'monthly') {
                echo 'selected="selected"';
            }
            ?>>Monthly
            </option>
        </select>
        <?php 
        $blogName = urlencode(urlencode(get_bloginfo('url')));
        ?>
            <a class="getApiKey  <?php if (strlen(get_option('expresscurate_google_refresh_token')) > 2) {echo 'expresscurate_displayNone';}?>" href="https://www.expresscurate.com/api/connector/google/webmasters/refreshtoken/<?=$blogName?>">Authorize access to Google Webmaster Tools</a>
    </div>
</li>
</ul>
<div class="centerSave">
    <?php @submit_button(); ?>
</div>
</form>
