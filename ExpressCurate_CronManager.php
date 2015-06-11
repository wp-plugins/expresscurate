<?php
require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_CronManager {

    /**
     * Construct the plugin object
     */

    private static $instance;


    public  $websiteUrlCallCronjob = null;
    public  $websiteUrlCallCronjobDeleteAfter = null;

    public function __construct() {
        $this->websiteUrlCallCronjob ='0 * * * *  wget  '.get_site_url().' > /dev/null 2>&1';
        $this->websiteUrlCallCronjobDeleteAfter ='0 * * * * wget --delete-after '.get_site_url().' > /dev/null 2>&1';
    }

    public static function getInstance() {
        if ( ! ( self::$instance instanceof self ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Activate cron jobs
     */
    public  function schedule_events() {
        $cronjobStatus = get_option('expresscurate_cronjob_status');

        if ($cronjobStatus !== 'ignore' && $cronjobStatus !== 'manual') {

            $execExists = function_exists('exec');

            if($execExists) {
                ExpressCurate_Util::exec('crontab -l', $output);
                $output = implode(PHP_EOL, $output);

                $cronjobExists = $this->check_if_exist($this->websiteUrlCallCronjob, $output) || $this->check_if_exist($this->websiteUrlCallCronjobDeleteAfter, $output);

                if ($cronjobExists) {
                    $this->websiteUrlCallCronjob . PHP_EOL;
                    $this->websiteUrlCallCronjobDeleteAfter . PHP_EOL;
                }

                $output = $output . PHP_EOL . $this->websiteUrlCallCronjobDeleteAfter . PHP_EOL;

                $tempFile = ExpressCurate_Util::tmpname('cron');
                file_put_contents($tempFile, $output);
                ExpressCurate_Util::exec('crontab ' . $tempFile, $output);
                unlink($tempFile);
            }
        }

        if (!wp_next_scheduled('expresscurate_publish_event')) {
            wp_schedule_event(time(), 'hourly', 'expresscurate_publish_event');
        }
        if (!wp_next_scheduled('expresscurate_pull_feeds')) {
                wp_schedule_event(time(), 'hourly', 'expresscurate_pull_feeds');
        }
        if (!wp_next_scheduled('expresscurate_content_alert')) {
                wp_schedule_event(time(), 'hourly', 'expresscurate_content_alert');
        }
        if (!wp_next_scheduled('expresscurate_sitemap_generate')) {
            wp_schedule_event(time(), 'hourly', 'expresscurate_sitemap_generate');
        }
        if (!wp_next_scheduled('expresscurate_sitemap_push')) {
            wp_schedule_event(time(), 'hourly', 'expresscurate_sitemap_push');
        }

    }

    /**
     * Deactivate cron jobs
     */
    public function deactivate_events() {
        wp_clear_scheduled_hook('expresscurate_publish_event');
        wp_clear_scheduled_hook('expresscurate_pull_feeds');
        wp_clear_scheduled_hook('expresscurate_content_alert');
        wp_clear_scheduled_hook('expresscurate_sitemap_generate');
        wp_clear_scheduled_hook('expresscurate_sitemap_push');

        $execExists = function_exists('exec');

        if($execExists) {
            ExpressCurate_Util::exec('crontab -l', $output);
            $cronjobExists = $this->check_if_exist($this->websiteUrlCallCronjob, $output) || $this->check_if_exist($this->websiteUrlCallCronjobDeleteAfter, $output);
            $output = implode(PHP_EOL, $output);

            if ($cronjobExists) {
                $output = str_replace($this->websiteUrlCallCronjob, "", $output) . PHP_EOL;
                $output = str_replace($this->websiteUrlCallCronjobDeleteAfter, "", $output) . PHP_EOL;
            }

            $tempFile = ExpressCurate_Util::tmpname('cron');
            file_put_contents($tempFile, $output);
            ExpressCurate_Util::exec('crontab ' . $tempFile);
            unlink($tempFile);
        }
    }


    public function set_permission_status() {
        $status = $_REQUEST['status'] === 'seen' ? 'ignore' : 'manual';
        update_option('expresscurate_cronjob_status', $status);

        $result = array('status'=>'success');
        echo json_encode($result);
        die();
    }

    /**
     * Check if cron job exists
     */
    public function check_if_exist($command, $crontab) {
        if(isset($crontab) && is_array($crontab)){
            $crontab = array_flip($crontab);
            if(isset($crontab[$command])){
                return true;
            }
        }
        return false;
    }



}
