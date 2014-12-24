<?php


class ExpressCurate_CronManager {

    /**
     * Construct the plugin object
     */
    private $websiteUrlCallCronjob = null;

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

        if(!$this->check_if_exist($this->websiteUrlCallCronjob)){
            file_put_contents($this->tmpForCron, $this->websiteUrlCallCronjob.PHP_EOL);
            exec('crontab '.$this->tmpForCron ,$output);

            // Chech if cron have been added
            add_action( 'admin_notices',array(&$this,'get_message'));
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

    /**
     * Check if cron job exists
     */
    private function check_if_exist($command) {
        exec('crontab -l', $crontab);
        if(isset($crontab) && is_array($crontab)){
            $crontab = array_flip($crontab);
            if(isset($crontab[$command])){
                return true;
            }
        }
        return false;
    }

    private function get_message() {
        $message = '';
        if(!$this->check_if_exist($this->websiteUrlCallCronjob)){
            $message .= '<div class="update-nag">';
            $message .= 'You don not have perrmission to add cronjob!</div>';
        };
        echo $message;
    }

}
