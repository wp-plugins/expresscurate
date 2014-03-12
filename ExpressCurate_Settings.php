<?php
/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */
require_once 'ExpressCurate_ExportAPI.php';
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

  private $exportAPI = null;

  /**
   * Construct the plugin object
   */
  public function __construct() {
    $this->exportAPI = new ExpressCurate_ExportAPI();
    $this->contentManager = new ExpressCurate_ContentManager();
    // register actions
    add_action('admin_init', array(&$this, 'admin_init'));
    add_action('admin_menu', array(&$this, 'add_menu'));
    add_action('admin_menu', array(&$this, 'expresscurate_add_widget'));
    add_action('admin_print_styles', array(&$this, 'expresscurate_admin_print_styles'));
    remove_action('admin_init', 'send_frame_options_header');
    add_action('init', array(&$this, 'init'));
    add_action('init', array(&$this, 'register_curated_post_status'), 0);
    add_action('init', array(&$this, 'expresscurate_buttons')); //'wpc_buttons'
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
    add_filter('the_excerpt', array(&$this, 'expresscurate_the_excerpt'));
    add_filter('get_the_excerpt', array(&$this, 'expresscurate_the_excerpt'));
    add_filter('mce_css', array(&$this, 'add_expresscurate_editor_style'));

    remove_filter('the_content', 'wpautop');
    //remove_filter( 'the_excerpt', 'wpautop' );
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
    register_setting('expresscurate-group', 'expresscurate_max_tags');
    register_setting('expresscurate-group', 'expresscurate_defined_tags');
    register_setting('expresscurate-group', 'expresscurate_curated_text');
    register_setting('expresscurate-group', 'expresscurate_featured');
    register_setting('expresscurate-group', 'expresscurate_seo');
    register_setting('expresscurate-group', 'expresscurate_autosummary');
    register_setting('expresscurate-group', 'expresscurate_share');
    add_action('admin_footer', array(&$this, 'add_inline_popup_content'));
    add_action('wp_ajax_expresscurate_export_api_get_terms', array($this->exportAPI, 'get_terms'));
    add_action('wp_ajax_expresscurate_export_api_check_auth', array($this->exportAPI, 'check_auth'));
    add_action('wp_ajax_expresscurate_export_api_save_post', array($this->exportAPI, 'save_post'));
    add_action('wp_ajax_expresscurate_get_article', array($this->contentManager, 'get_article'));
    if ($pagenow == 'post.php' || $pagenow == 'post-new.php') {
      add_action('media_buttons_context', array(&$this, 'add_expresscurate_custom_button'), 11);
    }

    // Above and below content filters
    //add_filter('the_content', array(&$this, 'expresscurate_the_content'));
  }

  function expresscurate_continue_reading_link() {
    return ' <a href="' . get_permalink() . '">' . __('more', 'expresscurate') . '</a>';
  }

  public function expresscurate_the_excerpt($text) {
    global $post;
    $attachments = '';
    $text = get_the_content();
    if (has_shortcode($text, 'gallery') || preg_match('/\[gallery\s(.*)\]/isU', $text)) {
      // Retrieve the first gallery in the post
      // Extract the shortcode arguments from the $page or $post
      $shortcode_args = shortcode_parse_atts($this->get_match('/\[gallery\s(.*)\]/isU', $text));
      // get the attachments specified in the "ids" shortcode argument
      $attachments = get_posts(
              array(
                  'include' => $shortcode_args["ids"],
                  'post_status' => 'inherit',
                  'post_type' => 'attachment',
                  'post_mime_type' => 'image',
                  'order' => 'menu_order ID',
                  'orderby' => 'post__in', //this forces the order to be based on the order of the "include" param
                  'numberposts' => 1,
              )
      );
    }

    remove_shortcode('gallery');
    $text = strip_shortcodes($text);
    $text = preg_replace('/\[gallery\s(.*)\]/isU', '', $text);
    $text = preg_replace("/<img[^>]+\>/i", '', $text);
    $text = apply_filters('the_content', $text);

    $text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $text);
    //var_dump($text);
    $expresscurateerpt_length = 300;
    if (strlen($text) > $expresscurateerpt_length) {
      $text = $this->truncateHtml($text, $expresscurateerpt_length, '');
    }
    //var_dump($attachments);die;
    if ($attachments) {
      $attachment_img = wp_get_attachment_image($attachments[0]->ID, 'thumbnail');
      $attachment_img = '<div class="fimgContainer">
			<a href="' . get_permalink($post->ID) . '">' . $attachment_img . '</a>
			</div>';
      $text = $attachment_img . $text;
    }

    return $text;
  }

  public function truncateHtml($text, $length = 100, $ending = '...', $exact = true, $considerHtml = true) {
    if ($considerHtml) {
      // if the plain text is shorter than the maximum length, return the whole text
      if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
        return $text;
      }
      //remove all images
      $text = preg_replace('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', '', $text);
      // splits all html-tags to scanable lines
      preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

      $total_length = strlen($ending);
      $open_tags = array();
      $truncate = '';
      foreach ($lines as $line_matchings) {
        // if there is any html-tag in this line, handle it and add it (uncounted) to the output
        if (!empty($line_matchings[1])) {
          // if it's an "empty element" with or without xhtml-conform closing slash
          if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
            // do nothing
            // if tag is a closing tag
          } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
            // delete tag from $open_tags list
            $pos = array_search($tag_matchings[1], $open_tags);
            if ($pos !== false) {
              unset($open_tags[$pos]);
            }
            // if tag is an opening tag
          } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
            // add tag to the beginning of $open_tags list
            array_unshift($open_tags, strtolower($tag_matchings[1]));
          }
          // add html-tag to $truncate'd text
          $truncate .= $line_matchings[1];
        }
        // calculate the length of the plain text part of the line; handle entities as one character
        $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
        if ($total_length + $content_length > $length) {
          // the number of characters which are left
          $left = $length - $total_length;
          $entities_length = 0;
          // search for html entities
          if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
            // calculate the real length of all entities in the legal range
            foreach ($entities[0] as $entity) {
              if ($entity[1] + 1 - $entities_length <= $left) {
                $left--;
                $entities_length += strlen($entity[0]);
              } else {
                // no more characters left
                break;
              }
            }
          }
          $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
          // maximum lenght is reached, so get off the loop
          break;
        } else {
          $truncate .= $line_matchings[2];
          $total_length += $content_length;
        }
        // if the maximum length is reached, get off the loop
        if ($total_length >= $length) {
          break;
        }
      }
    } else {
      if (strlen($text) <= $length) {
        return $text;
      } else {
        $truncate = substr($text, 0, $length - strlen($ending));
      }
    }
    // if the words shouldn't be cut in the middle...
    if (!$exact) {
      // ...search the last occurance of a space...
      $spacepos = strrpos($truncate, ' ');
      if (isset($spacepos)) {
        // ...and cut the text in this position
        $truncate = substr($truncate, 0, $spacepos);
      }
    }
    // add the defined ending to the text
    $truncate .= $ending;
    if ($considerHtml) {
      // close all unclosed html-tags
      foreach ($open_tags as $tag) {
        $truncate .= '</' . $tag . '>';
      }
    }
    return $truncate;
  }

  public function expresscurate_buttons() {
    add_filter("mce_external_plugins", array(&$this, 'expresscurate_add_plugins'));
    add_filter('mce_buttons', array(&$this, 'expresscurate_register_buttons'));
  }

  public function expresscurate_add_plugins($plugin_array) {
    $pluginUrl = plugin_dir_url(__FILE__);
    $plugin_array['expresscurate'] = $pluginUrl . 'js/expresscurate_buttons.js';
    return $plugin_array;
  }

  public function expresscurate_register_buttons($buttons) {
    array_push($buttons, 'annotate');
    return $buttons;
  }

  public function add_expresscurate_editor_style($mce_css) {
    $pluginUrl = plugin_dir_url(__FILE__);
    return $pluginUrl . 'css/theme-styles.css';
  }

  public function add_expresscurate_custom_button($context) {
//path to icon
    $img = plugins_url('images/19x19.png', __FILE__);

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
      <?php include(sprintf("%s/templates/metabox.php", dirname(__FILE__))); ?>
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
    echo sprintf('expresscurate_%s_section', self::POST_TYPE), sprintf('%s ' . self::PLUGIN_NAME, ucwords(str_replace("_", " ", self::POST_TYPE))), array(&$this, 'add_inner_meta_boxes'), self::POST_TYPE;
    die;
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
    include(sprintf("%s/templates/metabox.php", dirname(__FILE__)));
  }

  public function generate_tags($post_id) {
    $defined_tags = "";
    if (@get_option("expresscurate_defined_tags")) {
      $defined_tags = get_option("expresscurate_defined_tags");
    }
    if ($defined_tags) {
      $defined_tags = explode(",", $defined_tags);
    }
    $the_post = get_post($post_id);
// get the content of the post
    $post_content = $the_post->post_content;

    $tags = get_the_tags();
    if ($tags) {
      foreach ($tags as $tag) {
        $post_tags[$tag->term_id] = $tag->name;
      }
    }
    $content_tags = array();
    preg_match_all('/(?<!\w)(?=[^>]*(<|$))#\w+/i', $post_content, $content_tags);
//var_dump($content_tags); die;

    foreach ($content_tags[0] as $content_tag) {
      $content_tag_insert = str_replace("#", "", trim($content_tag));
//adding content tag to post tags if not exists
      if (!in_array($content_tag_insert, $post_tags)) {
        wp_set_post_tags($post_id, $content_tag_insert, true);
      }
    }
    if ($defined_tags && count($defined_tags)) {
      foreach ($defined_tags as $defined_tag) {
        $defined_tag_insert = trim($defined_tag);
//adding defined tag to post tags if tag exists in posttitle or post content and not exists
        preg_match("/(?<!\w)(?=[^>]*(<|$))#" . $tag->name . "/i", $post_content, $tag_in_content);
        if ((isset($tag_in_content[0]) || strpos($the_post->title, $defined_tag_insert)) && !in_array($defined_tag_insert, $post_tags)) {
          wp_set_post_tags($post_id, $defined_tag_insert, true);
        }
      }
    }
    $tags = get_the_tags();
    if ($tags && count($tags)) {
      foreach ($tags as $tag) {
        preg_match("/(?<!\w)(?=[^>]*(<|$))#" . $tag->name . "/i", $post_content, $tag_in_content);
        if (isset($tag_in_content[0])) {
          preg_match("/>#" . $tag->name . '(<\/a>)/', $post_content, $tag_in_a);
          if (!isset($tag_in_a[0])) {
            $post_content = preg_replace("/(?<!\w)(?=[^>]*(<|$))#" . $tag->name . "/i", '<a href="' . get_tag_link($tag->term_id) . '">#' . $tag->name . '</a>', $post_content, 1);
          }
        } else {
          preg_match("/(?<!\w)(?=[^>]*(<|$))" . $tag->name . "/i", $post_content, $tag_in_content);
          if (isset($tag_in_content[0])) {
            preg_match("/>" . $tag->name . '(<\/a>)/i', $post_content, $tag_in_a);
            if (!isset($tag_in_a[0])) {
              $post_content = preg_replace("/(?<!\w)(?=[^>]*(<|$))" . $tag->name . "/i ", '<a href="' . get_tag_link($tag->term_id) . '">#' . $tag->name . '</a>', $post_content, 1);
            }
          }
        }
      }//end tags
    }
    return $post_content;
  }

  public function get_metas($post_id = '', $type = 'post', $status = 'publish') {
    global $wpdb;
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

// verify if this is an auto save routine. 
// If it is our form has not been submitted, so we dont want to do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }
    if (wp_is_post_revision($post_id))
      return;

// get the content of the post
    $post_content = $this->generate_tags($post_id);
    //Seo part
    if (get_option('expresscurate_seo', '') == 1) {
      $expresscurate_keywords = isset($_POST['expresscurate_keywords']) ? $_POST['expresscurate_keywords'] : '';
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
      preg_match_all("/\< *[img][^\>]*[src] *= *[\"\']{0,1}([^\"\']*)/i", $post_content, $images);
      $upload_dir = wp_upload_dir();
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      $siteDomain = parse_url(get_site_url(), PHP_URL_HOST);
      if (count($images) > 0 && is_writable($upload_dir['path'])) {
        foreach ($images[1] as $i => $image) {
          $image = strtok($image, '?');
          $domain = parse_url($image, PHP_URL_HOST);
          if ($siteDomain != $domain) {
            $image_data = file_get_contents($image);
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
      $post_content = $this->generate_tags($post_id);
      $curated_post = array(
          'ID' => $post_id,
          'post_content' => $post_content,
      );
      remove_action('save_post', array(&$this, 'save_post'));
      wp_update_post($curated_post);
      add_action('save_post', array(&$this, 'save_post'));
      return;
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
            self::PLUGIN_NAME . ' Settings', self::PLUGIN_NAME, 'manage_options', 'expresscurate', array(&$this, 'plugin_settings_page')
    );
    add_menu_page(self::PLUGIN_NAME, self::PLUGIN_NAME, 'manage_options', 'expresscurate', array(&$this, 'plugin_settings_page'), '', 6);
    add_submenu_page('expresscurate', self::PLUGIN_NAME, '', 'manage_options', 'expresscurate', array(&$this, 'plugin_settings_page'), '');
    add_submenu_page('expresscurate', 'Settings', 'Settings', 'manage_options', 'expresscurate_settings', array(&$this, 'plugin_settings_page'), '');
    add_submenu_page('expresscurate', 'News', 'News', 'edit_posts', 'expresscurate_news', array(&$this, 'show_expresscurate_news'), '');
    add_submenu_page('expresscurate', 'Top Curated websites', 'Top Curated websites', 'edit_posts', 'expresscurate_websites', array(&$this, 'show_expresscurate_websites'), '');
  }

  //Add widget 
  public function expresscurate_add_widget() {
    if (get_option('expresscurate_seo', '') == 1) {
      add_meta_box('expresscurate', self::PLUGIN_NAME . ' SEO Control Center', array(&$this, 'expresscurate_meta_box'), 'post', 'side', 'high');
      add_meta_box('expresscurate', self::PLUGIN_NAME . ' SEO Control Center', array(&$this, 'expresscurate_meta_box'), 'page', 'side', 'high');
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

  public function add_expresscurate_seo() {
    global $post;
    $post_id = $post->ID;
    if (is_feed())
      return;
    $meta_string = '';
    if (is_single() || is_page() && (get_option('expresscurate_seo', '') == 1)) {
      $keywords = get_post_meta($post_id, '_expresscurate_keywords', true);
      $description = get_post_meta($post_id, '_expresscurate_description', true);
      if ($description) {
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

      if ($keywords) {
        $keword_arr = array_map('trim', explode(',', $keywords));
        $keword_arr = array_unique($keword_arr);
        $keywords = wp_filter_nohtml_kses(str_replace('"', '', implode(',', $keword_arr)));
        $meta_string .= sprintf("<meta name=\"keywords\" content=\"%s\" />\n", $keywords);
      }
      if ($meta_string != null) {
        echo "\n<!-- ExpressCurate SEO-->\n";
        echo "$meta_string\n";
        echo "<!-- /ExpressCurate SEO -->\n";
      }
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

  public function show_expresscurate_websites() {
    if (!current_user_can('edit_posts')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
    }
// Render the settings template
    include(sprintf("%s/templates/websites.php", dirname(__FILE__)));
  }

  public function expresscurate_admin_print_styles() {
    $plaugunUrl = plugin_dir_url(__FILE__);
    wp_enqueue_script('expresscurate', $plaugunUrl . 'js/expresscurate.js', array('jquery', 'jquery-ui-core', 'jquery-ui-dialog'));
    wp_enqueue_script('expresscurate_slider', $plaugunUrl . 'js/plugins/jquery.jcarousel.min.js', array('jquery'));
    wp_enqueue_style('texpresscurate', $plaugunUrl . 'css/expresscurate.css');
    if (get_bloginfo('version') < 3.8) {
      wp_enqueue_style('menu-expresscurate', $plaugunUrl . 'css/menu-style-3.6.css');
    } else {
      wp_enqueue_style('menu-expresscurate', $plaugunUrl . 'css/menu-style-3.8.css');
    }
  }

  public function expresscurate_theme_styles() {
    $plaugunUrl = plugin_dir_url(__FILE__);
    wp_enqueue_style('texpresscurate', $plaugunUrl . 'css/theme-styles.css');
  }

  private static function getCurationNews($url = self::NEWS_FEED_URL) {
    $rss = new DOMDocument();
    $rss->load($url);
    $feed = array();
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
    return $feed;
  }

  private function get_match($regex, $content) {
    preg_match($regex, $content, $matches);
    return $matches[1];
  }

  private function pluginUrl() {
    return plugin_dir_url(__FILE__);
  }

}

