<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_Email
{
    const SUPPORT_EMAIL = "support@expresscurate.com";

    private $customerEmail = null;

    public function hasContentAlertRecipients()
    {
        $alertUsers = trim(get_option('expresscurate_content_alert_users', ''));
        return strlen($alertUsers) > 0;
    }

    public function sendContentAlertEmail($emailData)
    {
        // render the email
        ob_start();
        include(sprintf("%s/templates/email/contentAlert.php", dirname(__FILE__)));
        $email = ob_get_clean();

        // define headers
        $headers = 'Content-type: text/html; charset=utf-8' . "\r\n";
        
        // get recipients
        $wpUsers = get_users();
        $alertUsers = split(',', trim(get_option('expresscurate_content_alert_users', '')));
        // TODO optimize to load only the users we need
        foreach ($wpUsers as $user) {
            // don't sent alerts to subscribers
            if(in_array('subscriber', $user->roles) || !in_array($user->user_login, $alertUsers)) {
                continue;
            }
            @wp_mail($user->user_email, 'ExpressCurate Content Alert', $email, $headers);
        }
        // set the last alert time
        update_option('expresscurate_content_alert_lastDate', date('Y-m-d H:i:s'));
    }

    public function sendSupportEmail($emailFrom, $title, $message)
    {
        $this->setCustomerEmail($emailFrom) ;
        add_filter( 'wp_mail_from',array(&$this,'getCustomerEmail'));
        return wp_mail(self::SUPPORT_EMAIL, $title, $message);

    }

    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail($val)
    {
        $this->customerEmail = $val;
    }

}
