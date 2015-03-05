<?php
require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

class ExpressCurate_CronManager {

    /**
     * Construct the plugin object
     */

    private static $instance;


    public  $websiteUrlCallCronjob = null;

    private $tmpForCron = null;

    public function __construct() {
        $temp_file =  ExpressCurate_Util::tmpname('.tmp', 'cron');

        $this->tmpForCron = $temp_file;

        $this->websiteUrlCallCronjob ='0 * * * *  wget  '.get_site_url().' > /dev/null 2>&1';
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
            $cronjobExists = $this->check_if_exist($this->websiteUrlCallCronjob);

            if ($execExists && !$cronjobExists) {
                ExpressCurate_Util::exec('crontab -l' ,$output);
                $output = implode(PHP_EOL, $output);
                $output = $output . PHP_EOL . $this->websiteUrlCallCronjob . PHP_EOL;
                file_put_contents($this->tmpForCron, $output);
                ExpressCurate_Util::exec('crontab ' . $this->tmpForCron, $output);
                unlink($this->tmpForCron);
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

        if($this->check_if_exist($this->websiteUrlCallCronjob)){
            ExpressCurate_Util::exec('crontab -l' ,$output);
            $output = implode(PHP_EOL,$output);
            $newCron = str_replace($this->websiteUrlCallCronjob,"",$output);
            file_put_contents($this->tmpForCron, $newCron.PHP_EOL);
            ExpressCurate_Util::exec('crontab '.$this->tmpForCron);
            unlink($this->tmpForCron);
        }
    }


    public function set_permission_status() {
//        $status = $_REQUEST['status'];
//        update_option('expresscurate_exec_function_permission_status', $status);

        $status = $_REQUEST['status'] === 'seen' ? 'ignore' : 'manual';
        update_option('expresscurate_cronjob_status', $status);

        $result = array('status'=>'success');
        echo json_encode($result);
        die();
    }

    /**
     * Check if cron job exists
     */
    public function check_if_exist($command) {
        ExpressCurate_Util::exec('crontab -l', $crontab);
        if(isset($crontab) && is_array($crontab)){
            $crontab = array_flip($crontab);
            if(isset($crontab[$command])){
                return true;
            }
        }
        return false;
    }



}
