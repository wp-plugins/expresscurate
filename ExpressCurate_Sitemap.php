<?php
require_once 'ExpressCurate_GoogleAuth.php';
/**
 * Created by PhpStorm.
 * User: armen
 * Date: 10/9/14
 * Time: 2:39 PM
 */
class ExpressCurate_Sitemap
{


    public function generateSitemapScheduled(){
        $intervalInDays = null;
        $lastGenerationDate = 0;
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
            $this->generateSitemapIndex();
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

    public function generateSitemapIndex(){
        $this->generateSitemap();
    }

    public function generateSitemap(){
        $header = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $body = '';
        $footer = '</urlset>';

        //$posts_and_pages = array_merge(get_posts(),get_pages());
        $posts_and_pages = array();
        $publishedPostCount = wp_count_posts()->publish;

        for ($i = 0; $i < ceil($publishedPostCount / 200); $i++) {
            $args = array('status' => 'published', 'numberposts' => 200, 'offset' => $i * 200);
            $postsAndPages = array_merge(get_posts($args), get_pages($args));
            foreach ($postsAndPages as $item) {
                $posts_and_pages[] = array('ID'=> $item->ID,'modified'=>$item->post_modified_gmt);
            }
        }
        foreach ($posts_and_pages as $item) {
            $skipPost = get_post_meta($item['ID'],'expresscurate_sitemap_post_exclude_from_sitemap',true);

            if (empty($skipPost)){
                $changeFrequency = get_option('expresscurate_sitemap_default_changefreq');
                $priority = get_option('expresscurate_sitemap_priority_manual_value');

                $configureManually = get_post_meta($item['ID'],'expresscurate_sitemap_post_configure_manually',true);

                if (!empty($configureManually)){
                    $changeFrequency = get_post_meta($item['ID'], 'expresscurate_sitemap_post_frequency',true);
                    $priority = get_post_meta($item['ID'], 'expresscurate_sitemap_post_priority',true);
                }

                $body .= '<url>
                        <loc>'.get_permalink($item['ID']).'</loc>
                        <lastmod>'.$item['modified'].'</lastmod>
                        <changefreq>'.$changeFrequency.'</changefreq>
                        <priority>'.$priority.'</priority>
                        </url>';
            }

        }
        $path = get_home_path();
        $file = fopen($path.'sitemap.xml','w');
        fwrite($file,$header.$body.$footer);
        fclose($file);
        $this->addToRobots();
        update_option('expresscurate_sitemap_generation_last_date',date('Y-m-d H:i:s'));

        echo json_encode(array('status'=>'success'));die;
    }

    private function addToRobots(){
        $path = get_home_path();
        $file = fopen($path.'robots.txt','w');
        fwrite($file,'Sitemap: '.get_site_url().'/sitemap.xml');
        fclose($file);
    }


    public function submitToGoogle()
    {
        $googleAuth = new ExpressCurate_GoogleAuth();
        settings_fields('expresscurate-sitemap-group');
        do_settings_sections('expresscurate-sitemap-group');

        if (get_option('expresscurate_sitemap_submit_webmasters') == 'on') {

            $responseToken = $googleAuth->getGoogleToken();
            if ($responseToken) {
                $oauth = new ExpressCurate_GoogleAuth();
                $oauth->accessToken = $responseToken;
                $response = $oauth->submit_sitemap(get_site_url(), get_site_url() . '/sitemap.xml');
                update_option('expresscurate_google_auth_key',$responseToken);
                wp_redirect( '/admin.php?page=expresscurate_settings', 301 );
               // return $response;
            } else {
                // @TODO notify user to get key
            }
        }
    }
}
