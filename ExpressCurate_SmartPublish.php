<?php

require_once(sprintf("%s/autoload.php", dirname(__FILE__)));

/*
  Author: ExpressCurate
  Author URI: http://www.expresscurate.com
  License: GPLv3 or later
  License URI: http://www.gnu.org/licenses/gpl.html
 */

class ExpressCurate_SmartPublish
{

    private static $instance;

    function __construct() {
        // action shall be added from actions controller
    }

    public static function getInstance() {
        if ( ! ( self::$instance instanceof self ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    //publish posts curated by chrome extension
    public function publish_event()
    {
        if (get_option('expresscurate_post_status', '') !== 'draft' || !get_option('expresscurate_publish', '') || get_option('expresscurate_publish', '') === '') {
            return;
        }
        $posts = $this->get_posts();
        $recent_posts = $this->get_recent_posts();
        $now = date('Y-m-d H:i:s');
        if (count($posts)) {
            if (((strtotime($posts[0]->post_date) - strtotime($recent_posts[0]['post_date'])) * 60 * 60) > get_option("expresscurate_hours_interval")) {
                $hourdiff = round((strtotime($now) - strtotime($posts[0]->post_date)) / (60 * 60));
            } else {
                $hourdiff = round((strtotime($now) - strtotime($recent_posts[0]['post_date'])) / (60 * 60));
            }
            if ($hourdiff >= get_option("expresscurate_hours_interval")) {
                wp_update_post(array('ID' => $posts[0]->ID, 'post_status' => 'publish'));
                update_post_meta($posts[0]->ID, 'smart_publish_date', $now);
                update_option('expresscurate_publish_mail_sent', 0);
            }
        } elseif (!count($posts) && get_settings('admin_email') && get_option('expresscurate_publish_mail_sent', '0') == '0') {
            $subject = "ExpressCurate Smart Publishing Status";
            $blogName = get_bloginfo('Name');
            $blogUrl = get_bloginfo('url');
            $message = "$blogName\n
                        $blogUrl\n
                        There is no curated posts to publish \n";
            @wp_mail(get_settings('admin_email'), $subject, $message);
            update_option('expresscurate_publish_mail_sent', 1);
        }
        if ($_POST) {
            $result = array();
            $result['status'] = 'success';
            echo json_encode($result);
            die;
        }
    }

    public function get_posts_for_publish()
    {
        if (get_option('expresscurate_post_status', '') !== 'draft' || !get_option('expresscurate_publish', '') || get_option('expresscurate_publish', '') === '') {
            return;
        }
        $posts = $this->get_posts();
        $recent_posts = $this->get_recent_posts();
        $result = array();
        if (round(((strtotime($posts[0]->post_date) - strtotime($recent_posts[0]['post_date'])) / 60 / 60)) > get_option("expresscurate_hours_interval")) {
            $next_post = strtotime($posts[0]->post_date) + (get_option('expresscurate_hours_interval') * 60 * 60);
        } else {
            $next_post = strtotime($recent_posts[0]['post_date']) + (60 * 60 * get_option('expresscurate_hours_interval'));
        }
        $result['next_post_date'] = str_replace(' ', ' ', date('Y/m/d H:i:s', $next_post));
        $result['next_post'] = $next_post;
        $result['posts'] = $posts;
        $result['recent'] = $recent_posts[0];
        return $result;
    }

    public function check_post_status()
    {
        $posts = $this->get_posts();
    }

    private function get_posts()
    {
        $args = array(
            'posts_per_page' => 10,
            'post_status' => 'draft',
            'orderby' => 'date',
            'order' => 'ASC',
        'meta_query' => array(
//            array(
//                'key' => 'expresscurate_chrome',
//                'value' => '1'
//            ),
           array(
               'key' => '_expresscurate_smart_publish',
               'value' => '1'
           )
        )
        );
        $query = new WP_Query($args);
        $posts = $query->get_posts();
        return $posts;
    }

    private function get_recent_posts()
    {
        $recentargs = array(
            'numberposts' => 1,
            'offset' => 0,
            'orderby' => 'post_date',
            'order' => 'DESC',
            'post_type' => 'post',
            'post_status' => 'publish',
            'suppress_filters' => true);

        $recent_posts = wp_get_recent_posts($recentargs, ARRAY_A);
        return $recent_posts;
    }

}

?>
