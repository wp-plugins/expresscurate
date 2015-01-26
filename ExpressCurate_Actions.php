<?php
/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */
require_once 'ExpressCurate_AjaxExportAPI.php';
require_once 'ExpressCurate_Keywords.php';
require_once 'ExpressCurate_ContentManager.php';
require_once 'ExpressCurate_Sitemap.php';
require_once 'ExpressCurate_FeedManager.php';
require_once 'ExpressCurate_Tags.php';

class ExpressCurate_Actions
{

    const POST_TYPE = "post";
    const COLUMN_NAME = "curated";
    const COLUMN_TITLE = "Curated";

    const SMART_PUBLISH_COLUMN_NAME = "smart_publish_date";
    const SMART_PUBLISH_COLUMN_TITLE = "Smart Publish date";

    const PLUGIN_FOLDER = "expresscurate";
    const PLUGIN_INNER_NAME = "expresscurate";
    const PLUGIN_NAME = "ExpressCurate";
    const PLUGIN_THEME = "ExpressCurate";
    const NEWS_FEED_URL = "http://news.expresscurate.com/feed/";
    const EXPRESSCURATE_URL = 'https://www.expresscurate.com/';
    const NEWS_FEED_COUNT = 10;

    //Extension
    const PCRE = 'pcre';
    const CURL = 'curl';
    const MBSTRING = 'mbstring';

    //functions
    const EXEC = 'exec';


    private $ajaxExportAPI = null;
    private $contentManager = null;
    private $keywords = null;
    private $feedManager = null;
    private $smartPublish = null;

    /**
     * Construct the plugin object
     */
    public function __construct()
    {
        $this->ajaxExportAPI = new ExpressCurate_AjaxExportAPI();
        $this->contentManager = new ExpressCurate_ContentManager();
        $this->keywords = new ExpressCurate_Keywords();
        $this->smartPublish = new ExpressCurate_SmartPublish();
        $this->sitemap = new ExpressCurate_Sitemap();
        $this->cronManager = new ExpressCurate_CronManager();
        $this->feedManager = new ExpressCurate_FeedManager();
        
        // register actions
        add_action('admin_init', array(&$this, 'register_settings'));
        add_action('admin_init', array(&$this, 'admin_init'));
        add_action('admin_menu', array(&$this, 'add_menu'));
        add_action('admin_init', array(&$this, 'expresscurate_add_widget'));
        add_action('admin_init', array(&$this, 'show_smart_publish_date_column'));
        add_action('admin_print_styles', array(&$this, 'expresscurate_admin_print_styles'));
        remove_action('admin_init', 'send_frame_options_header');
        add_action('init', array(&$this, 'init'));
        add_action('init', array(&$this, 'register_curated_post_status'), 0);
        add_action('init', array(&$this, 'expresscurate_buttons')); //'wpc_buttons'
        add_action('init', array(&$this, 'include_api'));
        add_filter('manage_edit-post_columns', array(&$this, 'curated_column_register'));
        add_action('manage_posts_custom_column', array(&$this, 'curated_column_display'), 10, 2);
        add_filter('manage_edit-post_sortable_columns', array(&$this, 'curated_column_register_sortable'));


        add_filter('request', array(&$this, 'curated_column_orderby'));
        add_action('wp_enqueue_scripts', array(&$this, 'expresscurate_theme_styles'));
        add_action('wp_head', array(&$this, 'add_expresscurate_seo'));
        remove_action('wp_head', 'rel_canonical');
        add_action('wp_head', array(&$this, 'expresscurate_advanced_seo_update_canonical_url'));
        add_filter('wp_title', array(&$this, 'expresscurate_advanced_seo_update_title'));
    }

    /**
     * hook into WP's init action hook
     */
    public function init()
    {
        add_action('admin_notices', array(&$this, 'check_plugins'));
        add_action('save_post', array(&$this, 'save_post'));
        add_filter('post_updated_messages', array(&$this, 'expresscurate_messages'));
        add_filter('mce_css', array(&$this, 'add_expresscurate_editor_style'));
        add_filter('show_admin_bar', array(&$this, 'expresscurate_hide_admin_bar'));
        add_action('admin_init', array(&$this, 'expresscurate_load_source'));

        // Above and below content filters
    }

// END public function init()
    /**
     * hook into WP's admin_init action hook
     */
    public function admin_init()
    {
        global $pagenow;
        if (!session_id()) {
            session_start();
        }

        add_action('admin_footer', array(&$this, 'add_inline_popup_content'));
        add_action('admin_footer', array(&$this, 'expresscurate_anonymous_tracking'));

        add_action('wp_ajax_expresscurate_export_api_get_terms', array($this->ajaxExportAPI, 'get_terms'));
        add_action('wp_ajax_expresscurate_export_api_check_auth', array($this->ajaxExportAPI, 'check_auth'));
        add_action('wp_ajax_expresscurate_export_api_check_images', array($this->ajaxExportAPI, 'check_images'));
        add_action('wp_ajax_expresscurate_export_api_download_images', array($this->ajaxExportAPI, 'download_images'));
        add_action('wp_ajax_expresscurate_export_api_save_post', array($this->ajaxExportAPI, 'save_post'));
        add_action('wp_ajax_expresscurate_export_api_check_source', array($this->ajaxExportAPI, 'check_source'));
        add_action('wp_ajax_expresscurate_export_api_send_google_key', array($this->ajaxExportAPI, 'send_google_key'));

        add_action('wp_ajax_expresscurate_get_article', array($this->contentManager, 'get_article'));
        add_action('wp_ajax_expresscurate_keywords_get_post_keyword_stats', array($this->keywords, 'get_post_keyword_stats'));
        add_action('wp_ajax_expresscurate_keywords_add_post_keyword', array($this->keywords, 'add_post_keyword'));
        add_action('wp_ajax_expresscurate_keywords_add_keyword', array($this->keywords, 'add_keyword'));
        add_action('wp_ajax_expresscurate_keywords_get_stats', array($this->keywords, 'get_stats'));
        add_action('wp_ajax_expresscurate_keywords_get_suggestions', array($this->keywords, 'suggestKeywordsFromGoogle'));
        add_action('wp_ajax_expresscurate_keywords_delete_keyword', array($this->keywords, 'delete_keyword'));
        add_action('wp_ajax_expresscurate_show_smart_publish', array(&$this, 'show_expresscurate_smart_publish_page'));
        add_action('wp_ajax_expresscurate_smart_publish_event', array(&$this->smartPublish, 'publish_event'));
        add_action('wp_ajax_expresscurate_save_sitemap_google_status', array(&$this->sitemap, 'saveSitemapGoogleStatus'));


        add_action('wp_ajax_expresscurate_feed_add', array($this->feedManager, 'add_feed'));
        add_action('wp_ajax_expresscurate_feed_delete', array($this->feedManager, 'delete_feed'));
        add_action('wp_ajax_expresscurate_bookmarks_add', array($this->feedManager, 'add_bookmarks'));
        add_action('wp_ajax_expresscurate_bookmark_set', array($this->feedManager, 'set_bookmark'));
        add_action('wp_ajax_expresscurate_bookmark_get', array($this->feedManager, 'get_bookmark'));
        add_action('wp_ajax_expresscurate_bookmarks_delete', array($this->feedManager, 'delete_bookmarks'));
        add_action('wp_ajax_expresscurate_delete_feed_content_items', array($this->feedManager, 'delete_feed_content_items'));
        add_action('wp_ajax_expresscurate_add_post_source', array($this->feedManager, 'add_post_source'));
        add_action('wp_ajax_expresscurate_delete_post_source', array($this->feedManager, 'delete_post_source'));
        add_action('wp_ajax_expresscurate_search_feed_bookmark', array($this->feedManager, 'search_feed_bookmark'));
        add_action('wp_ajax_expresscurate_get_feed', array($this->ajaxExportAPI, 'get_feed'));
        add_action('wp_ajax_expresscurate_export_api_get_post', array($this->ajaxExportAPI, 'get_post'));

        add_action('wp_ajax_expresscurate_sitemap_generate', array($this->ajaxExportAPI, 'generate_sitemap'));
        add_action('wp_ajax_expresscurate_sitemap_submit', array($this->sitemap, 'submitToGoogle'));
        add_action('wp_ajax_expresscurate_set_sitemap_permission_status', array( $this->sitemap, 'set_permission_status'));

        add_action('wp_ajax_expresscurate_set_cron_permission_status', array( $this->cronManager, 'set_permission_status'));


        add_action('wp_ajax_expresscurate_change_tab_event', array(&$this, 'change_tabs'));
        add_action('wp_ajax_expresscurate_change_layout_event', array(&$this, 'change_layout'));




        add_action('wp_dashboard_setup', array(&$this, 'add_dashboard_widgets'));
        add_filter('user_contactmethods', array(&$this, 'add_user_profile_metas'));
        if ($pagenow == 'post.php' || $pagenow == 'post-new.php') {
            add_action('media_buttons_context', array(&$this, 'add_expresscurate_custom_button'), 11);
            if (get_option('expresscurate_publish', '') == 'on') {
                add_action('post_submitbox_misc_actions', array($this, 'expresscurate_publish_box'));
            }
        }
        add_action('transition_post_status',array(&$this,'status_changes'),10,3);
        add_action( 'update_option_permalink_structure' , array(&$this,'permalink_changes'), 10, 2 );
        if ($_REQUEST['page'] == 'expresscurate_settings' ) {
            $cronManager = new ExpressCurate_CronManager();
            $cronManager->schedule_events();

            // Generate sitemap
            $seo = get_option('expresscurate_seo');
            $sitemap = new ExpressCurate_Sitemap();
            if('on' == $seo && !$sitemap->isExists()){
                $sitemap->generateSitemap();
            }
        }
    }
    public function register_settings()
    {
        // register plugin's settings
        register_setting('expresscurate-extension-group', 'expresscurate_post_status');
        register_setting('expresscurate-extension-group', 'expresscurate_def_cat');
        register_setting('expresscurate-extension-group', 'expresscurate_def_post_type');
        register_setting('expresscurate-group', 'expresscurate_max_tags');
     //   register_setting('expresscurate-group', 'expresscurate_max_tags');
        register_setting('expresscurate-keywords-group', 'expresscurate_defined_tags');
        register_setting('expresscurate-group', 'expresscurate_curated_text');
        register_setting('expresscurate-group', 'expresscurate_featured');
        register_setting('expresscurate-group', 'expresscurate_seo');
        register_setting('expresscurate-group', 'expresscurate_publisher');
        register_setting('expresscurate-group', 'expresscurate_publisher_twitter');
        register_setting('expresscurate-group', 'expresscurate_autosummary');
        register_setting('expresscurate-group', 'expresscurate_share');
        register_setting('expresscurate-group', 'expresscurate_smart_tagging');
        register_setting('expresscurate-group', 'expresscurate_quotes_style');
        register_setting('expresscurate-feed-group', 'expresscurate_enable_content_alert');
        register_setting('expresscurate-feed-group', 'expresscurate_pull_hours_interval');
        register_setting('expresscurate-feed-group', 'expresscurate_content_alert_frequency');
        register_setting('expresscurate-smartpublish-group', 'expresscurate_publish');
        register_setting('expresscurate-smartpublish-group', 'expresscurate_manually_approve_smart');
        register_setting('expresscurate-smartpublish-group', 'expresscurate_hours_interval');
        register_setting('expresscurate-sitemap-group', 'expresscurate_sitemap_generation_interval');
        register_setting('expresscurate-sitemap-group', 'expresscurate_sitemap_include_new_posts');
        register_setting('expresscurate-sitemap-group', 'expresscurate_sitemap_include_new_pages');
        register_setting('expresscurate-sitemap-group', 'expresscurate_sitemap_submit');
        register_setting('expresscurate-sitemap-group', 'expresscurate_sitemap_submit_frequency');
        register_setting('expresscurate-sitemap-group', 'expresscurate_sitemap_default_priority');
        register_setting('expresscurate-sitemap-group', 'expresscurate_sitemap_default_changefreq');
        register_setting('expresscurate-sitemap-group', 'expresscurate_sitemap_priority_manual_value');
        register_setting('expresscurate-sitemap-group', 'expresscurate_sitemap_archiving');
        //register_setting('expresscurate-sitemap-group', 'expresscurate_sitemap_token');
        register_setting('expresscurate-sitemap-generation-group', 'expresscurate_sitemap_generation_last_date');
    }

    public function add_options()
    {
        add_option('expresscurate_quotes_style', 'on');
        add_option('expresscurate_seo', 'on');
        add_option('expresscurate_smart_tagging', 'on');
    }


    public function expresscurate_load_source()
    {
        //open dialog
        global $pagenow;
        //make sure we are on the backend
        if (!is_admin())
            return false;
        if ($pagenow == 'post-new.php') { //check for new post page
            ?>
            <script type="text/javascript">
                window.expresscurate_load_url = <?php echo isset($_REQUEST['expresscurate_load_source']) ? "'" . $_REQUEST['expresscurate_load_source'] . "'" : 'null'; ?>;
            </script>
        <?php
        }
    }

    public function expresscurate_publish_box()
    {
        $smart_publishing = '';

        if ($GLOBALS['post']->post_status !== 'publish') {
            if (get_option('expresscurate_manually_approve_smart', '') == '' || get_post_meta($GLOBALS['post']->ID, '_expresscurate_smart_publish', true) == '1') {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }
            $smart_publishing = '<div class="misc-pub-section expresscurate_smart_puplish"><input type="checkbox" name="expresscurate_smart_publish_status" id="expresscurate_smart_publish_status" value="1" ' . $checked . '/><span><label for="expresscurate_smart_publish_status">&nbsp;&nbsp;Smart-Publish</label></span></div>';
        }
        echo $smart_publishing;
    }

    public function expresscurate_buttons()
    {
        add_filter("mce_external_plugins", array(&$this, 'expresscurate_add_plugins'));
        add_filter('mce_buttons', array(&$this, 'expresscurate_register_buttons'));
    }

    public function expresscurate_add_plugins($plugin_array)
    {
        $pluginUrl = plugin_dir_url(__FILE__);
        //$plugin_array['expresscurate'] = $pluginUrl . 'js/expresscurate_buttons.js';
        $plugin_array['expresscurate'] = $pluginUrl . 'js/Buttons.js';
        return $plugin_array;
    }

    public function expresscurate_register_buttons($buttons)
    {
        array_push($buttons, 'markKeywords');
        array_push($buttons, 'annotation');
        array_push($buttons, 'lefttextbox');
        array_push($buttons, 'justifytextbox');
        array_push($buttons, 'righttextbox');
        array_push($buttons, 'noFollow');
        array_push($buttons, 'wordCount');
        array_push($buttons, 'addKeyword');
        return $buttons;
    }

    public function add_expresscurate_editor_style($mce_css)
    {
        $pluginUrl = plugin_dir_url(__FILE__);
        if (!empty($mce_css))
            $mce_css .= ',';

        if (get_option('expresscurate_quotes_style') == "on") {
            $mce_css .= $pluginUrl . 'css/quotes-style.css,';
        }
        $mce_css .= $pluginUrl . 'css/theme-styles.css';
        return $mce_css;
    }

    public function add_expresscurate_custom_button($context)
    {
//the id of the container to be shown show in the popup
        $container_id = 'popup_container';

// popup's title
        $title = self::PLUGIN_NAME;

//append the icon
        $context .= "<a class='button expresscurate' title='{$title}'
    href='#' id='expresscurate_open-modal'>
    <span class='expresscurate_button_icon' /></span> Curate Content</a>";

        return $context;
    }

    public function add_inline_popup_content()
    {
        $display = false;

        $post_types = get_post_types('', 'names');

        foreach ($post_types as $post_type) {
            if (get_post_type() == $post_type) {
                $display = true;
            }
        }
        //$screen = get_current_screen();
        //if (strpos($screen->parent_file,'edit.php') == 0) {
        if ($display) {
            ?>
            <div id="expresscurate_dialog" class="expresscurate_dialog" title="<?php echo self::PLUGIN_NAME ?>">
                <?php include(sprintf("%s/templates/dialog.php", dirname(__FILE__))); ?>
            </div>
        <?php
        }
    }
    
    public function expresscurate_anonymous_tracking()
    {
         require_once 'ExpressCurate_Tracker.php';
         
         $tracker = ExpressCurate_Tracker::getInstance();
         $tracker->track();
    }

// Register the column
    function curated_column_register($columns)
    {
        return array_merge($columns, array(self::COLUMN_NAME => __(self::COLUMN_TITLE, self::PLUGIN_FOLDER)));
    }

// Display the column content
    public function curated_column_display($column_name, $post_id)
    {
        if ('curated' != $column_name)
            return;
        $curated = get_post_meta($post_id, '_is_expresscurate', true);
        if ($curated == 1) {
            $curated = '<em>' . __('Yes', self::PLUGIN_FOLDER) . '</em>';
        } else {
            $curated = '<em>' . __('No', self::PLUGIN_FOLDER) . '</em>';
        }
        echo $curated;
    }

// Register the column as sortable
    public function curated_column_register_sortable($columns)
    {
        $columns[self::COLUMN_NAME] = self::COLUMN_NAME;
        return $columns;
    }

    public function curated_column_orderby($vars)
    {
        if (isset($vars['orderby']) && '_is_expresscurated' == $vars['orderby']) {
            $vars = array_merge($vars, array(
                'meta_key' => '_is_expresscurate',
                'orderby' => 'meta_value_num'
            ));
        }

        return $vars;
    }

    function show_smart_publish_date_column()
    {
        if (get_option('expresscurate_publish')) {
            add_filter('manage_edit-post_columns', array(&$this, 'smart_publish_column_register'));
            add_action('manage_posts_custom_column', array(&$this, 'smart_publish_column_display'), 10, 2);
            add_filter('manage_edit-post_sortable_columns', array(&$this, 'smart_publish_column_register_sortable'));
        }
    }

    // Register the column
    function smart_publish_column_register($columns)
    {
        return array_merge($columns, array(self::SMART_PUBLISH_COLUMN_NAME => __(self::SMART_PUBLISH_COLUMN_TITLE, self::PLUGIN_FOLDER)));
    }

// Display the column content
    public function smart_publish_column_display($column_name, $post_id)
    {
        if ('smart_publish_date' != $column_name)
            return;

        $smartPublishDate = get_post_meta($post_id, 'smart_publish_date', true);
        if (!empty($smartPublishDate)) {
            $smartPublishDate = '<em>' . __($smartPublishDate, self::PLUGIN_FOLDER) . '</em>';
        } else {
            $smartPublishDate = '';
        }
        echo $smartPublishDate;
    }

// Register the column as sortable
    public function smart_publish_column_register_sortable($columns)
    {
        $columns[self::SMART_PUBLISH_COLUMN_NAME] = self::SMART_PUBLISH_COLUMN_NAME;
        return $columns;
    }

    /*    public function smart_publish_column_orderby($vars)
        {
            if (isset($vars['orderby']) && '_is_expresscurated' == $vars['orderby']) {
                $vars = array_merge($vars, array(
                    'meta_key' => '_is_expresscurate',
                    'orderby' => 'meta_value_num'
                ));
            }

            return $vars;
        }*/

    public function settings_section_expresscurate()
    {
// Think of this as help text for the section.
        echo 'These settings do things for ' . self::PLUGIN_NAME . '.';
    }

    /**
     * hook into WP's add_meta_boxes action hook
     */
    public function add_meta_boxes()
    {
// Add this metabox to every selected post

        add_meta_box(
            sprintf('expresscurate_%s_section', self::POST_TYPE), sprintf('%s ' . self::PLUGIN_NAME, ucwords(str_replace("_", " ", self::POST_TYPE))), array(&$this, 'add_inner_meta_boxes'), self::POST_TYPE
        );
    }

//register "curated" post status
    public function register_curated_post_status()
    {
        register_post_status('curated', array(
            'label' => _x(self::COLUMN_TITLE, 'post'),
            'public' => true,
            'expresscuratelude_from_search' => true,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop(self::COLUMN_TITLE . ' <span class="count">(%s)</span>', self::COLUMN_TITLE . ' <span class="count">(%s)</span>'),
        ));
    }

    public function add_inner_meta_boxes($post)
    {
        include(sprintf("%s/templates/dialog.php", dirname(__FILE__)));
    }

//    private function checkOpenTag($matches)
//    {
//        if (strpos($matches[0], '<') === false) {
//            return $matches[0];
//        } else {
//            return '<strong>' . $matches[1] . '</strong>' . $this->doReplace($matches[2]);
//        }
//    }
//
//    private function doReplace($html)
//    {
/*        return preg_replace_callback('/(\b' . $this->word . '\b)(.*?>)/i', array(&$this, 'checkOpenTag'), $html);*/
//    }
//
//    public function replace($html, $word)
//    {
//        $this->word = $word;
//
//        return $this->doReplace($html);
//    }

    public function generate_tags($post_id)
    {
        $tagsObj = new Expresscurate_Tags();
        $defined_tags = "";
        $post_tags = array();
        if (@get_option("expresscurate_defined_tags")) {
            $defined_tags = get_option("expresscurate_defined_tags");
        }
        if ($defined_tags) {
            $defined_tags = explode(",", $defined_tags);
        }
        $the_post = get_post($post_id);

// get the content of the post
        $post_content = $the_post->post_content;

        if (strpos($post_content, 'keywordsHighlight') !== false) {
            $post_content = $tagsObj->removeHighlights($post_content);
        }
        $tags = get_the_tags($post_id);
        if ($tags) {
            foreach ($tags as $tag) {
                $post_tags[$tag->term_id] = $tag->name;
            }
        }

        $content_tags = array();
        // Find all hashtags in text
        preg_match_all('/\s(?!<\w)(?=[^>]*(<|$))#\w+/iu', $post_content, $content_tags);

        foreach ($content_tags[0] as $content_tag) {

            $content_tag_insert = str_replace("#", "", trim($content_tag));
    //adding content tag to post tags if not exists
            if (!in_array($content_tag_insert, $post_tags)) {
                wp_set_post_tags($post_id, strtolower($content_tag_insert), true);
            }
        }
        if ($defined_tags && count($defined_tags)) {
            foreach ($defined_tags as $defined_tag) {
                $defined_tag_insert = trim($defined_tag);
                $defined_tag_insert = preg_replace('/\s+/', '[ ]', $defined_tag_insert);
                //$defined_tag_insert = preg_replace('/\s+/', '|', $defined_tag_insert);
                //adding defined tag to post tags if tag exists in posttitle or post content
                preg_match("/(?!<\w)(?=[^>]*(<|$))" . $defined_tag_insert . "(\W|$)/i", $post_content, $tag_in_content);
                if ((isset($tag_in_content[0]) || strpos($the_post->title, $defined_tag_insert)) && !in_array($defined_tag_insert, $post_tags)) {
                    wp_set_post_tags($post_id, $defined_tag_insert, true);
                }
            }
        }

        $tags = get_the_tags($post_id);
        $count_tags = count($tags);
        if ($tags && count($count_tags)) {
            $sorted_tags = array();
            foreach ($tags as $tag) {
                $tag_name = preg_replace('/\s+/', '[ ]', $tag->name);
                $sorted_tags[$tag_name]["count_words"] = str_word_count($tag_name);
                $sorted_tags[$tag_name]["name"] = $tag_name;
                $sorted_tags[$tag_name]["id"] = $tag->term_id;
            }

            usort($sorted_tags, create_function('$a,$b', 'return $b["count_words"] - $a["count_words"];'));
            $tags = $sorted_tags;
            $post_content = $tagsObj->removeTagLinks($post_content);


            foreach ($tags as $tag) {
                $tag_name = preg_replace('/\s+/', '[ ]', $tag["name"]);
                $post_content = $tagsObj->createTag($post_content, $tag_name, $tag["id"]);
            }//end tags
        }
        return $post_content;
    }

    public function get_metas($post_id = '', $meta = '_expresscurate_link_%', $type = 'post', $status = 'publish')
    {
        global $wpdb;
        $metas = array();
        if (empty($post_id))
            return;
        $r = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key LIKE {$meta}
        AND pm.post_id = '%s'
        AND p.post_status = '%s'
        AND p.post_type = '%s'
    ", $post_id, $status, $type));
        if (count($r)) {
            foreach ($r as $my_r) {
                $metas[$my_r->meta_key] = $my_r->meta_value;
            }
        }

        return $metas;
    }


    public function status_changes($new_status,$old_status,$post){
        if(("publish" == $old_status &&  "publish" != $new_status) || ("publish" == $new_status && "publish" != $old_status)){
            $this->generate_sitemap();
        }
    }

    public function permalink_changes( $old_permalink, $new_permalink )
    {
        if( $old_permalink!= $new_permalink){
            $this->generate_sitemap();
        }
    }


    public function generate_sitemap(){
        global $post;

        $sitemapUpdateFrequency = get_option('expresscurate_sitemap_generation_interval');
        $seo = get_option('expresscurate_seo');

        if('always' == $sitemapUpdateFrequency && 'on' == $seo){
            $sitemap = new ExpressCurate_Sitemap();
            $response = $sitemap->generateSitemap();
            if(!$response){
                $warning = get_option('expresscurate_not_writable_warning');
                $warning[$post->ID]['sitemap'] =  ' ExpressCurate tries to generate sitemap but it was not able to write sitemap.';
                update_option('expresscurate_not_writable_warning', $warning);

            }
        }
    }
    /**
     * Save the metaboxes for this custom post type
     */
    public function save_post($post_id)
    {

        $post_type = get_post_type($post_id);

        if ($post_type == 'acf') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_revision($post_id))
            return;
        $upload_dir = wp_upload_dir();


        // get the content of the post
        if (get_option('expresscurate_smart_tagging') == "on") {
            $post_content = $this->generate_tags($post_id);
        } else {
            $the_post = get_post($post_id);
            $post_content = $the_post->post_content;
            if (strpos($post_content, 'keywordsHighlight') !== false) {
                $tags_obj = new Expresscurate_Tags();
                $post_content = $tags_obj->removeHighlights($post_content);
            }
        }

        //Smart publishing
        if (get_option('expresscurate_publish', '') == 'on') {
            if (isset($_POST['expresscurate_smart_publish_status']) && $_POST['expresscurate_smart_publish_status'] == 1) {
                update_post_meta($post_id, '_expresscurate_smart_publish', 1);
            } else {
                update_post_meta($post_id, '_expresscurate_smart_publish', 0);
            }
        }

        $expresscurate_keywords = isset($_POST['expresscurate_defined_tags']) ? $_POST['expresscurate_defined_tags'] : '';
        update_post_meta($post_id, '_expresscurate_keywords', $expresscurate_keywords);
        //Seo part

        if (get_option('expresscurate_seo', '') == 'on') {
            $expresscurate_description = isset($_POST['expresscurate_description']) ? $_POST['expresscurate_description'] : '';
            update_post_meta($post_id, '_expresscurate_description', $expresscurate_description);
            // Advanced SEO part
            $expresscurate_advanced_seo_title = isset($_POST['expresscurate_advanced_seo_title']) ? $_POST['expresscurate_advanced_seo_title'] : '';
            $expresscurate_advanced_seo_canonical_url = isset($_POST['expresscurate_advanced_seo_canonical_url']) ? $_POST['expresscurate_advanced_seo_canonical_url'] : '';
            $expresscurate_advanced_seo_nofollow = isset($_POST['expresscurate_advanced_seo_nofollow_value']) ? $_POST['expresscurate_advanced_seo_nofollow_value'] : 'on';
            $expresscurate_advanced_seo_noindex = isset($_POST['expresscurate_advanced_seo_noindex_value']) ? $_POST['expresscurate_advanced_seo_noindex_value'] : 'on';

            update_post_meta($post_id, '_expresscurate_advanced_seo_title', esc_attr($expresscurate_advanced_seo_title));
            update_post_meta($post_id, '_expresscurate_advanced_seo_canonical_url', esc_attr($expresscurate_advanced_seo_canonical_url));
            update_post_meta($post_id, '_expresscurate_advanced_seo_nofollow', esc_attr($expresscurate_advanced_seo_nofollow));
            update_post_meta($post_id, '_expresscurate_advanced_seo_noindex', esc_attr($expresscurate_advanced_seo_noindex));
            
            // social part
            $expresscurate_advanced_seo_social_title = isset($_POST['expresscurate_advanced_seo_social_title']) ? $_POST['expresscurate_advanced_seo_social_title'] : '';
            $expresscurate_advanced_seo_social_shortdesc = isset($_POST['expresscurate_advanced_seo_social_shortdesc']) ? $_POST['expresscurate_advanced_seo_social_shortdesc'] : '';
            $expresscurate_advanced_seo_social_desc = isset($_POST['expresscurate_advanced_seo_social_desc']) ? $_POST['expresscurate_advanced_seo_social_desc'] : '';
            
            update_post_meta($post_id, '_expresscurate_advanced_seo_social_title', esc_attr($expresscurate_advanced_seo_social_title));
            update_post_meta($post_id, '_expresscurate_advanced_seo_social_shortdesc', esc_attr($expresscurate_advanced_seo_social_shortdesc));
            update_post_meta($post_id, '_expresscurate_advanced_seo_social_desc', esc_attr($expresscurate_advanced_seo_social_desc));
        }

        //Sitemap Settings
        $expresscurate_sitemap_post_configure_manually = isset($_POST['expresscurate_sitemap_post_configure_manually']) ? $_POST['expresscurate_sitemap_post_configure_manually'] : false;

        if ($expresscurate_sitemap_post_configure_manually) {

            $expresscurate_sitemap_post_exclude_from_sitemap = isset($_POST['expresscurate_sitemap_post_exclude_from_sitemap']) ? $_POST['expresscurate_sitemap_post_exclude_from_sitemap'] : '';

            if (!$expresscurate_sitemap_post_exclude_from_sitemap) {

                $expresscurate_sitemap_post_sitemap_update = isset($_POST['expresscurate_sitemap_post_sitemap_update']) ? $_POST['expresscurate_sitemap_post_sitemap_update'] : '';
                $expresscurate_sitemap_post_google_update = isset($_POST['expresscurate_sitemap_post_google_update']) ? $_POST['expresscurate_sitemap_post_google_update'] : '';
                $expresscurate_sitemap_post_frequency = isset($_POST['expresscurate_sitemap_post_frequency']) ? $_POST['expresscurate_sitemap_post_frequency'] : '';
                $expresscurate_sitemap_post_priority = isset($_POST['expresscurate_sitemap_post_priority']) ? $_POST['expresscurate_sitemap_post_priority'] : '';

              //  $expresscurate_sitemap_post_priority = ("auto-draft"!==get_post_status( $post_id )) ? $expresscurate_sitemap_post_priority : get_option('expresscurate_sitemap_priority_manual_value');
               // $expresscurate_sitemap_post_frequency = ("auto-draft"!==get_post_status( $post_id )) ? $expresscurate_sitemap_post_frequency : get_option('expresscurate_sitemap_default_changefreq');

                update_post_meta($post_id, '_expresscurate_sitemap_post_configure_manually', esc_attr($expresscurate_sitemap_post_configure_manually));
                update_post_meta($post_id, '_expresscurate_sitemap_post_exclude_from_sitemap', esc_attr($expresscurate_sitemap_post_exclude_from_sitemap));
                update_post_meta($post_id, '_expresscurate_sitemap_post_sitemap_update', esc_attr($expresscurate_sitemap_post_sitemap_update));
                update_post_meta($post_id, '_expresscurate_sitemap_post_google_update', esc_attr($expresscurate_sitemap_post_google_update));
                update_post_meta($post_id, '_expresscurate_sitemap_post_frequency', esc_attr($expresscurate_sitemap_post_frequency));
                // update_post_meta($post_id, 'expresscurate_sitemap_post_default_priority', esc_attr($expresscurate_sitemap_post_default_priority));
                update_post_meta($post_id, '_expresscurate_sitemap_post_priority', esc_attr($expresscurate_sitemap_post_priority));


            } else {
                update_post_meta($post_id, '_expresscurate_sitemap_post_configure_manually', esc_attr($expresscurate_sitemap_post_configure_manually));
                update_post_meta($post_id, '_expresscurate_sitemap_post_exclude_from_sitemap', esc_attr($expresscurate_sitemap_post_exclude_from_sitemap));
            }

        } else {
            update_post_meta($post_id, '_expresscurate_sitemap_post_configure_manually', esc_attr($expresscurate_sitemap_post_configure_manually));
        }

        //source part
        if (isset($_POST['expresscurate_sources']) && count($_POST['expresscurate_sources']) > 0) {
            $expresscurate_sources_meta_value = array();
            foreach ($_POST['expresscurate_sources'] as $i => $curated_data) {
                $curated_data = json_decode(stripslashes($curated_data), true);
                $expresscurate_sources_meta_value[$i]['title'] = $curated_data['title'];
                $expresscurate_sources_meta_value[$i]['link'] = $curated_data['link'];
                $expresscurate_sources_meta_value[$i]['domain'] = $curated_data['domain'];
            }
            update_post_meta($post_id, '_expresscurate_curated_data', wp_slash($expresscurate_sources_meta_value));
        }
        $image_made_featured = get_post_meta($post_id, '_expresscurate_image_made_featured', true);
        if ($image_made_featured && strlen($image_made_featured) > 0) {
            $post_content = str_replace($image_made_featured, '', $post_content);
        }

        // check if there is featured image
        $make_featured = get_option("expresscurate_featured", '');
        $make_featured = ($make_featured == '' || $make_featured == 1) && !has_post_thumbnail($post_id);

        // look for curated images
        preg_match_all('/\<img\s[^\>]*data-img-curated-from\s*=\s*(["\'])((?:\\.|(?!\1).)*)\1[^\>]*\>/i', $post_content, $curated_images);

        // proceed only if there are curated images and it's possible to upload them
        if (count($curated_images) > 0 && count($curated_images[0]) > 0) {
            $upload_dir_writable = is_writable($upload_dir['path']);
            if (!$upload_dir_writable) {
                $warning = get_option('expresscurate_not_writable_warning');
                $warning[$post_id]['upload'] = "Your upload dir is not writable";
                update_option('expresscurate_not_writable_warning', $warning);
            }

            $siteDomain = parse_url(get_site_url(), PHP_URL_HOST);
            if ($upload_dir_writable) {
                foreach ($curated_images[0] as $i => $full_image) {
                    // resolve image address
                    preg_match_all('/src\s*=\s*(["\'])((?:\\.|(?!\1).)*)\1/i', $full_image, $curated_image_sources);
                    // check so that the curated image has a source
                    if (count($curated_image_sources) > 0 && isset($curated_image_sources[2][0])) {
                        // now get the image and its source
                        $image = $curated_image_sources[2][0];
                        $image_from = $curated_images[2][$i];

                        // clean-up and check domain
                        $image = strtok($image, '?');
                        $image_filename = basename($image);
                        $domain = parse_url($image, PHP_URL_HOST);

                        // create attachement
                        $wp_filetype = wp_check_filetype($image_filename, null);
                        $attachment = array(
                            'post_mime_type' => $wp_filetype['type'],
                            'post_status' => 'inherit'
                        );

                        // check if download is required
                        if ($siteDomain != $domain || strpos($image, 'expresscurate_tmp')) {
                            // all set, try to download it
                            require_once(ABSPATH . 'wp-admin/includes/image.php');
                            $content_manager = new ExpressCurate_HtmlParser($image_from);
                            // download
                            $image_data = $content_manager->file_get_contents_utf8($image, false, false);
                            // create file
                            if (wp_mkdir_p($upload_dir['path'])) {
                                $file = $upload_dir['path'] . '/' . $image_filename;
                            } else {
                                $file = $upload_dir['basedir'] . '/' . $image_filename;
                            }
                            $file = strtok($file, '?');
                            file_put_contents($file, $image_data);

                            // now we have the attachement
                            // make it a featured image
                            $attach_id = wp_insert_attachment($attachment, $file, $post_id);
                            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                            wp_update_attachment_metadata($attach_id, $attach_data);

                            if ($make_featured) {
                                //set first image as featured
                                set_post_thumbnail($post_id, $attach_id);
                                update_post_meta($post_id, '_expresscurate_image_made_featured', $full_image);

                                $post_content = str_replace($full_image, '', $post_content);

                                // make sure second image doesn't become featured
                                $make_featured = 0;
                            } else {
                                $post_content = str_replace(
                                    $curated_image_sources[0][$i],
                                    'src="' . wp_get_attachment_url($attach_id) . '"',
                                    $post_content);
                            }
                        } else if ($make_featured) {
                            //  var_dump(parse_url($image));
                            // $image_filename = basename($image);
                            // create file
                            $image_path = parse_url($image);
                            $file = $image_path['path'];
//                            if (wp_mkdir_p($upload_dir['path'])) {
//                                $file = $upload_dir['path'] . '/' . $image_filename;
//                            } else {
//                                $file = $upload_dir['basedir'] . '/' . $image_filename;
//                            }
                            $file = strtok($file, '?');

                            // now we have the attachement
                            // make it a featured image
                            $attach_id = wp_insert_attachment($attachment, $file, $post_id);
                            $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                            wp_update_attachment_metadata($attach_id, $attach_data);

                            //set first image as featured
                            set_post_thumbnail($post_id, $attach_id);
                            update_post_meta($post_id, '_expresscurate_image_made_featured', $full_image);

                            $post_content = str_replace($full_image, '', $post_content);

                            // make sure second image doesn't become featured
                            $make_featured = 0;
                        }
                    }

                    // end foreach
                }
                // end writable if
            }
            // end curated image count if


        }

        preg_match_all('/\sdata-curated-url\s*=\s*(["\'])((?:\\.|(?!\1).)*)\1/i', $post_content, $curated_links);

        $curated_links_meta = $this->get_metas($post_id); //get_post_meta($post_id, '_expresscurate_links');
        if (!$curated_links_meta) {
            $curated_links_meta = array();
        } else {
            $curated_links_meta = $curated_links_meta[0];

            // delete metas
            foreach ($curated_links_meta as $i => $curated_links_meta) {
                delete_post_meta($post_id, '_expresscurate_link_' . $i, $curated_links_meta);
            }
        }

        if (isset($curated_links[2]) && count($curated_links[2]) > 0 ) {
            foreach ($curated_links[2] as $curated_link) {
                $curated_links_meta[] = $curated_link;
            }
            array_unique($curated_links_meta);
            foreach ($curated_links_meta as $i => $curated_links_meta) {
                update_post_meta($post_id, '_expresscurate_link_' . $i, $curated_links_meta);
            }
            update_post_meta($post_id, '_is_expresscurate', 1);
        }

        $curated_post = array(
            'ID' => $post_id,
            'post_content' => $post_content
        );
        //category
        $category = get_the_category($post_id);
        if (count($category) == 1 && $category[0]->cat_ID == 1) {
            $categories = array(get_option('expresscurate_def_cat'));
            wp_set_post_terms($post_id, $categories, 'category');
        }
        remove_action('save_post', array(&$this, 'save_post'));
        wp_update_post($curated_post);
        add_action('save_post', array(&$this, 'save_post'));

        if (wp_mkdir_p($upload_dir['path'])) {
            $this->ajaxExportAPI->delete_dir($upload_dir['path'] . '/expresscurate_tmp/' . $post_id);
        } else {
            $this->ajaxExportAPI->delete_dir($upload_dir['basedir'] . '/expresscurate_tmp/' . $post_id);
        }

        $post_issues = $this->post_analysis($post_content,has_post_thumbnail($post_id));
        if($post_issues > 0){
            $warning = get_option('expresscurate_not_writable_warning');
            $warning[$post_id]['analyzed'] = "Post Analyzed. View $post_issues <a class='expresscurateLink expresscurate_postAnalysis' href='#'>recommendations</a>. ";
            update_option('expresscurate_not_writable_warning', $warning);
        }

    }


    public function post_analysis($post_content , $has_featured)
    {
        $issues = 0;
        if(strlen($post_content) > 1){
            // Check words count
            preg_match_all('/[\pL\pN\pPd]+/u', strip_tags($post_content), $matches);
            $words_count = count($matches[0]);
            if( $words_count < 700 || $words_count > 1600 ){
                $issues++;
            }

            // Get blockquote
            $htmlParser = new ExpressCurate_HtmlParser();

                $text_inside_blockquote = $htmlParser->getTextBetweenTags('blockquote',$post_content);


            if(count($text_inside_blockquote) > 0) {
                $words_count_of_blockquote = 0;
                foreach($text_inside_blockquote as $content) {
                    preg_match_all('/[\pL\pN\pPd]+/u', strip_tags($content), $matches);
                    $words_count_of_blockquote += count($matches[0]);
                }
                if(round(($words_count_of_blockquote/$words_count)*100) > 20) {
                    $issues++;
                }
            }

            $img_in_post = $htmlParser->getTextBetweenTags('img',$post_content);
            if(count($img_in_post) < 1) {
                $issues++;
            }
        }else{
            $issues++;
        }

        if(!$has_featured) {
            $issues++;
        }
       return  $issues;

    }

    public function expresscurate_messages($m)
    {
        global $post;
        $notices = get_option('expresscurate_not_writable_warning');
        if (empty($notices))
            return $m;
        foreach ($notices as $post_id => $mm) {

            if ($post->ID == $post_id) {
                $notice = '';
                foreach($mm as $key){
                    $notice =  $notice . '<div id="message" class="error"><p>'.$key.'</p></div>';
                }
                foreach ($m['post'] as $i => $message) {
                    $m['post'][$i] = $message . $notice;
                }
                unset($notices[$post_id]);
                update_option('expresscurate_not_writable_warning', $notices);
                break;
            }
        }
        return $m;
    }

    /**
     * This function provides text inputs for settings fields
     */
    public function settings_field_input_text($args)
    {
// Get the field name from the $args array
        $field = $args['field'];
// Get the value of this setting
        $value = get_option($field);
// echo a proper input type="text"
        echo sprintf('<input type="text" name="%s" id="%s" value="%s" />', $field, $field, $value);
    }

    /**
     * add a menu
     */
    public function add_menu()
    {
// Add a page to manage this plugin's settings
        add_options_page(
            self::PLUGIN_NAME . ' Settings', self::PLUGIN_NAME, 'manage_options', 'expresscurate_settings', array(&$this, 'plugin_settings_page')
        );
        add_menu_page(self::PLUGIN_NAME, self::PLUGIN_NAME, 'edit_posts', 'expresscurate', array(&$this, 'show_expresscurate_dashboard'), '', '9.95458');
        add_submenu_page('expresscurate', 'Content Feed', 'Content Feed', 'edit_posts', 'expresscurate_feed_list', array(&$this, 'show_expresscurate_feed_list'), '');
        add_submenu_page('expresscurate', 'Bookmarks', 'Bookmarks', 'edit_posts', 'expresscurate_bookmarks', array(&$this, 'show_expresscurate_bookmarks'), '');
        add_submenu_page('expresscurate', 'Top Sources', 'Top Sources', 'edit_posts', 'expresscurate_websites', array(&$this, 'show_expresscurate_websites'), '');
        add_submenu_page('expresscurate', 'Keywords', 'Keywords', 'edit_posts', 'expresscurate_keywords', array(&$this, 'show_expresscurate_keywords'), '');
        add_submenu_page('expresscurate', 'Feeds', 'RSS Feeds', 'edit_posts', 'expresscurate_feeds', array(&$this, 'show_expresscurate_feed_dashboard'), '');
        add_submenu_page('expresscurate', 'Settings', 'Settings', 'manage_options', 'expresscurate_settings', array(&$this, 'plugin_settings_page'), '');
        add_submenu_page('expresscurate', 'News', 'News', 'edit_posts', 'expresscurate_news', array(&$this, 'show_expresscurate_news'), '');
        add_submenu_page('expresscurate', 'FAQ', 'FAQ', 'edit_posts', 'expresscurate_faq', array(&$this, 'show_expresscurate_faq_page'), '');
        add_submenu_page('expresscurate', 'Support', 'Support', 'edit_posts', 'expresscurate_support', array(&$this, 'show_expresscurate_support_page'), '');
    }

    //Add widget
    public function expresscurate_add_widget()
    {
        $seo = get_option('expresscurate_seo', '') == 'on';
        $post_types = array('post', 'page');
        $post_types = array_merge($post_types, get_post_types(array('_builtin' => false, 'public' => true), 'names'));
        foreach ($post_types as $post_type) {
            add_meta_box('expresscurate_sources_collection', ' Sources', array(&$this, 'expresscurate_sources_collection'), $post_type, 'normal', 'high');
            add_meta_box('expresscurate', ' SEO Control Center', array(&$this, 'expresscurate_meta_box'), $post_type, 'side', 'high');
            if ( $seo) {
                add_meta_box('expresscurate_advanced_seo', ' Advanced SEO', array(&$this, 'expresscurate_advanced_seo'), $post_type, 'normal', 'high');
            }
        }
    }

    public function expresscurate_meta_box()
    {
        ?>
        <div id="expresscurate_widget" class="expresscurate_widget">
            <?php include(sprintf("%s/templates/widget.php", dirname(__FILE__))); ?>
        </div>
    <?php
    }

    public function expresscurate_advanced_seo()
    {
        ?>
        <div id="expresscurate_advanced_seo_widget" class="expresscurate_advanced_seo_widget">
            <?php include(sprintf("%s/templates/advanced_seo_widget.php", dirname(__FILE__))); ?>
        </div>


    <?php
    }

    public function expresscurate_sources_collection()
    {
        ?>
        <div id="expresscurate_sources_collection_widget" class="expresscurate_sources_collection_widget">
            <?php include(sprintf("%s/templates/sources_coll_widget.php", dirname(__FILE__))); ?>
        </div>
    <?php
    }
    
    public function expresscurate_advanced_seo_update_title($default_title)
    {
        global $post;
        $title = $default_title;
        if (is_single() || is_page()) {
            $post_id = $post->ID;
            $title = get_post_meta($post_id, '_expresscurate_advanced_seo_title', true);
            $title = !empty($title) ? $title . ' | ' : '';
        }
        echo $title;
    }

    public function expresscurate_advanced_seo_update_canonical_url($default_title)
    {
        global $post;
        $post_id = $post->ID;
        $canonical_url = get_post_meta($post_id, '_expresscurate_advanced_seo_canonical_url', true);
        $canonical_url = !empty($canonical_url) ? $canonical_url : get_permalink($post_id);

        echo "<link rel='canonical' href='$canonical_url'>";
    }

    private function expresscurate_get_unique_keywords($keywords)
    {
        $small_keywords = array();
        foreach ($keywords as $word)
            $small_keywords[] = $this->strtolower($word);

        $keywords_ar = array_unique($small_keywords);
        return implode(',', $keywords_ar);
    }

    public function add_expresscurate_seo($post)
    {
        global $post;
        if (is_feed() || is_search() || is_404())
            return;
        $post_id = $post->ID;
        $meta_string = '';
        //(is_home() || is_page() || is_search() || is_category())
        if (!is_feed() && (get_option('expresscurate_seo', '')) == 'on' && (get_option('expresscurate_publisher', ''))) {
            $meta_string .= '<link href="' . get_option('expresscurate_publisher', '') . '" rel="publisher" />';
        }
        // TODO check whether is_author is applicable here
        if ((is_single() || is_page() || is_author()) && (get_option('expresscurate_seo', '') == 'on')) {
            $keywords = get_post_meta($post_id, '_expresscurate_keywords', true);
            $description = get_post_meta($post_id, '_expresscurate_description', true);
            if ($description && !is_author()) {
                $meta_string .= sprintf("<meta name=\"description\" content=\"%s\" />\n", $description);
            }
            $tags = get_the_tags();
            if ($tags) {
                foreach ($tags as $tag) {
                    if ($keywords) {
                        $keywords .= ',' . $tag->name;
                    } else {
                        $keywords .= $tag->name;
                    }
                }
            }
            if ($keywords && !is_author()) {
                if (!is_array($keywords)) {
                    $keword_arr = array_map('trim', explode(',', $keywords));
                } else {
                    $keword_arr = $keywords;
                }
                $keword_arr = array_unique($keword_arr);
                $keywords = wp_filter_nohtml_kses(str_replace('"', '', implode(',', $keword_arr)));
                $meta_string .= sprintf("<meta name=\"keywords\" content=\"%s\" />\n", $keywords);
            }

            $author_gplus = get_user_meta($post->post_author, 'expresscurate_gplus', true);

            if (strlen($author_gplus) > 1) {
                $meta_string .= '<link rel="author" href="' . $author_gplus . '"/>';
            }

            //Advanced Seo
            $this->add_expresscurate_advanced_seo_metas($post_id, $meta_string);
            
            // social seo
            $this->add_expresscurate_social_metas($post_id, $meta_string);
        }
        if ($meta_string != null) {
            echo "\n<!-- ExpressCurate SEO-->\n";
            echo "$meta_string\n";
            echo "<!-- /ExpressCurate SEO -->\n";
        }
    }

    private function add_expresscurate_advanced_seo_metas($post_id, &$meta_string)
    {
        $nofollow = get_post_meta($post_id, '_expresscurate_advanced_seo_nofollow', true) == 'on' ? 'NOFOLLOW' : '';
        $noindex = get_post_meta($post_id, '_expresscurate_advanced_seo_noindex', true) == 'on' ? 'NOINDEX' : '';
        if (!empty($nofollow) || !empty($noindex)) {
            $meta_string .= sprintf("<meta name=\"ROBOTS\" content=\"%s\" />\n", implode(', ', array_filter(array($nofollow, $noindex))));
        }
    }
    
    private function add_expresscurate_social_metas($post_id, &$meta_string)
    {
        $title = get_post_meta($post_id, '_expresscurate_advanced_seo_social_title', true);
        if(empty($title)) {
            $title = get_post_meta($post_id, '_expresscurate_advanced_seo_title', true);
        }
        if($title) {
            $title = esc_attr($title);
        }
        
        $shortdesc = esc_attr(get_post_meta($post_id, '_expresscurate_advanced_seo_social_shortdesc', true));
        $desc = esc_attr(get_post_meta($post_id, '_expresscurate_advanced_seo_social_desc', true));
        
        $featuredImageID = get_post_thumbnail_id($post_id);
        if($featuredImageID) {
            $featuredImage = wp_get_attachment_url(featuredImageID);
            $featuredImage = $featuredImage ? esc_url($featuredImage) : null;
        }
        
        $publisherTwitter = esc_attr(get_option('expresscurate_publisher_twitter'));
        $authorTwitter = esc_attr(get_the_author_meta('expresscurate_twitter'));
        
        $categories = wp_get_post_categories($post_id);
        if(!empty($categories)) {
            $cat = $categories[0];
            $categoryName = esc_attr($cat->name);
        }
        
        $tags = wp_get_post_tags($post_id);
        if(!empty($tags)) {
            $tagNames = array();
            foreach($tags as $tag) {
                $tagNames[] = $tag->name;
            }
        }
        
        $meta_string .= '<!-- Schema.org markup for Google+ -->';
        $meta_string .= '<meta itemprop="name" content="' . $title . '" />';
        $meta_string .= '<meta itemprop="description" content="' . $desc . '" />';
        if ($featuredImage) {
            $meta_string .= '<meta itemprop="image" content="' . $featuredImage . '" />';
        }
        
        $meta_string .= '<!-- Twitter Card data -->';
        // TODO check if featured image is set, then use summary_large_image otherwise use summary
        $meta_string .= '<meta name="twitter:card" content="' . ($featuredImage ? 'summary_large_image' : 'summary') . '" />';
        //$meta_string .= '<meta name="twitter:site" content="@publisher_handle" />';
        $meta_string .= '<meta name="twitter:title" content="' . $title . '" />';
        $meta_string .= '<meta name="twitter:description" content="' . $shortdesc . '" />';
        $meta_string .= '<meta name="twitter:creator" content="@' . $authorTwitter . '" />';
        $meta_string .= '<!-- Twitter summary card with large image must be at least 280x150px -->';
        if ($featuredImage) {
            $meta_string .= '<meta name="twitter:image:src" content="' . $featuredImage . '" />';
        }
        
        $meta_string .= '<!-- Open Graph data -->';
        $meta_string .= '<meta property="og:title" content="' . $title . '" />';
        $meta_string .= '<meta property="og:type" content="article" />';
        $meta_string .= '<meta property="og:url" content="' . esc_url(get_permalink($post_id)) . '" />';
        if ($featuredImage) {
            $meta_string .= '<meta property="og:image" content="' . $featuredImage . '" />';
        }
        $meta_string .= '<meta property="og:description" content="' . $desc . '" />';
        $meta_string .= '<meta property="og:site_name" content="' . get_bloginfo('name') . '" />';
        $meta_string .= '<meta property="article:published_time" content="' . get_post_time('Y-m-d\Tg:i:s', true, $post_id) . '" />';
        //$meta_string .= '<meta property="article:modified_time" content="' . get_post_time('Y-m-d\Tg:i:s', true, $post_id) . '" />';
        $meta_string .= '<meta property="article:section" content="' . $categoryName . '" />';
        $meta_string .= '<meta property="article:tag" content="' . $tagNames . '" />';
        //$meta_string .= '<meta property="fb:admins" content="Facebook numberic ID" />';
    }

    /**
     * Menu Callback
     */
    public function plugin_settings_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        $_SESSION['sitemap_token'] = false;
       /* if(get_option('expresscurate_seo', '' == "on") && get_option('expresscurate_sitemap_submit_webmasters') == 'on'){
          // $googleAuth = new ExpressCurate_GoogleAuth();
           // $responseToken = $googleAuth->getGoogleToken();
            $responseToken =  get_option('expresscurate_google_refresh_token');
            if($responseToken){
                $_SESSION['sitemap_token'] = true;
            }
        }*/

        // Render the settings template
        include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        
        // init page tracking
        global $expresscurate_track_page;
        $expresscurate_track_page = 'settings';
    }

    public function plugin_sitemap_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        include(sprintf("%s/templates/sitemap.php", dirname(__FILE__)));
    }

    public function show_expresscurate_news()
    {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        include(sprintf("%s/templates/news.php", dirname(__FILE__)));
        
        // init page tracking
        global $expresscurate_track_page;
        $expresscurate_track_page = 'news';
    }

    public function show_expresscurate_websites()
    {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        include(sprintf("%s/templates/websites.php", dirname(__FILE__)));
        
        // init page tracking
        global $expresscurate_track_page;
        $expresscurate_track_page = 'top-sources';
    }

    public function show_expresscurate_keywords()
    {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        include(sprintf("%s/templates/keywords.php", dirname(__FILE__)));
        
        // init page tracking
        global $expresscurate_track_page;
        $expresscurate_track_page = 'keywords';
    }

    public function show_expresscurate_feed_dashboard()
    {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        include(sprintf("%s/templates/feed_dashboard.php", dirname(__FILE__)));
        
        // init page tracking
        global $expresscurate_track_page;
        $expresscurate_track_page = 'content-feed';
    }


    public function show_expresscurate_feed_list()
    {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        include(sprintf("%s/templates/feed_list.php", dirname(__FILE__)));
        
        // init page tracking
        global $expresscurate_track_page;
        $expresscurate_track_page = 'rss-feeds';
    }

    public function show_expresscurate_bookmarks()
    {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        include(sprintf("%s/templates/bookmarks.php", dirname(__FILE__)));
        
        // init page tracking
        global $expresscurate_track_page;
        $expresscurate_track_page = 'bookmarks';
    }

    public function show_expresscurate_dashboard() {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        include(sprintf("%s/templates/dashboard.php", dirname(__FILE__)));

        // init page tracking
        global $expresscurate_track_page;
        $expresscurate_track_page = 'dashboard';
    }

    public function show_expresscurate_support_page()
    {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        include(sprintf("%s/templates/support.php", dirname(__FILE__)));
        
        // init page tracking
        global $expresscurate_track_page;
        $expresscurate_track_page = 'support';
    }


    public function show_expresscurate_license_page()
    {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        include(sprintf("%s/templates/license.php", dirname(__FILE__)));
        
        // init page tracking
        global $expresscurate_track_page;
        $expresscurate_track_page = 'license';
    }

    public function show_expresscurate_smart_publish_page()
    {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        include(sprintf("%s/templates/dashboard/smart_publishing_widget.php", dirname(__FILE__)));
        die;
    }

    public function show_expresscurate_faq_page()
    {
        if (!current_user_can('edit_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        include(sprintf("%s/templates/faq.php", dirname(__FILE__)));
        
        // init page tracking
        global $expresscurate_track_page;
        $expresscurate_track_page = 'faq';
    }

    public function expresscurate_admin_print_styles()
    {
        $pluginUrl = plugin_dir_url(__FILE__);
        wp_enqueue_script('expresscurate_utils', $pluginUrl . 'js/Utils.js', array('jquery','masonry','jquery-ui-sortable'));
        wp_enqueue_script('expresscurate_dialog', $pluginUrl . 'js/Dialog.js', array('jquery', 'jquery-ui-core', 'jquery-ui-dialog'));
        wp_enqueue_script('expresscurate_settings', $pluginUrl . 'js/Settings.js', array('jquery'));
        wp_enqueue_script('expresscurate_source_collection', $pluginUrl . 'js/sourceCollection.js', array('jquery'));
        wp_enqueue_script('expresscurate_bookmarks', $pluginUrl . 'js/bookmarks.js', array('jquery','masonry'));
        wp_enqueue_script('expresscurate_feed_settings', $pluginUrl . 'js/feed/feedSettings.js', array('jquery'));
        wp_enqueue_script('expresscurate_content_feed', $pluginUrl . 'js/feed/contentFeed.js', array('jquery','masonry'));

        wp_enqueue_script('expresscurate_keyword_utils', $pluginUrl . 'js/keywords/KeywordUtils.js', array('jquery'));
        wp_enqueue_script('expresscurate_keywords', $pluginUrl . 'js/keywords/Keywords.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'));
        wp_enqueue_script('expresscurate_seo_control_center', $pluginUrl . 'js/keywords/SEOControlCenter.js', array('jquery', 'jquery-ui-draggable', 'jquery-ui-droppable'));
        //
        wp_enqueue_style('texpresscurate', $pluginUrl . 'css/expresscurate.css');
        /* wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css'); */
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style('menu-expresscurate', $pluginUrl . 'css/menu-style-3.8.css');
        wp_enqueue_style('expresscurate', $pluginUrl . 'css/dialog-style-3.9.css');
    }

    public function expresscurate_theme_styles()
    {
        $pluginUrl = plugin_dir_url(__FILE__);
        wp_enqueue_style('texpresscurate', $pluginUrl . 'css/theme-styles.css');
        if (get_option('expresscurate_quotes_style') == "on") {
            wp_enqueue_style('quotesexpresscurate', $pluginUrl . 'css/quotes-style.css');
        }
    }

    public static function getCurationNews($url = self::NEWS_FEED_URL)
    {
        libxml_disable_entity_loader(false);
        $rss = new DOMDocument('1.0', 'UTF-8');
        $feed = array();
        if (ini_get('allow_url_fopen') && $rss->load($url, LIBXML_NOWARNING) === true) {
            foreach ($rss->getElementsByTagName('item') as $i => $node) {
                $item = array(
                    'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                    'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
                    'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
                    'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
                );
                array_push($feed, $item);
                if ($i == self::NEWS_FEED_COUNT) {
                    break;
                }
            }
        }
        return $feed;
    }

    public function add_user_profile_metas($profile_fields)
    {
        // Add new fields
        $profile_fields['expresscurate_twitter'] = 'Twitter Username';
        $profile_fields['expresscurate_facebook'] = 'Facebook URL';
        $profile_fields['expresscurate_gplus'] = 'Google+ URL';
        return $profile_fields;
    }

    private function get_match($regex, $content)
    {
        preg_match($regex, $content, $matches);
        return $matches[1];
    }

    private function pluginUrl()
    {
        return plugin_dir_url(__FILE__);
    }

    //Dashboard
    public function add_dashboard_widgets()
    {
        add_meta_box('dashboard_widget_keywords', 'Keywords Summary', array(&$this, 'keywords_widget'), get_current_screen(), 'side', 'high');
        add_meta_box('dashboard_widget_keywords_interest_over_time', 'Keywords Interest Over Time', array(&$this, 'keywords_interest_over_time_widget'), get_current_screen(), 'side', 'high');
        add_meta_box('dashboard_widget_keywords_related_topics', 'Keywords Related Topics', array(&$this, 'keywords_related_topics_widget'), get_current_screen(), 'side', 'high');

        if (get_option('expresscurate_publish', '') == "on") {
            add_meta_box('dashboard_widget_smartPublishing', 'Smart Publishing Overview', array(&$this, 'smart_publishing_widget'), get_current_screen(), 'side', 'high');
        }

        add_meta_box('dashboard_widget_feed', 'Feed', array(&$this, 'feed_widget'), get_current_screen(), 'side', 'high');
        add_meta_box('dashboard_widget_bookmarks', 'Bookmarks', array(&$this, 'bookmarks_widget'), get_current_screen(), 'side', 'high');
    }

    public function keywords_widget()
    {
        ?>
        <div id="expresscurate_keywords_widget" class="expresscurate_keywords_widget">
            <?php include(sprintf("%s/templates/dashboard/keywords_widget.php", dirname(__FILE__))); ?>
        </div>
    <?php
    }

    public function smart_publishing_widget()
    {
        ?>
        <div id="expresscurate_smart_publishing_widget" class="expresscurate_smart_publishing_widget">
            <?php include(sprintf("%s/templates/dashboard/smart_publishing_widget.php", dirname(__FILE__))); ?>
        </div>
    <?php
    }

    public function feed_widget()
    {
        ?>
        <div id="expresscurate_feed_widget" class="expresscurate_feed_widget">
            <?php include(sprintf("%s/templates/dashboard/feed_widget.php", dirname(__FILE__))); ?>
        </div>
    <?php
    }

    public function bookmarks_widget()
    {
        ?>
        <div id="expresscurate_bookmarks_widget" class="expresscurate_bookmarks_widget">
            <?php include(sprintf("%s/templates/dashboard/bookmarks_widget.php", dirname(__FILE__))); ?>
        </div>
    <?php
    }
    
    public function keywords_interest_over_time_widget()
    {
        ?>
        <div id="expresscurate_keywords_interest_over_time_widget" class="expresscurate_keywords_widget">
            <?php include(sprintf("%s/templates/dashboard/keywords_interest_over_time_widget.php", dirname(__FILE__))); ?>
        </div>
    <?php
    }
    
    public function keywords_related_topics_widget()
    {
        ?>
        <div id="expresscurate_keywords_related_topics_widget" class="expresscurate_keywords_widget">
            <?php include(sprintf("%s/templates/dashboard/keywords_related_topics_widget.php", dirname(__FILE__))); ?>
        </div>
    <?php
    }

    public function search_widget()
    {
        ?>
        <div id="expresscurate_search_widget" class="expresscurate_search_widget">
            <?php include(sprintf("%s/templates/dashboard/search_widget.php", dirname(__FILE__))); ?>
        </div>
    <?php
    }

    public function include_api()
    {
        include(sprintf("%s/ExpressCurate_API.php", dirname(__FILE__)));
    }

    public function expresscurate_hide_admin_bar()
    {
        if (isset($_REQUEST['hideadminmenu']) && $_REQUEST['hideadminmenu']=='true') {
            return false;
        }
        return is_user_logged_in();
    }

    public function change_tabs()
    {
        $_SESSION['settings_current_tab'] = $_POST['tab'];
    }
    public function change_layout()
    {
        $page = $_POST['page'];
        $layout = $_POST['layout'];
        update_option($page, $layout);
    }

    public function check_plugins() {

        $warnings = array();
        $extWarnings = array();
        $cronManager = new ExpressCurate_CronManager();
        $homeUrl = get_site_url();

        $homePath = get_home_path();


        if (!extension_loaded(self::PCRE)) {
            $extWarnings[] = self::PCRE;
        }

        if (!extension_loaded(self::CURL)) {
            $extWarnings[] = self::CURL;
        }

        if (!extension_loaded(self::MBSTRING)) {
            $extWarnings[] = self::MBSTRING;
        }

        if(count($extWarnings) > 0 ){
            $message = '<div class="update-nag">';
            foreach($extWarnings as $warning){
                $message .= 'You do not have  <b>'.$warning.'</b> extension.</br>';
            }
            $message .= 'Please install before using plugin! </div>';
            echo $message;
        }

        $blogName = urlencode(urlencode(get_bloginfo('url')));
        $expresscurateWebsiteUrl = self::EXPRESSCURATE_URL;
        if (strlen(get_option('expresscurate_google_refresh_token')) < 3 && get_option('expresscurate_sitemap_submit') == 'on') {
            $warnings[] = '<p>Authorise access to Google Webmasters. <a class="expresscurateLink" href="'.$expresscurateWebsiteUrl.'api/getsitemapkey/'.$blogName.'">Authorize </a>  |  <a class="expresscurateLink" href="options-general.php?page=expresscurate_settings"> Sitemap Settings </a></p>';
        }

        //Check cron permission

        //update_option('expresscurate_exec_function_permission_status',false);
        $exec_function_permission_status = get_option('expresscurate_exec_function_permission_status',false);
        $doesExecCommandExist = function_exists('exec');
        $doesCronPermissionExist = $cronManager->check_if_exist($cronManager->websiteUrlCallCronjob);
        if(!$doesExecCommandExist || $doesCronPermissionExist) {
            if(!$exec_function_permission_status || 'error' == $exec_function_permission_status){
                update_option('expresscurate_exec_function_permission_status','error');
                $cronjob ='0 * * * *  wget '.$homeUrl.' > /dev/null 2>&1';
                if(!$doesExecCommandExist){
                    $warnings[] = '
                           ExpressCurate was not able to schedule jobs required for Content Feed. Cause: Function <b>exec()</b> is disabled.
                           Please, enable <b>exec()</b> or add the following cron from the control panel of your hosting:
                    <p style="text-indent: 20px;font-weight: bolder">'.$cronjob.'</p>

                    For more details, contact  <a class="expresscurateLink" href="'.$homeUrl.'/wp-admin/admin.php?page=expresscurate_support"> Support </a></br></br>
                    <a class="expresscurateLink" href="#" id="exec_function_perm_seen" >Yes I have got it</a> | <a class="expresscurateLink" href="#" id="cron_setup_manually">I have already set it manually</a>

                    ';
                }elseif(!$doesCronPermissionExist){
                    $warnings[] = '
                          You do not have perrmission to add cronjob! Please add following cron from the control panel of your hosting:
                    <p style="text-indent: 20px;font-weight: bolder">'.$cronjob.'</p>

                    For more details, contact  <a class="expresscurateLink" href="'.$homeUrl.'/wp-admin/admin.php?page=expresscurate_support"> Support </a></br></br>
                    <a class="expresscurateLink" href="#" id="exec_function_perm_seen" >Yes I have got it</a> | <a class="expresscurateLink" href="#" id="cron_setup_manually">I have already set it manually</a>

                    ';
                }
            }elseif($exec_function_permission_status == 'set' &&  $_REQUEST['page'] == 'expresscurate_settings' ){
                $warnings[] = '
                           You have already set cron job manually from your host.</br>
                <a class="expresscurateLink" href="#" id="exec_function_perm_seen" >Yes I have got it</a>
                ';
            }
        }

        // Check sitemap update permission
        $sitemmapPath = $homePath . 'sitemap.xml';
        $sitemmapUrl = $homeUrl . '/sitemap.xml';
        $seo = get_option('expresscurate_seo');
        $expresscurateSitemapUpdatePermission = get_option('expresscurate_sitemap_update_permission',false);
        if('on' == $seo  && (!$expresscurateSitemapUpdatePermission || $expresscurateSitemapUpdatePermission == 'error')) {
            if(file_exists($sitemmapPath) && !is_writable($sitemmapPath)){
                update_option('expresscurate_sitemap_update_permission','error');
                $warnings[] =  '<p>
                           ExpressCurate was not able to write sitemap. Please, grant write access to file
                    <p style="text-indent: 20px;font-weight: bolder">'.$sitemmapUrl.'</p>
                    <a class="expresscurateLink"  href="'.$homeUrl.'/wp-admin/admin.php?page=expresscurate_settings"  > Sitemap settings</a> | <a class="expresscurateLink" href="#" id="expresscurate_sitemap_update_permission">Ignore</a>
                    </p>';
            }
        }

        if(count($warnings) > 0 ){
            $message = '';
            foreach($warnings as $warning){
                $message .= '<div class="notice notice-warning">'.$warning.'</div>';
            }

            echo $message;
        }

    }
}

