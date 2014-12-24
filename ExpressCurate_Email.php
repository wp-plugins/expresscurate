<?php

class ExpressCurate_Email
{

    public function sendContentAlertEmail($emailData)
    {
        ob_start();
        include(sprintf("%s/templates/email/contentAlert.php", dirname(__FILE__)));
        $email = ob_get_clean();

        $wpUsers = get_users();
        $headers = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        foreach ($wpUsers as $user) {
            @wp_mail($user->user_email, 'ExpressCurate Content Alert', $email, $headers);
        }
        update_option('expresscurate_content_alert_lastDate', date('Y-m-d H:i:s'));

    }

}
