<?php
require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_Tracker
{

    private static $instance;

    private $pages = array();
    private $paths = array();
    private $hash;

    function __construct()
    {
        $this->hash = site_url();
    }

    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getSiteHash()
    {
        return $this->hash;
    }

    public function trackPage($page)
    {
        $this->pages[] = $page;
    }

    public function trackPath($path)
    {
        $this->paths[] = $path;
    }

    public function track()
    {
        global $expresscurate_track_page;
        global $pagenow;
        // create hash to keep tracking anonymous

        ?>

        <script type="text/javascript">
            var expresscurate_track_hash = '<?php echo $this->hash; ?>',
                siteSendAnalytics = false,
                siteWpSendAnalytics = false;

            if (typeof ga !== 'function') {
                (function (i, s, o, g, r, a, m) {
                    i['GoogleAnalyticsObject'] = r;
                    i[r] = i[r] || function () {
                        (i[r].q = i[r].q || []).push(arguments)
                    }, i[r].l = 1 * new Date();
                    a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                    a.async = 1;
                    a.src = g;
                    m.parentNode.insertBefore(a, m)
                })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
            }

            ga('create', 'UA-47364718-4', 'auto', {
                'name': 'expresscurate',
                //'cookieDomain': 'none',
                'cookieDomain': 'tracking.expresscurate.com',
                'cookieName': 'expresscurate',
                'cookieExpires': 20000
            });
            ga('expresscurate.set', 'forceSSL', true);
            ga('expresscurate.set', 'anonymizeIp', true);
            ga('expresscurate.send', 'pageview', {
                'page': '/site/<?php echo $this->hash; ?>',
                'hitCallback': function () {
                    siteSendAnalytics = true;
                }
            });
            ga('expresscurate.send', 'pageview', {
                'page': '/site/wp/<?php echo $this->hash; ?>',
                'hitCallback': function () {
                    siteWpSendAnalytics = true;
                }
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
            ga('expresscurate.send', 'pageview', {
                'page': '/action/wp/<?php echo $page; ?>/open';
            })
            ;
            <?php } } ?>

            <?php
            if (!empty($this->paths)) {
                foreach($this->paths as $path) {
            ?>
            ga('expresscurate.send', 'pageview', {
                'page': '<?php echo $path; ?>'
            });
            <?php } }
            if((get_option('expresscurate_changed_post_status') && get_option('expresscurate_changed_post_status')=="publish") && ($pagenow=="post-new.php" || $pagenow=="post.php")){
                ?>
            ga('expresscurate.send', 'pageview', {
                'page': '/action/wp/post-edit/publish'
            });
            <?php
               update_option('expresscurate_changed_post_status','');
            }
            ?>
        </script>
    <?php
    }
}

?>
