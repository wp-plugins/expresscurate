<?php
require_once(sprintf("%s/autoload.php", dirname(__FILE__)));
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 10/9/14
 * Time: 2:39 PM
 */
class ExpressCurate_Sitemap
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

    public function generateSitemapScheduled(){
        $intervalInDays = null;
        //$lastGenerationDate = 0;
        switch(get_option('expresscurate_sitemap_generation_interval')) {
            case 'daily': $intervalInDays = 1;
                break;
            case 'weekly': $intervalInDays = 7;
                break;
            case 'monthly': $intervalInDays = 30;
        };

        if (empty($intervalInDays)) {
            return;
        }
        $lastGenerationDate = get_option('expresscurate_sitemap_generation_last_date');
        if (floor(date('Y-m-d H:i:s') - strtotime($lastGenerationDate)/(60*60*24) ) >= $intervalInDays) {
            $this->generateSitemap();
        }


    }

    public function pushSitemapScheduled(){
        $intervalInDays = null;
        $lastGenerationDate = 0;
        switch(get_option('expresscurate_sitemap_submit_frequency')) {
            case 'daily': $intervalInDays = 1;
                break;
            case 'weekly': $intervalInDays = 7;
                break;
            case 'monthly': $intervalInDays = 30;
        };

        if (empty($intervalInDays)) {
            return;
        }
        $lastGenerationDate = get_option('expresscurate_sitemap_submit_lastDate');
        if (floor(date('Y-m-d H:i:s') - strtotime($lastGenerationDate)/(60*60*24) ) >= $intervalInDays) {
            $this->submitToGoogle();
        }


    }

    public function generateSitemap()
    {
        $homePath = get_home_path();
        $sitemmapPath = $homePath . 'sitemap.xml';
        $expresscurateSitemapUpdatePermission = get_option('expresscurate_sitemap_update_permission',false);
        if($expresscurateSitemapUpdatePermission !='seen' && $expresscurateSitemapUpdatePermission != 'error') {
            if (is_writable($sitemmapPath)) {
                $header = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                $body = '';
                $footer = '</urlset>';

                $posts_and_pages = array();
                $publishedPostCount = wp_count_posts()->publish;

                for ($i = 0; $i < ceil($publishedPostCount / 200); $i++) {
                    $args = array('status' => 'published', 'numberposts' => 200, 'offset' => $i * 200);
                    $postsAndPages = array_merge(get_posts($args), get_pages($args));
                    foreach ($postsAndPages as $item) {
                        $posts_and_pages[] = array('ID' => $item->ID, 'modified' => $item->post_modified_gmt);
                    }
                }
                foreach ($posts_and_pages as $item) {
                    $skipPost = get_post_meta($item['ID'], '_expresscurate_sitemap_post_exclude_from_sitemap', true);
                    if (empty($skipPost)) {
                        $changeFrequency = get_option('expresscurate_sitemap_default_changefreq');
                        $priority = get_option('expresscurate_sitemap_priority_manual_value');

                        $configureManually = get_post_meta($item['ID'], '_expresscurate_sitemap_post_configure_manually', true);

                        if (!empty($configureManually)) {
                            $changeFrequency = get_post_meta($item['ID'], '_expresscurate_sitemap_post_frequency', true);
                            $priority = get_post_meta($item['ID'], '_expresscurate_sitemap_post_priority', true);
                        }
                        $lastMod = gmdate('Y-m-d', strtotime($item['modified']));
                        $body .= '<url>
                            <loc>' . get_permalink($item['ID']) . '</loc>
                            <lastmod>' . $lastMod . '</lastmod>
                            <changefreq>' . $changeFrequency . '</changefreq>
                            <priority>' . $priority . '</priority>
                          </url>';

                    }

                }
                $path = get_home_path();

                $file = fopen($path . 'sitemap.xml', 'w');
                if ($file) {
                    fwrite($file, $header . $body . $footer);
                    fclose($file);
                    $this->addToRobots();
                    update_option('expresscurate_sitemap_generation_last_date', date('Y-m-d H:i:s'));
                    return true;
                }
            }
        }
        return false;

    }

    public function isExists(){
        $path = get_home_path();
        $file = file_exists($path . 'sitemap.xml');
        if($file){
            return true;
        }
        return false;
    }

    public function saveSitemapGoogleStatus(){
        $data = $_REQUEST;
        $status = $data['status'];
        if($status == 'off'){
            update_option('expresscurate_google_refresh_token','');
        }
        update_option('expresscurate_sitemap_submit',$status);
    }

    public function submitToGoogle()
    {
        $googleAuth = new ExpressCurate_GoogleAuth();
        $submitToWebmastersStatus = get_option('expresscurate_sitemap_submit');
        if ($submitToWebmastersStatus) {
            $response = $googleAuth->getAccessToken();
            if ($response) {
                $response = $googleAuth->submit_sitemap(get_site_url(), get_site_url() . '/sitemap.xml');
                 return $response;
            } else {
                // @TODO notify user to get key
            }
       }
    }

    public function errorMessage(){
        $homeUrl = get_site_url();
        $sitemmapPath = $homeUrl . '/sitemap.xml';
        $message = '<div class="update-nag">';
        $message .=  '
                          ExpressCurate tries to generate sitemap but it was not able to write sitemap. Please, grant write access to file
                    <p style="text-indent: 20px;font-weight: bolder">'.$sitemmapPath.'</p>
                    <a class="expresscurateLink"  href="'.$homeUrl.'/wp-admin/admin.php?page=expresscurate_settings"  > Sitemap settings</a>
                    ';
        $message .= '</div>';
        echo $message;
    }

    public function set_permission_status() {
        $status = $_REQUEST['status'];
        update_option('expresscurate_sitemap_update_permission', $status);
        $result = array('status'=>'success');
        echo json_encode($result);die;
    }

    public function getSitemapFrequencyTagArray(){
        return array('always'=>'Always',
            'hourly'=>'Hourly',
            'daily'=>'Daily',
            'weekly'=>'Weekly',
            'monthly'=>'Monthly',
            'yearly'=>'Yearly',
            'never'=>'Never'
        );
    }

    private function addToRobots(){
        $path = get_home_path();
        $file = fopen($path.'robots.txt','w');
        fwrite($file,'Sitemap: '.get_site_url().'/sitemap.xml');
        fclose($file);
    }

}
