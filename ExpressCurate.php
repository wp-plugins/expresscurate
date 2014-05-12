<?php

/*
  Plugin Name: ExpressCurate
  Plugin URI: http://www.expresscurate.com/products/wordpress-plugin
  Description: ExpressCurate makes it easy to quickly curate quality content for your site. It brings all essential elements for curating content on a single screen, making content curation intuitive and fast.
  Version: 1.1.8
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate {

  /**
   * Construct the plugin object
   */
  public function __construct() {
    // Initialize Settings
    require_once(sprintf("%s/ExpressCurate_Settings.php", dirname(__FILE__)));
    $expresscurate_settings = new ExpressCurate_Settings();
  }

  /**
   * Activate the plugin
   */
  public static function activate() {
    if (!wp_next_scheduled('expresscurate_publish_event')) {
      wp_schedule_event(time(), 'hourly', 'expresscurate_publish_event');
    }
  }

  /**
   * Deactivate the plugin
   */
  public static function deactivate() {
    wp_clear_scheduled_hook('expresscurate_publish_event');
  }

}

if (class_exists('ExpressCurate')) {
  // Installation and uninstallation hooks
  //add_filter('cron_schedules', 'cron_add_5min');
  register_activation_hook(__FILE__, array('ExpressCurate', 'activate'));
  register_deactivation_hook(__FILE__, array('ExpressCurate', 'deactivate'));
  add_action('expresscurate_publish_event', 'expresscurate_publish_event');
  
  function expresscurate_publish_event(){
    $expresscurate_settings = new ExpressCurate_Settings();
    $expresscurate_settings->publish_event();
  }
  
  function cron_add_5min($schedules) {
    $schedules['5min'] = array(
        'interval' => 5 * 60,
        'display' => __('Once every five minutes')
    );
    return $schedules;
  }

  // instantiate the plugin class
  $expresscurate = new ExpressCurate();

  // Add a link to the settings page onto the plugin page
  if (isset($expresscurate)) {

    // Add the settings link to the plugins page
    function expresscurate_settings_link($links) {
      $settings_link = '<a href="options-general.php?page=expresscurate">Settings</a>';
      array_unshift($links, $settings_link);
      return $links;
    }

    $plugin = plugin_basename(__FILE__);
    if (get_option("current_theme") == ExpressCurate_Settings::PLUGIN_THEME) {
      add_filter("plugin_action_links_$plugin", 'expresscurate_settings_link');
    }
  }
}