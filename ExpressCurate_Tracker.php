<?php

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_Tracker {

    private static $instance;

    private $pages = array();

    function __construct() {
        // action shall be added from actions controller
    }

    public static function getInstance() {
        if ( ! ( self::$instance instanceof self ) ) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    public function trackPage($page) {
        $this->pages[] = $page;
    }

    public function track() {
         global $expresscurate_track_page;
         
         // create hash to keep tracking anonymous
         $hash = md5( site_url() );
         ?>
         
         <script type="text/javascript">
         if (typeof ga !== 'function') {
             (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
             (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
             m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
             })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
         }
         
         ga('create', 'UA-47364718-4', 'auto', {
             'name': 'expresscurate',
             'cookieDomain': 'none',
             //'cookieDomain': 'tracking.expresscurate.com',
             'cookieName': 'expresscurate',
             //'cookieExpires': 20000
         });
         ga('expresscurate.set', 'forceSSL', true);
         ga('expresscurate.set', 'anonymizeIp', true);
         ga('expresscurate.send', 'pageview', {
             'page': '/site/<?php echo $hash; ?>'
         });
         <?php if ($expresscurate_track_page) {?>
         ga('expresscurate.send', 'pageview', {
             'page': '/page/<?php echo $expresscurate_track_page; ?>'
         });
         <?php } ?>
         
         <?php
         if (!empty($this->pages)) {
             foreach($this->pages as $page) {
         ?>
         ga('expresscurate.send', 'pageview', {
             'page': '/page/<?php echo $page; ?>'
         });
         <?php } } ?>
         
         </script>
         <?php
    }
}

?>
