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

class ExpressCurate_Settings {

  const POST_TYPE = "post";
  const COLUMN_NAME = "curated";
  const COLUMN_TITLE = "Curated";
  const PLUGIN_FOLDER = "expresscurate";
  const PLUGIN_INNER_NAME = "expresscurate";
  const PLUGIN_NAME = "ExpressCurate";
  const PLUGIN_THEME = "ExpressCurate";
  const NEWS_FEED_URL = "http://news.expresscurate.com/feed/";
  const NEWS_FEED_COUNT = 10;

  private $ajaxExportAPI = null;
  private $keywords = null;

  /**
   * Construct the plugin object
   */
  public function __construct() {
    $this->ajaxExportAPI = new ExpressCurate_AjaxExportAPI();
    $this->contentManager = new ExpressCurate_ContentManager();
    $this->keywords = new ExpressCurate_Keywords();
    // register actions
    add_action('admin_init', array(&$this, 'admin_init'));
    add_action('admin_menu', array(&$this, 'add_menu'));
    add_action('admin_menu', array(&$this, 'expresscurate_add_widget'));
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
  }

  /**
   * hook into WP's init action hook
   */
  public function init() {
    add_action('save_post', array(&$this, 'save_post'));
    add_filter('post_updated_messages', array(&$this, 'expresscurate_messages'));
    add_filter('mce_css', array(&$this, 'add_expresscurate_editor_style'));

    // Above and below content filters
  }

// END public function init()
  /**
   * hook into WP's admin_init action hook
   */
  public function admin_init() {
    global $pagenow;
// register plugin's settings
    register_setting('expresscurate-group', 'expresscurate_post_status');
    register_setting('expresscurate-group', 'expresscurate_def_cat');
    register_setting('expresscurate-group', 'expresscurate_def_post_type');
    register_setting('expresscurate-group', 'expresscurate_max_tags');
    register_setting('expresscurate-group', 'expresscurate_defined_tags');
    register_setting('expresscurate-group', 'expresscurate_curated_text');
    register_setting('expresscurate-group', 'expresscurate_featured');
    register_setting('expresscurate-group', 'expresscurate_seo');
    register_setting('expresscurate-group', 'expresscurate_publisher');
    register_setting('expresscurate-group', 'expresscurate_autosummary');
    register_setting('expresscurate-group', 'expresscurate_share');
    register_setting('expresscurate-group', 'expresscurate_publish');
    register_setting('expresscurate-group', 'expresscurate_smart_tagging');
    register_setting('expresscurate-group', 'expresscurate_quotes_style');
    register_setting('expresscurate-group', 'expresscurate_hours_interval');
    register_setting('expresscurate-group', 'expresscurate_manually_approve_smart');
    add_action('admin_footer', array(&$this, 'add_inline_popup_content'));
    add_action('wp_ajax_expresscurate_export_api_get_terms', array($this->ajaxExportAPI, 'get_terms'));
    add_action('wp_ajax_expresscurate_export_api_check_auth', array($this->ajaxExportAPI, 'check_auth'));
    add_action('wp_ajax_expresscurate_export_api_check_images', array($this->ajaxExportAPI, 'check_images'));
    add_action('wp_ajax_expresscurate_export_api_download_images', array($this->ajaxExportAPI, 'download_images'));
    add_action('wp_ajax_expresscurate_export_api_save_post', array($this->ajaxExportAPI, 'save_post'));
    add_action('wp_ajax_expresscurate_export_api_check_source', array($this->ajaxExportAPI, 'check_source'));
    add_action('wp_ajax_expresscurate_get_article', array($this->contentManager, 'get_article'));
    add_action('wp_ajax_expresscurate_keywords_get_post_keyword_stats', array($this->keywords, 'get_post_keyword_stats'));
    add_action('wp_ajax_expresscurate_keywords_add_post_keyword', array($this->keywords, 'add_post_keyword'));
    add_action('wp_ajax_expresscurate_keywords_add_keyword', array($this->keywords, 'add_keyword'));
    add_action('wp_ajax_expresscurate_keywords_get_stats', array($this->keywords, 'get_stats'));
    add_action('wp_ajax_expresscurate_keywords_delete_keyword', array($this->keywords, 'delete_keyword'));
    add_action('wp_dashboard_setup', array(&$this, 'add_dashboard_widgets'));
    add_filter('user_contactmethods', array(&$this, 'add_user_profile_metas'));
    if ($pagenow == 'post.php' || $pagenow == 'post-new.php') {
      add_action('media_buttons_context', array(&$this, 'add_expresscurate_custom_button'), 11);
      if (get_option('expresscurate_publish', '') == 1) {
        add_action('post_submitbox_misc_actions', array($this, 'expresscurate_publish_box'));
      }
    }
  }

  public function expresscurate_publish_box() {
    $smart_publishing = '';
      if ($GLOBALS['post']->post_status !== 'publish') {
          if (get_option('expresscurate_manually_approve_smart', '') == 0 || get_post_meta($GLOBALS['post']->ID,'_expresscurate_smart_publish', true) == '1') {
        $checked = 'checked="checked"';
      } else {
        $checked = '';
      }
      $smart_publishing = '<div class="misc-pub-section expresscurate_smart_puplish"><input type="checkbox" name="expresscurate_smart_publish_status" id="expresscurate_smart_publish_status" value="1" ' . $checked . '/><span><label for="expresscurate_smart_publish_status">&nbsp;&nbsp;Smart-Publish</label></span></div>';
    }
    echo $smart_publishing;
  }

  public function expresscurate_buttons() {
    add_filter("mce_external_plugins", array(&$this, 'expresscurate_add_plugins'));
    add_filter('mce_buttons', array(&$this, 'expresscurate_register_buttons'));
  }

  public function expresscurate_add_plugins($plugin_array) {
    $pluginUrl = plugin_dir_url(__FILE__);
    //$plugin_array['expresscurate'] = $pluginUrl . 'js/expresscurate_buttons.js';
    $plugin_array['expresscurate'] = $pluginUrl . 'js/Buttons.js';
    return $plugin_array;
  }

  public function expresscurate_register_buttons($buttons) {
    if (get_option('expresscurate_seo', '') == 1) {
      array_push($buttons, 'markKeywords');
    }
    array_push($buttons, 'annotation');
    array_push($buttons, 'lefttextbox');
    array_push($buttons, 'justifytextbox');
    array_push($buttons, 'righttextbox');
    return $buttons;
  }

  public function add_expresscurate_editor_style($mce_css) {
    $pluginUrl = plugin_dir_url(__FILE__);
    if (!empty($mce_css))
      $mce_css .= ',';

    if (get_option('expresscurate_quotes_style') == "1" || get_option('expresscurate_quotes_style', '') == '') {
      $mce_css .= $pluginUrl . 'css/quotes-style.css,';
    }
    $mce_css .= $pluginUrl . 'css/theme-styles.css';
    return $mce_css;
  }

  public function add_expresscurate_custom_button($context) {
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

  public function add_inline_popup_content() {
    ?>
    <div id="expresscurate_dialog" class="expresscurate_dialog" title="<?php echo self::PLUGIN_NAME ?>">
      <?php include(sprintf("%s/templates/dialog.php", dirname(__FILE__))); ?>
    </div>
    <?php
  }

// Register the column
  function curated_column_register($columns) {
    return array_merge($columns, array(self::COLUMN_NAME => __(self::COLUMN_TITLE, self::PLUGIN_FOLDER)));
  }

// Display the column content
  public function curated_column_display($column_name, $post_id) {
    if ('curated' != $column_name)
      return;
    $curated = get_post_meta($post_id, 'is_expresscurate', true);
    if ($curated == 1) {
      $curated = '<em>' . __('Yes', self::PLUGIN_FOLDER) . '</em>';
    } else {
      $curated = '<em>' . __('No', self::PLUGIN_FOLDER) . '</em>';
    }
    echo $curated;
  }

// Register the column as sortable
  public function curated_column_register_sortable($columns) {
    $columns[self::COLUMN_NAME] = self::COLUMN_NAME;
    return $columns;
  }

  public function curated_column_orderby($vars) {
    if (isset($vars['orderby']) && 'is_expresscurated' == $vars['orderby']) {
      $vars = array_merge($vars, array(
          'meta_key' => 'is_expresscurate',
          'orderby' => 'meta_value_num'
      ));
    }

    return $vars;
  }

  public function settings_section_expresscurate() {
// Think of this as help text for the section.
    echo 'These settings do things for ' . self::PLUGIN_NAME . '.';
  }

  /**
   * hook into WP's add_meta_boxes action hook
   */
  public function add_meta_boxes() {
// Add this metabox to every selected post

    add_meta_box(
            sprintf('expresscurate_%s_section', self::POST_TYPE), sprintf('%s ' . self::PLUGIN_NAME, ucwords(str_replace("_", " ", self::POST_TYPE))), array(&$this, 'add_inner_meta_boxes'), self::POST_TYPE
    );
  }

//register "curated" post status
  public function register_curated_post_status() {
    register_post_status('curated', array(
        'label' => _x(self::COLUMN_TITLE, 'post'),
        'public' => true,
        'expresscuratelude_from_search' => true,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop(self::COLUMN_TITLE . ' <span class="count">(%s)</span>', self::COLUMN_TITLE . ' <span class="count">(%s)</span>'),
    ));
  }

  public function add_inner_meta_boxes($post) {
    include(sprintf("%s/templates/dialog.php", dirname(__FILE__)));
  }

  private function checkOpenTag($matches) {
    if (strpos($matches[0], '<') === false) {
      return $matches[0];
    } else {
      return '<strong>' . $matches[1] . '</strong>' . $this->doReplace($matches[2]);
    }
  }

  private function doReplace($html) {
    return preg_replace_callback('/(\b' . $this->word . '\b)(.*?>)/i', array(&$this, 'checkOpenTag'), $html);
  }

  public function replace($html, $word) {
    $this->word = $word;

    return $this->doReplace($html);
  }

  public function generate_tags($post_id) {
    $tagsObj = new Expresscurate_Tags();
    $defined_tags = "";
    $post_tags = array();
//    if (@get_option("expresscurate_defined_tags")) {
//      $defined_tags = get_option("expresscurate_defined_tags");
//    }
//    if ($defined_tags) {
//      $defined_tags = explode(",", $defined_tags);
//    }
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
    preg_match_all('/\s(?<!\w)(?=[^>]*(<|$))#\w+/iu', $post_content, $content_tags);

    foreach ($content_tags[0] as $content_tag) {
      $content_tag_insert = str_replace("#", "", trim($content_tag));
//adding content tag to post tags if not exists
      if (!in_array($content_tag_insert, $post_tags)) {
        wp_set_post_tags($post_id, strtolower($content_tag_insert), true);
      }
    }
    $tags = get_the_tags($post_id);
    $count_tags = count($tags);
    if ($tags && count($count_tags)) {

      $sorted_tags = array();

      foreach ($tags as $tag) {
        $tag_name = str_replace('/\s+/', '[ ]', $tag->name);
        $sorted_tags[$tag_name]["count_words"] = str_word_count($tag_name);
        $sorted_tags[$tag_name]["name"] = $tag_name;
        $sorted_tags[$tag_name]["id"] = $tag->term_id;
      }
      // For php5.3
//      usort($sorted_tags, function ($a, $b) {
//                return $b['count_words'] - $a['count_words'];
//              });
      usort($sorted_tags, create_function('$a,$b', 'return $b["count_words"] - $a["count_words"];'));
      $tags = $sorted_tags;
      $post_content = $tagsObj->removeTagLinks($post_content);
      foreach ($tags as $tag) {
        $tag_name = str_replace('/\s+/', '[ ]', $tag["name"]);
        $post_content = $tagsObj->createTag($post_content, $tag_name, $tag["id"]);
      }//end tags
    }
    return $post_content;
  }

  public function get_metas($post_id = '', $type = 'post', $status = 'publish') {
    global $wpdb;
    $metas = array();
    if (empty($post_id))
      return;
    $r = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, pm.meta_value FROM {$wpdb->postmeta} pm
        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key LIKE 'expresscurate_link_%'
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

  /**
   * Save the metaboxes for this custom post type
   */
  public function save_post($post_id) {
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
    if (get_option('expresscurate_smart_tagging') == "1" || get_option('expresscurate_smart_tagging', '') == '') {
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
    if (get_option('expresscurate_publish', '') == 1) {
      if (isset($_POST['expresscurate_smart_publish_status']) && $_POST['expresscurate_smart_publish_status'] == 1) {
        update_post_meta($post_id, '_expresscurate_smart_publish', 1);
      }
    }

    //Seo part
    if (get_option('expresscurate_seo', '') == 1) {
      $expresscurate_keywords = isset($_POST['expresscurate_defined_tags']) ? $_POST['expresscurate_defined_tags'] : '';
      $expresscurate_description = isset($_POST['expresscurate_description']) ? $_POST['expresscurate_description'] : '';
      update_post_meta($post_id, '_expresscurate_keywords', $expresscurate_keywords);
      update_post_meta($post_id, '_expresscurate_description', $expresscurate_description);
    }

//check if content curated
    $curated_content = preg_match_all('/data-curated-url=["\']?([^"\' ]*)["\' ]/is', $post_content, $curated_links);
//preg_match('/^<a.*?href=(["\'])(.*?)\1.*$/', $post_content, $link);

    $curated_before = get_post_meta($post_id, 'is_expresscurate');
    if ($curated_content > 0 || $curated_before == 1 && current_user_can('edit_post', $post_id)) {
//download images
      $images = array();
      preg_match_all("/\<img\s[^\>]*src\s*=\s*([\"'])(((?!\1).)*)\1/i", $post_content, $images);
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      $siteDomain = parse_url(get_site_url(), PHP_URL_HOST);
      if (count($images) > 0 && is_writable($upload_dir['path'])) {
        $content_manager = new ExpressCurate_HtmlParser($images[1][0]);
        foreach ($images[1] as $i => $image) {
          $image = strtok($image, '?');
          $domain = parse_url($image, PHP_URL_HOST);
          if ($siteDomain != $domain || strpos($image, 'expresscurate_tmp')) {
            //$options = array('http' => array('user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36'));
            //$context = stream_context_create($options);
            $image_data = $content_manager->file_get_contents_utf8($image, false, false);
            $filename[$i] = basename($image);
            if (wp_mkdir_p($upload_dir['path'])) {
              $file[$i] = $upload_dir['path'] . '/' . $filename[$i];
            } else {
              $file[$i] = $upload_dir['basedir'] . '/' . $filename[$i];
            }
            $file[$i] = strtok($file[$i], '?');
            file_put_contents($file[$i], $image_data);
            $wp_filetype = wp_check_filetype($filename[$i], null);
            $attachment[$i] = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_status' => 'inherit'
            );
            //if need to set first image as featured
            if ((get_option("expresscurate_featured", '') == '' || get_option("expresscurate_featured", '') == 1) && !has_post_thumbnail($post_id) && $i == 0) {
              $attach_id[$i] = wp_insert_attachment($attachment[$i], $file[$i], $post_id);
              $attach_data[$i] = wp_generate_attachment_metadata($attach_id[$i], $file[$i]);
              wp_update_attachment_metadata($attach_id[$i], $attach_data[$i]);
              //set first image as featured
              set_post_thumbnail($post_id, $attach_id[0]);
              $post_content = preg_replace('#<img([^>]+)>#i', '', $post_content, 1);
              //preg_match('/\<img(.*?)\/\>/', $post_content, $aaa);
            } else {
              $attach_id[$i] = wp_insert_attachment($attachment[$i], $file[$i], $post_id);
              //$attach_data[$i] = wp_generate_attachment_metadata($attach_id[$i], $file[$i]);
              $post_content = preg_replace(
                      '#src="' . $image . '"+#', 'src="' . wp_get_attachment_url($attach_id[$i]) . '"', $post_content
              );
            }
          }
        }
      } else {
        if (!is_writable($upload_dir['path'])) {
          $warning = get_option('expresscurate_not_writable_warning');
          $warning[$post_id] = "Your upload dir is not writable";
          update_option('expresscurate_not_writable_warning', $warning);
        }
      }
      $curated_links_meta = $this->get_metas($post_id); //get_post_meta($post_id, 'expresscurate_links');
      if (!$curated_links_meta) {
        $curated_links_meta = array();
      } else {
        $curated_links_meta = $curated_links_meta[0];
      }
      if (isset($curated_links[1])) {
        foreach ($curated_links[1] as $curated_link) {
          $curated_links_meta[] = $curated_link;
        }
      }
      array_unique($curated_links_meta);
      foreach ($curated_links_meta as $i => $curated_links_meta) {
        update_post_meta($post_id, 'expresscurate_link_' . $i, $curated_links_meta);
      }
      update_post_meta($post_id, 'is_expresscurate', 1);

      $curated_post = array(
          'ID' => $post_id,
          'post_content' => $post_content,
          'status' => 'published'
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
    } else {

      if (get_option('expresscurate_smart_tagging') == "1" || get_option('expresscurate_smart_tagging', '') == '') {
        $post_content = $this->generate_tags($post_id);
      } else {
        $the_post = get_post($post_id);
        $post_content = $the_post->post_content;
        if (strpos($post_content, 'keywordsHighlight') !== false) {
          $tags_obj = new Expresscurate_Tags();
          $post_content = $tags_obj->removeHighlights($post_content);
        }
      }
      $curated_post = array(
          'ID' => $post_id,
          'post_content' => $post_content,
      );
      remove_action('save_post', array(&$this, 'save_post'));
      wp_update_post($curated_post);
      add_action('save_post', array(&$this, 'save_post'));
      return;
    }
    if (wp_mkdir_p($upload_dir['path'])) {
      $this->ajaxExportAPI->delete_dir($upload_dir['path'] . '/expresscurate_tmp/' . $post_id);
    } else {
      $this->ajaxExportAPI->delete_dir($upload_dir['basedir'] . '/expresscurate_tmp/' . $post_id);
    }
  }

  public function expresscurate_messages($m) {
    global $post;
    $notice = get_option('expresscurate_not_writable_warning');
    if (empty($notice))
      return $m;
    foreach ($notice as $post_id => $mm) {
      if ($post->ID == $post_id) {
        foreach ($m['post'] as $i => $message) {
          $m['post'][$i] = $message . '<div id="message" class="error"><p>' . $mm . '</p></div>';
        }
        unset($notice[$post_id]);
        update_option('expresscurate_not_writable_warning', $notice);
        break;
      }
    }
    return $m;
  }

  /**
   * This function provides text inputs for settings fields
   */
  public function settings_field_input_text($args) {
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
  public function add_menu() {
// Add a page to manage this plugin's settings
    add_options_page(
            self::PLUGIN_NAME . ' Settings', self::PLUGIN_NAME, 'manage_options', 'expresscurate_settings', array(&$this, 'plugin_settings_page')
    );
    add_menu_page(self::PLUGIN_NAME, self::PLUGIN_NAME, 'edit_posts', 'expresscurate', array(&$this, 'show_expresscurate_support_page'), '', '9.95458');
    add_submenu_page('expresscurate', self::PLUGIN_NAME, '', 'edit_posts', 'expresscurate', array(&$this, 'show_expresscurate_support_page'), '');
    add_submenu_page('expresscurate', 'Top Sources', 'Top Sources', 'edit_posts', 'expresscurate_websites', array(&$this, 'show_expresscurate_websites'), '');
    add_submenu_page('expresscurate', 'Keywords', 'Keywords', 'edit_posts', 'expresscurate_keywords', array(&$this, 'show_expresscurate_keywords'), '');
    add_submenu_page('expresscurate', 'Settings', 'Settings', 'manage_options', 'expresscurate_settings', array(&$this, 'plugin_settings_page'), '');
    add_submenu_page('expresscurate', 'News', 'News', 'edit_posts', 'expresscurate_news', array(&$this, 'show_expresscurate_news'), '');
    add_submenu_page('expresscurate', 'FAQ', 'FAQ', 'edit_posts', 'expresscurate_faq', array(&$this, 'show_expresscurate_faq_page'), '');
    add_submenu_page('expresscurate', 'Support', 'Support', 'edit_posts', 'expresscurate', array(&$this, 'show_expresscurate_support_page'), '');

  }

  //Add widget
  public function expresscurate_add_widget() {
    if (get_option('expresscurate_seo', '') == 1) {
      $post_types = array('post', 'page');
      $post_types = array_merge($post_types, get_post_types(array('_builtin' => false, 'public' => true), 'names'));
      foreach ($post_types as $post_type) {
        add_meta_box('expresscurate', ' SEO Control Center', array(&$this, 'expresscurate_meta_box'), $post_type, 'side', 'high');
      }
    }
  }

  public function expresscurate_meta_box() {
    global $post;
    ?>
    <div id="expresscurate_widget" class="expresscurate_widget" title="<?php echo self::PLUGIN_NAME ?>">
      <?php include(sprintf("%s/templates/widget.php", dirname(__FILE__))); ?>
    </div>


    <?php
  }

  public function add_expresscurate_seo($post) {
    global $post;
    if (is_feed() || is_search() || is_404())
      return;
    $post_id = $post->ID;
    $meta_string = '';
    //(is_home() || is_page() || is_search() || is_category())
    if (!is_feed() && (get_option('expresscurate_seo', '')) == 1 && (get_option('expresscurate_publisher', ''))) {
      $meta_string .= '<link href="' . get_option('expresscurate_publisher', '') . '" rel="publisher" />';
    }
    if ((is_single() || is_page() || is_author()) && (get_option('expresscurate_seo', '') == 1)) {
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
    }
    if ($meta_string != null) {
      echo "\n<!-- ExpressCurate SEO-->\n";
      echo "$meta_string\n";
      echo "<!-- /ExpressCurate SEO -->\n";
    }
  }

  private function expresscurate_get_unique_keywords($keywords) {
    $small_keywords = array();
    foreach ($keywords as $word)
      $small_keywords[] = $this->strtolower($word);

    $keywords_ar = array_unique($small_keywords);
    return implode(',', $keywords_ar);
  }

  /**
   * Menu Callback
   */
  public function plugin_settings_page() {
    if (!current_user_can('manage_options')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }
// Render the settings template
    include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
  }

  public function show_expresscurate_news() {
    if (!current_user_can('edit_posts')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }
// Render the settings template
    include(sprintf("%s/templates/news.php", dirname(__FILE__)));
  }

  public function show_expresscurate_paid_version() {
      if (!current_user_can('edit_posts')) {
          wp_die(__('You do not have sufficient permissions to access this page.'));
      }
// Render the settings template
        include(sprintf("%s/templates/paid.php", dirname(__FILE__)));
  }

  public function show_expresscurate_websites() {
    if (!current_user_can('edit_posts')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }
// Render the settings template
    include(sprintf("%s/templates/websites.php", dirname(__FILE__)));
  }

  public function show_expresscurate_keywords() {
    if (!current_user_can('edit_posts')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }
// Render the settings template
    include(sprintf("%s/templates/keywords.php", dirname(__FILE__)));
  }

  public function show_expresscurate_support_page() {
    if (!current_user_can('edit_posts')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }
// Render the support template
    include(sprintf("%s/templates/support.php", dirname(__FILE__)));
  }

  public function show_expresscurate_faq_page() {
    if (!current_user_can('edit_posts')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }
// Render the support template
    include(sprintf("%s/templates/faq.php", dirname(__FILE__)));
  }

  public function expresscurate_admin_print_styles() {
    $plaugunUrl = plugin_dir_url(__FILE__);
    //wp_enqueue_script('expresscurate', $plaugunUrl . 'js/expresscurate.js', array('jquery', 'jquery-ui-core', 'jquery-ui-dialog'));
    //wp_enqueue_script('expresscurate_keywords', $plaugunUrl . 'js/keywords.js', array('jquery', 'jquery-ui-core', 'jquery-ui-dialog'));
    //
    wp_enqueue_script('expresscurate_menu', $plaugunUrl . 'js/Menu.js');
    wp_enqueue_script('expresscurate_dialog', $plaugunUrl . 'js/Dialog.js', array('jquery', 'jquery-ui-core', 'jquery-ui-dialog'));
    wp_enqueue_script('expresscurate_settings', $plaugunUrl . 'js/Settings.js', array('jquery'));
    wp_enqueue_script('expresscurate_support', $plaugunUrl . 'js/Support.js', array('jquery'));

    wp_enqueue_script('expresscurate_keyword_utils', $plaugunUrl . 'js/keywords/KeywordUtils.js', array('jquery'));
    wp_enqueue_script('expresscurate_keywords', $plaugunUrl . 'js/keywords/Keywords.js', array('jquery'));
    wp_enqueue_script('expresscurate_seo_control_center', $plaugunUrl . 'js/keywords/SEOControlCenter.js', array('jquery'));
    wp_enqueue_script('expresscurate_keywords_dashboard_widget', $plaugunUrl . 'js/keywords/DashboardWidget.js', array('jquery'));
    //
    wp_enqueue_style('texpresscurate', $plaugunUrl . 'css/expresscurate.css');
    wp_enqueue_style('wp-jquery-ui-dialog');
    wp_enqueue_style('menu-expresscurate', $plaugunUrl . 'css/menu-style-3.8.css');
    wp_enqueue_style('expresscurate', $plaugunUrl . 'css/dialog-style-3.9.css');
  }

  public function expresscurate_theme_styles() {
    $plaugunUrl = plugin_dir_url(__FILE__);
    wp_enqueue_style('texpresscurate', $plaugunUrl . 'css/theme-styles.css');
    if (get_option('expresscurate_quotes_style') == "1" || get_option('expresscurate_quotes_style', '') == '') {
      wp_enqueue_style('quotesexpresscurate', $plaugunUrl . 'css/quotes-style.css');
    }
  }

  public static function getCurationNews($url = self::NEWS_FEED_URL) {
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

  public function add_user_profile_metas($profile_fields) {
    // Add new fields
    $profile_fields['expresscurate_twitter'] = 'Twitter Username';
    $profile_fields['expresscurate_facebook'] = 'Facebook URL';
    $profile_fields['expresscurate_gplus'] = 'Google+ URL';
    return $profile_fields;
  }

  //publish posts curated by chrome extension
  public function publish_event() {
    if (get_option('expresscurate_post_status', '') !== 'draft' || !get_option('expresscurate_publish', '') || get_option('expresscurate_publish', '') === 0) {
      return;
    }
    $args = array(
        'posts_per_page' => 1,
        'post_type' => get_option('expresscurate_def_post_type', 'post'),
        'post_status' => 'draft',
        'orderby' => 'post_date',
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key' => 'expresscurate_chrome',
                'value' => '1'
            ),
            array(
                'key' => '_expresscurate_smart_publish',
                'value' => '1'
            )
        )
    );
    $posts = new WP_Query($args);
    $recentargs = array(
        'numberposts' => 1,
        'offset' => 0,
        'orderby' => 'post_date',
        'order' => 'DESC',
        'post_type' => 'post',
        'post_status' => 'publish',
        'suppress_filters' => true);

    $recent_posts = wp_get_recent_posts($recentargs, ARRAY_A);
    $now = date('Y-m-d h:i:s');
    $hourdiff = round((strtotime($recent_posts[0]['post_date']) - strtotime($now)) / (60 * 60));
    if ($hourdiff >= get_option("expresscurate_hours_interval") && $posts->have_posts()) {
      wp_update_post(array('ID' => $posts->posts[0]->ID, 'post_status' => 'publish'));
      update_option('expresscurate_publish_mail_sent', 0);
    } elseif (!$posts->have_posts() && get_settings('admin_email') && get_option('expresscurate_publish_mail_sent', '0') == "0") {
      $subject = "ExpressCurate Smart Publishing Status";
        $blogName = get_bloginfo('Name');
        $blogUrl = get_bloginfo('url');
        $message = "$blogName\n
                        $blogUrl\n
                        There is no curated posts to publish \n";
      wp_mail(get_settings('admin_email'), $subject, $message);
      update_option('expresscurate_publish_mail_sent', 1);
    }
  }

  private function get_match($regex, $content) {
    preg_match($regex, $content, $matches);
    return $matches[1];
  }

  private function pluginUrl() {
    return plugin_dir_url(__FILE__);
  }

  //Dashboard
  public function add_dashboard_widgets() {
    add_meta_box('dashboard_widget_exck', 'Keywords Summary', array(&$this, 'keywords_widget'), get_current_screen(), 'side', 'high');
    //add_meta_box('dashboard_widget_excs', 'ExpressCurate Search', array(&$this, 'search_widget'), get_current_screen(), 'side', 'high');
  }

  public function keywords_widget() {
    ?>
    <div id="expresscurate_keywords_widget" class="expresscurate_keywords_widget" title="<?php echo self::PLUGIN_NAME ?>">
      <?php include(sprintf("%s/templates/dashboard/keywords_widget.php", dirname(__FILE__))); ?>
    </div>
    <?php
  }

  public function search_widget() {
    ?>
    <div id="expresscurate_search_widget" class="expresscurate_search_widget  " title="<?php echo self::PLUGIN_NAME ?>">
      <?php include(sprintf("%s/templates/dashboard/search_widget.php", dirname(__FILE__))); ?>
    </div>
    <?php
  }

  public function include_api() {
    include(sprintf("%s/ExpressCurate_API.php", dirname(__FILE__)));
  }

}

class Expresscurate_Tags {

  private function checkOpenTag($matches) {
    if (strpos($matches[3], '</a') < strpos($matches[3], '<a')) {
      return $matches[0];
    } else {
      return '<a class="expresscurate_contentTags" href="' . get_tag_link($this->tag_id) . '">#' . strtolower($matches[0]) . '</a>';
    }
  }

  private function doReplace($html) {
    return preg_replace_callback('/(\b' . $this->word . '\b)(?=[^>]*(<|$))(?=(.*?>))/Uuis', array(&$this, 'checkOpenTag'), $html, 1);
  }

  public function createTag($html, $word, $tag_id) {
    $this->word = $word;
    $this->tag_id = $tag_id;
    return $this->doReplace($html);
  }

  public function removeTagLinks($html) {
    $tagLinks = '/<a class="expresscurate_contentTags".*?>(.*?)<\/a>/i';
    $html = preg_replace($tagLinks, '$1', $html);
    preg_match_all('/(^|\s|>|)(?<!\w)(?=[^>]*(<|$))#\w+/Uuis', $html, $tags);
    foreach ($tags[0] as $tag){
      $html = str_replace($tag, str_replace('#', '', $tag), $html);
    }
    return $html;
  }

  public function removeHighlights($html) {
    $spans = '/<span class="expresscurate_keywordsHighlight">(.*?)<\/span>/uis';
    return preg_replace($spans, '$1', $html);
  }

}

