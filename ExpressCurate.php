<?php

/*
  Plugin Name: ExpressCurate
  Plugin URI: http://www.expresscurate.com/products/wordpress-plugin
  Description: ExpressCurate makes it easy to quickly curate quality content for your site .  It brings all essential elements for curating content on a single screen, making content curation intuitive and fast.
  Version: 1.1
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
    
  }

  /**
   * Deactivate the plugin
   */
  public static function deactivate() {
    
  }

}

if (class_exists('ExpressCurate')) {
  // Installation and uninstallation hooks
  //add_filter('cron_schedules', 'cron_add_5min');
  register_activation_hook(__FILE__, array('ExpressCurate', 'activate'));
  register_deactivation_hook(__FILE__, array('ExpressCurate', 'deactivate'));

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