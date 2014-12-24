<h2 class="expresscurate_displayNone">Sitemap.xml</h2>
<form class="expresscurate_marginTop20" method="post" action="options.php">
<?php @settings_fields('expresscurate-sitemap-group'); ?>
<?php @do_settings_fields('expresscurate-sitemap-group'); ?>

<?php //do_settings_sections('expresscurate');?>
<ul>
<li>
    <div class="title submitSitemapWrap">
        <a class="generate <?php if(!get_option('expresscurate_sitemap_generation_last_date')) { echo 'expresscurate_displayNone'; } ?>" id="submitSiteMap" href="#">Submit Sitemap</a>
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
<li>
    <p class="title">Sitemap manual priority value<span class="description ">Please select the priority for new posts in your sitemap</span></p>
    <select class="controls" name="expresscurate_sitemap_priority_manual_value">
        <option value="0.1" <?php
        if (get_option('expresscurate_sitemap_priority_manual_value') == '0.1') {
            echo 'selected="selected"';
        }
        ?>>0.1
        </option>
        <option value="0.2" <?php
        if (get_option('expresscurate_sitemap_priority_manual_value') == '0.2') {
            echo 'selected="selected"';
        }
        ?>>0.2
        </option>
        <option value="0.3" <?php
        if (get_option('expresscurate_sitemap_priority_manual_value') == '0.3') {
            echo 'selected="selected"';
        }
        ?>>0.3
        </option>
        <option value="0.4" <?php
        if (get_option('expresscurate_sitemap_priority_manual_value') == '0.4') {
            echo 'selected="selected"';
        }
        ?>>0.4
        </option>
        <option value="0.5" <?php
        if (get_option('expresscurate_sitemap_priority_manual_value') == '0.5') {
            echo 'selected="selected"';
        }
        ?>>0.5
        </option>
        <option value="0.6" <?php
        if (get_option('expresscurate_sitemap_priority_manual_value') == '0.6') {
            echo 'selected="selected"';
        }
        ?>>0.6
        </option>
        <option value="0.7" <?php
        if (get_option('expresscurate_sitemap_priority_manual_value') == '0.7') {
            echo 'selected="selected"';
        }
        ?>>0.7
        </option>
        <option value="0.8" selected <?php
        if (get_option('expresscurate_sitemap_priority_manual_value') == '0.8') {
            echo 'selected="selected"';
        }
        ?>>0.8
        </option>
        <option value="0.9" <?php
        if (get_option('expresscurate_sitemap_priority_manual_value') == '0.9') {
            echo 'selected="selected"';
        }
        ?>>0.9
        </option>
        <option value="1" <?php
        if (get_option('expresscurate_sitemap_priority_manual_value') == '1') {
            echo 'selected="selected"';
        }
        ?>>1
        </option>
    </select>
</li>
<li>
    <p class="title">Sitemap default changefreq<span class="description ">Please select the sitemap changefreq value for your new posts</span></p>
    <select class="controls" name="expresscurate_sitemap_default_changefreq">
        <option value="always" <?php
        if (get_option('expresscurate_sitemap_default_changefreq') == 'always') {
            echo 'selected="selected"';
        }
        ?>>always
        </option>

        <option value="hourly" <?php
        if (get_option('expresscurate_sitemap_default_changefreq') == 'hourly') {
            echo 'selected="selected"';
        }
        ?>>hourly
        </option>

        <option value="daily" <?php
        if (get_option('expresscurate_sitemap_default_changefreq') == 'daily') {
            echo 'selected="selected"';
        }
        ?>>daily
        </option>


        <option value="weekly" <?php
        if (get_option('expresscurate_sitemap_default_changefreq') == 'weekly') {
            echo 'selected="selected"';
        }
        ?>>weekly
        </option>


        <option value="monthly" <?php
        if (get_option('expresscurate_sitemap_default_changefreq') == 'monthly') {
            echo 'selected="selected"';
        }
        ?>>monthly
        </option>

        <option value="hourly" <?php
        if (get_option('expresscurate_sitemap_default_changefreq') == 'hourly') {
            echo 'selected="selected"';
        }
        ?>>hourly
        </option>


        <option value="yearly" <?php
        if (get_option('expresscurate_sitemap_default_changefreq') == 'yearly') {
            echo 'selected="selected"';
        }
        ?>>yearly
        </option>

        <option value="never" selected <?php
        if (get_option('expresscurate_sitemap_default_changefreq') == 'never') {
            echo 'selected="selected"';
        }
        ?>>never
        </option>
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
<li>
    <p class="title">Submit sitemap automatically?<span class="description ">If this option is enabled, sitemap will be submitted to webmaster tools of search engines automatically.</span></p>
    <input class="expresscurate_displayNone" type="checkbox" id="expresscurate_sitemap_submit_webmasters"
           name="expresscurate_sitemap_submit_webmasters" <?php if (get_option('expresscurate_sitemap_submit_webmasters') == 'on') echo 'checked'; ?>>
    <label class="controls checkboxLabel" for="expresscurate_sitemap_submit_webmasters"></label>
</li>
<li class="sitemapUpdateFrequency <?php if (get_option('expresscurate_sitemap_submit_webmasters') != 'on') { echo 'expresscurate_displayNone';}?> />">
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
    <?php if(!$_SESSION['sitemap_token']){ ?>
        <a class="getApiKey" href="admin-ajax.php?action=expresscurate_sitemap_submit">Get API Key </a>
    <?php } ?>
</li>
</ul>
<div class="centerSave">
    <?php @submit_button(); ?>
</div>
</form>