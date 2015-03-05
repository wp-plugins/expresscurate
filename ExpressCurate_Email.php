<?php
require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

class ExpressCurate_Email
{
    const SUPPORT_EMAIL = "support@expresscurate.com";

    private $customerEmail = null;

    public function sendContentAlertEmail($emailData)
    {
        ob_start();
        include(sprintf("%s/templates/email/contentAlert.php", dirname(__FILE__)));
        $email = ob_get_clean();

        $wpUsers = get_users();
        $headers = 'Content-type: text/html; charset=utf-8' . "\r\n";
        foreach ($wpUsers as $user) {
            @wp_mail($user->user_email, 'ExpressCurate Content Alert', $email, $headers);
        }
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
