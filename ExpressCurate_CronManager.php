<?php


class ExpressCurate_CronManager {

    /**
     * Construct the plugin object
     */
    public  $websiteUrlCallCronjob = null;

    private $tmpForCron = null;

    public function __construct() {
        $temp_file = tempnam(sys_get_temp_dir(), 'cron');

        $this->tmpForCron = $temp_file;

        $this->websiteUrlCallCronjob ='0 * * * *  wget  '.get_site_url().' > /dev/null 2>&1';
    }

    /**
     * Activate cron jobs
     */
    public  function schedule_events() {
        $exec_function_permission_status = get_option('expresscurate_exec_function_permission_status',false);
        if($exec_function_permission_status != 'error'){
            if(!$this->check_if_exist($this->websiteUrlCallCronjob)){
                file_put_contents($this->tmpForCron, $this->websiteUrlCallCronjob.PHP_EOL);
                exec('crontab '.$this->tmpForCron ,$output);
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

        // Remove existing cronjob
        if($this->check_if_exist($this->websiteUrlCallCronjob)){
            echo exec('crontab -l' ,$output);
            $newCron = str_replace($this->websiteUrlCallCronjob,"",$output);
            file_put_contents($this->tmpForCron, $newCron.PHP_EOL);
            exec('crontab '.$this->tmpForCron);
        }
    }


    public function set_permission_status() {
        $status = $_REQUEST['status'];
        update_option('expresscurate_exec_function_permission_status', $status);
        $result = array('status'=>'success');
        echo json_encode($result);die;
    }

    /**
     * Check if cron job exists
     */
    public function check_if_exist($command) {
        exec('crontab -l', $crontab);
        if(isset($crontab) && is_array($crontab)){
            $crontab = array_flip($crontab);
            if(isset($crontab[$command])){
                return true;
            }
        }
        return false;
    }



}
